<?php
/**
 * NEXUS V2 — Gestionnaire de clés API & modèles Mistral
 */

define('MANAGER_DB', __DIR__ . '/mistral_manager.sqlite');
define('APIKEY_FILE', __DIR__ . '/apikey.json');

function getManagerDB(): PDO {
    $db = new PDO('sqlite:' . MANAGER_DB);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS api_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pseudo TEXT, key_val TEXT UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS model_usage (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        model_name TEXT UNIQUE,
        limit_tpm INTEGER, limit_mpm TEXT, limit_rps REAL,
        used_tokens_session INTEGER DEFAULT 0,
        last_status TEXT, last_tested DATETIME
    )");
    return $db;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $db = getManagerDB();

    if ($_POST['action'] === 'add_key') {
        $key    = trim($_POST['key'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? 'user');
        if (strlen($key) < 10) { echo json_encode(['error'=>'Clé trop courte']); exit; }
        $db->prepare("INSERT OR IGNORE INTO api_keys (pseudo, key_val) VALUES (?,?)")->execute([$pseudo, $key]);
        // Sauvegarder comme clé active
        file_put_contents(APIKEY_FILE, json_encode(['api_key'=>$key,'pseudo'=>$pseudo]));
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($_POST['action'] === 'get_data') {
        $keys   = $db->query("SELECT * FROM api_keys ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($keys as &$k) $k['key_masked'] = substr($k['key_val'],0,6).'••••'.substr($k['key_val'],-4);
        $models = $db->query("SELECT * FROM model_usage ORDER BY last_status ASC, model_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $active = file_exists(APIKEY_FILE) ? (json_decode(file_get_contents(APIKEY_FILE),true)['pseudo']??'') : '';
        echo json_encode(['keys'=>$keys,'models'=>$models,'active_pseudo'=>$active]);
        exit;
    }

    if ($_POST['action'] === 'test_model') {
        $model = $_POST['model'];
        $key   = $_POST['key'];

        $ch = curl_init('https://api.mistral.ai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$key,'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['model'=>$model,'messages'=>[['role'=>'user','content'=>'ok']],'max_tokens'=>2]),
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data   = json_decode($response, true);
        $tokens = $data['usage']['total_tokens'] ?? 0;
        $status = $code === 200 ? 'OK' : 'Err '.$code;

        $db->prepare("INSERT INTO model_usage (model_name,limit_tpm,limit_mpm,limit_rps,used_tokens_session,last_status,last_tested)
            VALUES (:n,:tpm,:mpm,:rps,:tok,:stat,CURRENT_TIMESTAMP)
            ON CONFLICT(model_name) DO UPDATE SET
              used_tokens_session=used_tokens_session+:tok,
              last_status=:stat, last_tested=CURRENT_TIMESTAMP")
           ->execute([':n'=>$model,':tpm'=>(int)$_POST['limit_tpm'],':mpm'=>$_POST['limit_mpm'],
                      ':rps'=>(float)$_POST['limit_rps'],':tok'=>$tokens,':stat'=>$status]);

        echo json_encode(['status'=>$code,'tokens'=>$tokens,'ok'=>$code===200]);
        exit;
    }

    if ($_POST['action'] === 'set_active') {
        $key    = $_POST['key'];
        $pseudo = $_POST['pseudo'] ?? 'user';
        file_put_contents(APIKEY_FILE, json_encode(['api_key'=>$key,'pseudo'=>$pseudo]));
        echo json_encode(['success'=>true]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>NEXUS — Gestionnaire API Mistral</title>
<style>
:root{--bg:#0a0e17;--bg2:#111827;--card:#141c2e;--border:#1e2d47;--accent:#4f9eff;--green:#2dd4a0;--red:#f87171;--text:#cdd8ee;--muted:#7a8aaa}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);padding:24px}
.container{max-width:1100px;margin:auto}
h1{font-size:1.4rem;margin-bottom:20px;display:flex;align-items:center;gap:10px}
h1 a{color:var(--accent);text-decoration:none;font-size:.9rem;font-weight:400}
.card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:20px}
h3{font-size:.95rem;margin-bottom:14px;color:var(--accent)}
table{width:100%;border-collapse:collapse;font-size:.83rem}
th,td{padding:9px 12px;border-bottom:1px solid var(--border);text-align:left}
th{background:rgba(255,255,255,.03);color:var(--muted);text-transform:uppercase;font-size:.72rem;letter-spacing:.8px}
.ok{color:var(--green);font-weight:700}
.err{color:var(--red)}
.pending{color:var(--muted)}
.btn{padding:8px 16px;border-radius:7px;border:none;font-size:.83rem;font-weight:600;cursor:pointer;transition:all .2s}
.btn-primary{background:var(--accent);color:#fff}
.btn-sm{padding:5px 10px;font-size:.75rem;background:var(--bg2);color:var(--text);border:1px solid var(--border);border-radius:5px;cursor:pointer}
.btn-sm:hover{border-color:var(--accent);color:var(--accent)}
input,select{padding:8px 12px;background:#1a2236;border:1px solid var(--border);color:var(--text);border-radius:7px;font-size:.85rem}
.progress{width:100%;height:8px;background:var(--border);border-radius:4px;overflow:hidden;margin:10px 0}
.progress-fill{height:100%;background:var(--accent);border-radius:4px;transition:width .3s;width:0%}
.info-bar{padding:10px 14px;background:rgba(79,158,255,.08);border-radius:8px;font-size:.83rem;margin-bottom:10px;color:var(--accent)}
.active-badge{background:rgba(45,212,160,.15);color:var(--green);padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700}
</style>
</head>
<body>
<div class="container">
<h1>🔑 NEXUS — Gestionnaire API Mistral &nbsp; <a href="index.php">← Dashboard</a></h1>

<div class="card">
  <h3>Ajouter une clé API</h3>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <input type="text" id="pseudo" placeholder="Pseudo" style="max-width:130px" value="laurent">
    <input type="password" id="new_key" placeholder="Clé API Mistral" style="min-width:280px">
    <button class="btn btn-primary" onclick="addKey()">Enregistrer & Activer</button>
  </div>
</div>

<div class="card">
  <h3>Clés enregistrées</h3>
  <div id="active-info" class="info-bar" style="display:none"></div>
  <table id="keys-table">
    <thead><tr><th>Pseudo</th><th>Clé</th><th>Actions</th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<div class="card">
  <h3>Scanner les modèles Mistral</h3>
  <div style="margin-bottom:12px;font-size:.83rem;color:var(--muted)">Sélectionnez une clé puis lancez le scan complet pour tester tous les modèles disponibles.</div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px">
    <select id="scan-key"></select>
    <button class="btn btn-primary" id="btn-scan" onclick="startScan()">🚀 Scan complet (<?= count(getAllModels()) ?> modèles)</button>
  </div>
  <div id="scan-status" style="font-size:.82rem;color:var(--muted);margin-bottom:8px"></div>
  <div class="progress"><div class="progress-fill" id="progress-fill"></div></div>
</div>

<div class="card">
  <h3>État des modèles</h3>
  <table id="models-table">
    <thead><tr><th>Modèle</th><th>Statut</th><th>TPM</th><th>MPM</th><th>RPS</th><th>Tokens session</th><th>Dernier test</th></tr></thead>
    <tbody></tbody>
  </table>
</div>
</div>

<script>
const MODELS = <?= json_encode(getAllModels()) ?>;

async function addKey() {
  const key    = document.getElementById('new_key').value.trim();
  const pseudo = document.getElementById('pseudo').value.trim() || 'user';
  if (!key) return;
  const fd = new FormData();
  fd.append('action','add_key'); fd.append('key',key); fd.append('pseudo',pseudo);
  await fetch('',{method:'POST',body:fd});
  location.reload();
}

async function loadData() {
  const fd = new FormData();
  fd.append('action','get_data');
  const r = await fetch('',{method:'POST',body:fd});
  const d = await r.json();

  const info = document.getElementById('active-info');
  if (d.active_pseudo) { info.style.display='block'; info.textContent='✓ Clé active : ' + d.active_pseudo; }

  const kt = document.querySelector('#keys-table tbody');
  kt.innerHTML = d.keys.map(k=>`
    <tr>
      <td>${k.pseudo}</td>
      <td><code style="font-size:.78rem">${k.key_masked}</code></td>
      <td>
        <button class="btn-sm" onclick="setActive('${k.key_val}','${k.pseudo}')">Activer</button>
        &nbsp;
        <button class="btn-sm" onclick="document.getElementById('scan-key').value='${k.key_val}'">Sélectionner pour scan</button>
      </td>
    </tr>
  `).join('');

  const sk = document.getElementById('scan-key');
  sk.innerHTML = d.keys.map(k=>`<option value="${k.key_val}">${k.pseudo}</option>`).join('');

  const mt = document.querySelector('#models-table tbody');
  mt.innerHTML = d.models.map(m=>`
    <tr>
      <td><strong style="font-size:.82rem">${m.model_name}</strong></td>
      <td class="${m.last_status==='OK'?'ok':m.last_status?'err':'pending'}">${m.last_status||'–'}</td>
      <td>${m.limit_tpm?.toLocaleString()||'–'}</td>
      <td>${m.limit_mpm||'–'}</td>
      <td>${m.limit_rps||'–'}</td>
      <td>${m.used_tokens_session||0}</td>
      <td style="font-size:.72rem;color:var(--muted)">${m.last_tested||'Jamais'}</td>
    </tr>
  `).join('');
}

async function setActive(key, pseudo) {
  const fd = new FormData();
  fd.append('action','set_active'); fd.append('key',key); fd.append('pseudo',pseudo);
  await fetch('',{method:'POST',body:fd});
  loadData();
}

async function startScan() {
  const key = document.getElementById('scan-key').value;
  if (!key) { alert('Sélectionnez une clé'); return; }
  document.getElementById('btn-scan').disabled = true;

  for (let i=0; i<MODELS.length; i++) {
    const m = MODELS[i];
    const pct = Math.round((i+1)/MODELS.length*100);
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('scan-status').textContent = `Test ${i+1}/${MODELS.length} : ${m.name}`;

    const fd = new FormData();
    fd.append('action','test_model');
    fd.append('key',key); fd.append('model',m.name);
    fd.append('limit_tpm',m.tpm); fd.append('limit_mpm',m.mpm); fd.append('limit_rps',m.rps);
    try { await fetch('',{method:'POST',body:fd}); } catch(e) {}

    await new Promise(r=>setTimeout(r,1200));
    if ((i+1) % 5 === 0) loadData();
  }

  document.getElementById('scan-status').textContent = '✅ Scan terminé !';
  document.getElementById('btn-scan').disabled = false;
  loadData();
}

loadData();
</script>
</body>
</html>
<?php

function getAllModels(): array {
    return [
        ['name'=>'mistral-small-latest',   'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'mistral-medium-2508',     'tpm'=>375000,   'mpm'=>'-',   'rps'=>0.42],
        ['name'=>'mistral-large-2411',      'tpm'=>600000,   'mpm'=>'200B','rps'=>1.00],
        ['name'=>'mistral-large-2512',      'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'codestral-2508',          'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'devstral-2512',           'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'ministral-8b-2512',       'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'ministral-3b-2512',       'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'open-mistral-nemo',       'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'pixtral-large-2411',      'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'magistral-medium-2509',   'tpm'=>75000,    'mpm'=>'1B',  'rps'=>0.08],
        ['name'=>'magistral-small-2509',    'tpm'=>75000,    'mpm'=>'1B',  'rps'=>0.08],
        ['name'=>'mistral-small-2506',      'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'mistral-small-2603',      'tpm'=>375000,   'mpm'=>'-',   'rps'=>1.00],
        ['name'=>'devstral-small-2507',     'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'devstral-medium-2507',    'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'mistral-medium-2505',     'tpm'=>375000,   'mpm'=>'-',   'rps'=>0.42],
        ['name'=>'pixtral-12b-2409',        'tpm'=>50000,    'mpm'=>'4M',  'rps'=>1.00],
        ['name'=>'ministral-14b-2512',      'tpm'=>50000,    'mpm'=>'4M',  'rps'=>0.50],
        ['name'=>'mistral-embed-2312',      'tpm'=>20000000, 'mpm'=>'200B','rps'=>1.00],
    ];
}
