<?php
/**
 * NEXUS V2 — Audit Serveur
 * Génère le contexte d'exécution pour l'agent IA
 */

$os              = php_uname('s') . ' ' . php_uname('r');
$php_version     = phpversion();
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'CLI';
$current_dir     = __DIR__;
$is_writable     = is_writable($current_dir) ? 'OUI' : 'NON';
$free_space      = function_exists('disk_free_space') ? round(disk_free_space($current_dir)/(1024**3),2).' GB' : 'Inconnu';

$ini = [
    'memory_limit'       => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'post_max_size'      => ini_get('post_max_size'),
    'upload_max_filesize'=> ini_get('upload_max_filesize'),
    'allow_url_fopen'    => ini_get('allow_url_fopen') ? 'Activé' : 'Désactivé',
    'allow_url_include'  => ini_get('allow_url_include') ? 'Activé' : 'Désactivé',
    'open_basedir'       => ini_get('open_basedir') ?: 'Aucune restriction',
    'disable_functions'  => ini_get('disable_functions') ?: 'Aucune',
];

$extensions = get_loaded_extensions();
natcasesort($extensions);

$caps = [
    'Réseau'    => ['curl','sockets','soap','ftp'],
    'DB'        => ['pdo','pdo_sqlite','sqlite3','pdo_mysql','mysqli'],
    'XML/Data'  => ['simplexml','dom','json','mbstring','xml'],
    'Sécurité'  => ['openssl','hash','filter','sodium'],
    'Fichiers'  => ['zip','zlib','fileinfo'],
    'Images'    => ['gd','imagick'],
];

$report = [];
foreach ($caps as $cat => $list) {
    $ok  = array_intersect($list, $extensions);
    $mis = array_diff($list, $extensions);
    $report[$cat] = ['ok'=>array_values($ok),'missing'=>array_values($mis)];
}

// Construire le system prompt
$prompt = "Tu es NEXUS, un Agent IA Autonome de développement web. Adapte STRICTEMENT ton code à cet environnement.\n\n";
$prompt .= "### ENVIRONNEMENT\n- OS: $os\n- Serveur: $server_software\n- PHP: $php_version\n";
$prompt .= "- Dossier: $current_dir\n- Écriture: $is_writable\n- Espace: $free_space\n\n";
$prompt .= "### LIMITES\n";
foreach ($ini as $k => $v) $prompt .= "- $k: $v\n";
$prompt .= "\n### CAPACITÉS\n";
foreach ($report as $cat => $info) {
    $prompt .= "- $cat: " . implode(', ', $info['ok'] ?: ['–']);
    if ($info['missing']) $prompt .= " [Absent: ".implode(', ',$info['missing'])."]";
    $prompt .= "\n";
}
$prompt .= "\n### RÈGLES\n";
$prompt .= "1. Vérifie function_exists() avant usage\n";
$prompt .= "2. Utilise cURL si dispo, sinon file_get_contents si allow_url_fopen=ON\n";
$prompt .= "3. SQLite uniquement (pas MySQL externe)\n";
$prompt .= "4. Pas d'exec/shell_exec/system/passthru\n";
$prompt .= "5. try/catch sur tous les appels API et I/O\n";

file_put_contents(__DIR__ . '/serveur.txt', $prompt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>NEXUS — Audit Serveur</title>
<style>
body{font-family:ui-monospace,monospace;background:#0a0e17;color:#cdd8ee;padding:24px}
.container{max-width:900px;margin:auto}
h1{color:#4f9eff;border-bottom:1px solid #1e2d47;padding-bottom:10px;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.card{background:#141c2e;border:1px solid #1e2d47;border-radius:10px;padding:16px}
h3{color:#4f9eff;font-size:.85rem;margin-bottom:10px}
.ok{color:#2dd4a0} .warn{color:#f59e0b} .err{color:#f87171}
ul{padding-left:18px;font-size:.82rem;line-height:1.8}
.prompt-box{background:#05080f;padding:18px;border-radius:10px;white-space:pre-wrap;font-size:.78rem;color:#7dd3fc;border:1px solid #1e2d47;max-height:400px;overflow-y:auto;margin-top:16px}
.btn{padding:9px 18px;background:#2dd4a0;color:#000;border:none;border-radius:7px;cursor:pointer;font-weight:700;margin-bottom:14px}
.back{display:inline-block;padding:8px 16px;background:#141c2e;border:1px solid #1e2d47;color:#4f9eff;text-decoration:none;border-radius:7px;font-size:.83rem;margin-bottom:16px}
</style>
</head>
<body>
<div class="container">
<a class="back" href="index.php">← Retour Dashboard</a>
<h1>⚙️ Audit Serveur NEXUS V2</h1>
<p style="color:#7a8aaa;margin-bottom:20px;font-size:.85rem">✅ <strong>serveur.txt</strong> généré avec succès.</p>

<div class="grid">
  <div class="card">
    <h3>📁 Système de Fichiers</h3>
    <ul>
      <li>PHP : <strong><?= $php_version ?></strong></li>
      <li>Écriture : <span class="<?= $is_writable==='OUI'?'ok':'err' ?>"><?= $is_writable ?></span></li>
      <li>Espace : <strong><?= $free_space ?></strong></li>
      <li>open_basedir : <span class="<?= $ini['open_basedir']==='Aucune restriction'?'ok':'warn' ?>"><?= $ini['open_basedir']==='Aucune restriction'?'OFF':$ini['open_basedir'] ?></span></li>
    </ul>
  </div>
  <div class="card">
    <h3>⚡ Performances</h3>
    <ul>
      <li>Mémoire : <strong><?= $ini['memory_limit'] ?></strong></li>
      <li>Timeout : <strong><?= $ini['max_execution_time'] ?>s</strong></li>
      <li>Upload max : <strong><?= $ini['upload_max_filesize'] ?></strong></li>
      <li>allow_url_fopen : <span class="<?= $ini['allow_url_fopen']==='Activé'?'ok':'err' ?>"><?= $ini['allow_url_fopen'] ?></span></li>
    </ul>
  </div>
  <div class="card">
    <h3>🔒 Sécurité</h3>
    <ul>
      <li>Fonctions bloquées : <span class="<?= $ini['disable_functions']==='Aucune'?'ok':'warn' ?>"><?= $ini['disable_functions']==='Aucune'?'0':'Oui' ?></span></li>
      <li>cURL : <span class="<?= in_array('curl',$extensions)?'ok':'err' ?>"><?= in_array('curl',$extensions)?'✓':'✗' ?></span></li>
      <li>SimpleXML : <span class="<?= in_array('simplexml',$extensions)||in_array('SimpleXML',$extensions)?'ok':'err' ?>"><?= in_array('simplexml',$extensions)||in_array('SimpleXML',$extensions)?'✓':'✗' ?></span></li>
      <li>SQLite3 : <span class="<?= class_exists('SQLite3')?'ok':'err' ?>"><?= class_exists('SQLite3')?'✓':'✗' ?></span></li>
    </ul>
  </div>
</div>

<?php foreach ($report as $cat => $info): ?>
<div class="card" style="margin-bottom:12px">
  <h3><?= $cat ?></h3>
  <div style="font-size:.82rem">
    <?php foreach ($info['ok'] as $e): ?><span class="ok" style="margin-right:10px">✓ <?= $e ?></span><?php endforeach; ?>
    <?php foreach ($info['missing'] as $e): ?><span class="err" style="margin-right:10px">✗ <?= $e ?></span><?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<h3 style="margin-top:24px;color:#4f9eff">System Prompt généré</h3>
<button class="btn" onclick="navigator.clipboard.writeText(document.getElementById('prompt').innerText).then(()=>alert('Copié !'))">Copier le prompt</button>
<div class="prompt-box" id="prompt"><?= htmlspecialchars($prompt) ?></div>
</div>
</body>
</html>
