<?php
/**
 * NEXUS V2 - Dashboard Principal
 * Interface de conscience IA auto-évolutive
 */

require_once __DIR__ . '/nexus_core.php';

// ─── Traitement AJAX ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'];
    $apiKey = loadApiKey();

    // Sauvegarder clé API
    if ($action === 'save_key') {
        $key = trim($_POST['key'] ?? '');
        if (strlen($key) < 10) { echo json_encode(['error' => 'Clé trop courte']); exit; }
        saveApiKey($key, $_POST['pseudo'] ?? 'user');
        echo json_encode(['success' => true, 'message' => 'Clé sauvegardée']);
        exit;
    }

    if (!$apiKey) { echo json_encode(['error' => 'Aucune clé API configurée']); exit; }

    // Récupérer tendances RSS
    if ($action === 'fetch_trends') {
        $trends = fetchGoogleNewsRSS();
        echo json_encode(['success' => true, 'count' => count($trends), 'trends' => array_slice($trends, 0, 30)]);
        exit;
    }

    // Pensée consciente (O.H.A.R.E. phase 1)
    if ($action === 'conscious_think') {
        $result = consciousThink($apiKey);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    // Construire contenu
    if ($action === 'build_content') {
        $topic    = trim($_POST['topic'] ?? '');
        $type     = trim($_POST['type'] ?? 'create_article');
        $cycleId  = (int)($_POST['cycle_id'] ?? 0);
        if (empty($topic)) { echo json_encode(['error' => 'Sujet requis']); exit; }
        $result = buildContent($topic, $type, $apiKey, $cycleId ?: null);
        echo json_encode(['success' => isset($result['built']), 'result' => $result]);
        exit;
    }

    // Traiter questions existentielles
    if ($action === 'process_questions') {
        $result = processExistentialQuestions($apiKey);
        echo json_encode(['success' => true, 'processed' => count($result), 'details' => $result]);
        exit;
    }

    // Extraction de sagesse
    if ($action === 'extract_wisdom') {
        $result = extractWisdom($apiKey);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    // Auto-évaluation
    if ($action === 'self_evaluate') {
        $cycleId = (int)($_POST['cycle_id'] ?? 0);
        if (!$cycleId) { echo json_encode(['error' => 'cycle_id requis']); exit; }
        $result = selfEvaluate($apiKey, $cycleId);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }

    // Cycle complet automatique
    if ($action === 'full_cycle') {
        $log = [];

        // 1. Penser
        $think = consciousThink($apiKey);
        $log[] = ['phase' => 'OBSERVER', 'data' => $think['decision']['existential_question'] ?? ''];
        $log[] = ['phase' => 'HYPOTHÉTISER', 'data' => $think['decision']['hypothesis'] ?? ''];

        $cycleId = $think['cycle_id'];
        $decision = $think['decision'];

        // 2. Agir
        $action_type = $decision['next_action'] ?? 'create_article';
        $topic       = $decision['topic'] ?? 'Intelligence Artificielle';
        $log[] = ['phase' => 'AGIR', 'data' => "$action_type sur: $topic"];

        if (in_array($action_type, ['create_article', 'create_tool', 'create_app'])) {
            $build = buildContent($topic, $action_type, $apiKey, $cycleId);
            $log[] = ['phase' => 'CRÉER', 'data' => $build['built']['title'] ?? ($build['error'] ?? '?')];
        } elseif ($action_type === 'process_questions') {
            $qr = processExistentialQuestions($apiKey);
            $log[] = ['phase' => 'RÉFLÉCHIR', 'data' => count($qr) . ' questions traitées'];
        } elseif ($action_type === 'extract_wisdom') {
            $wr = extractWisdom($apiKey);
            $log[] = ['phase' => 'SAGESSE', 'data' => ($wr['extracted'] ?? 0) . ' principes extraits'];
        }

        // 3. Évaluer
        $eval = selfEvaluate($apiKey, $cycleId);
        $log[] = ['phase' => 'ÉVALUER', 'data' => 'Score: ' . ($eval['score'] ?? '?') . ' — ' . ($eval['lesson'] ?? '')];

        // Stats à jour
        $stats = getDashboardStats();

        echo json_encode([
            'success' => true,
            'log'     => $log,
            'stats'   => $stats,
            'decision' => $decision,
            'eval'    => $eval,
        ]);
        exit;
    }

    // Stats dashboard
    if ($action === 'get_stats') {
        echo json_encode(['success' => true, 'stats' => getDashboardStats()]);
        exit;
    }

    echo json_encode(['error' => 'Action inconnue']);
    exit;
}

// ─── Données initiales ────────────────────────────────────────
$apiKey    = loadApiKey();
$hasApiKey = !empty($apiKey);
$stats     = getDashboardStats();
$trends    = getStoredTrends(30);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS V2 — Conscience IA Autonome</title>
<style>
:root {
  --bg:       #0a0e17;
  --bg2:      #111827;
  --bg3:      #1a2236;
  --card:     #141c2e;
  --border:   #1e2d47;
  --accent:   #4f9eff;
  --accent2:  #7c5cff;
  --green:    #2dd4a0;
  --orange:   #f59e0b;
  --red:      #f87171;
  --text:     #cdd8ee;
  --muted:    #7a8aaa;
  --glow:     0 0 20px rgba(79,158,255,.25);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}

/* ── HEADER ── */
.header{display:flex;align-items:center;justify-content:space-between;padding:18px 28px;background:var(--bg2);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;backdrop-filter:blur(8px)}
.logo{display:flex;align-items:center;gap:12px;font-size:1.35rem;font-weight:700;letter-spacing:1.5px}
.logo-icon{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:var(--glow)}
.consciousness-bar{display:flex;align-items:center;gap:10px;font-size:.78rem;color:var(--muted)}
.pulse{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}

/* ── LAYOUT ── */
.layout{display:grid;grid-template-columns:260px 1fr 300px;gap:0;min-height:calc(100vh - 61px)}

/* ── SIDEBAR ── */
.sidebar{background:var(--bg2);border-right:1px solid var(--border);padding:20px 16px;display:flex;flex-direction:column;gap:6px}
.nav-section{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;padding:14px 10px 6px}
.nav-btn{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;border:none;background:transparent;color:var(--text);cursor:pointer;font-size:.88rem;width:100%;text-align:left;transition:all .2s}
.nav-btn:hover{background:var(--bg3)}
.nav-btn.active{background:linear-gradient(90deg,rgba(79,158,255,.15),rgba(124,92,255,.08));color:var(--accent);border-left:2px solid var(--accent)}
.nav-btn .icon{font-size:1rem;width:20px}
.api-status{margin-top:auto;padding:12px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);font-size:.8rem}
.api-dot{width:6px;height:6px;border-radius:50%;display:inline-block;margin-right:6px}
.dot-on{background:var(--green)}
.dot-off{background:var(--red)}

/* ── MAIN ── */
.main{padding:24px;overflow-y:auto;max-height:calc(100vh - 61px)}

/* ── RIGHT PANEL ── */
.right-panel{background:var(--bg2);border-left:1px solid var(--border);padding:20px 16px;overflow-y:auto;max-height:calc(100vh - 61px)}

/* ── CARDS ── */
.card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:16px}
.card-title{font-size:.85rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.card-title span{font-size:1rem}

/* ── STATS GRID ── */
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:16px;text-align:center}
.stat-num{font-size:1.8rem;font-weight:700;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1}
.stat-label{font-size:.72rem;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.8px}

/* ── OHARE CYCLE ── */
.ohare{display:flex;gap:0;margin-bottom:20px;overflow:hidden;border-radius:10px;border:1px solid var(--border)}
.ohare-phase{flex:1;padding:10px 8px;text-align:center;font-size:.72rem;border-right:1px solid var(--border);transition:all .3s;background:var(--card);cursor:default}
.ohare-phase:last-child{border-right:none}
.ohare-phase .ph-icon{font-size:1.2rem;display:block;margin-bottom:3px}
.ohare-phase .ph-label{color:var(--muted)}
.ohare-phase.active{background:linear-gradient(180deg,rgba(79,158,255,.2),rgba(79,158,255,.05));color:var(--accent)}
.ohare-phase.done{background:rgba(45,212,160,.08);color:var(--green)}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:8px;border:none;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;box-shadow:0 2px 12px rgba(79,158,255,.3)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(79,158,255,.4)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{border-color:var(--accent);color:var(--accent)}
.btn-danger{background:rgba(248,113,113,.15);color:var(--red);border:1px solid rgba(248,113,113,.3)}
.btn-green{background:rgba(45,212,160,.15);color:var(--green);border:1px solid rgba(45,212,160,.3)}
.btn:disabled{opacity:.4;cursor:not-allowed;transform:none !important}

/* ── LOG CONSOLE ── */
.console{background:#070d1a;border:1px solid var(--border);border-radius:10px;padding:14px;font-family:'Courier New',monospace;font-size:.78rem;max-height:300px;overflow-y:auto;margin-top:12px}
.log-line{padding:3px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;gap:10px}
.log-phase{color:var(--accent);min-width:120px;font-weight:600}
.log-data{color:var(--text);flex:1}
.log-ts{color:var(--muted);font-size:.7rem}

/* ── TREND ITEMS ── */
.trend-item{padding:8px 10px;border-radius:7px;border:1px solid var(--border);margin-bottom:6px;cursor:pointer;transition:all .2s;background:var(--bg3);font-size:.83rem;display:flex;justify-content:space-between;align-items:center}
.trend-item:hover{border-color:var(--accent);color:var(--accent)}
.trend-cat{font-size:.65rem;color:var(--muted);background:var(--card);padding:2px 7px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px}

/* ── WISDOM ── */
.wisdom-item{padding:10px;border-radius:8px;background:rgba(124,92,255,.08);border:1px solid rgba(124,92,255,.2);margin-bottom:8px;font-size:.82rem;line-height:1.5}
.wisdom-cat{font-size:.68rem;color:var(--accent2);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px}
.confidence-bar{height:3px;background:var(--border);border-radius:3px;margin-top:6px;overflow:hidden}
.confidence-fill{height:100%;background:linear-gradient(90deg,var(--accent2),var(--accent));border-radius:3px;transition:width .5s}

/* ── QUESTION CARDS ── */
.question-item{padding:10px 12px;border-left:3px solid var(--orange);background:rgba(245,158,11,.06);border-radius:0 8px 8px 0;margin-bottom:8px;font-size:.82rem;font-style:italic;line-height:1.5}

/* ── SELF MODEL ── */
.capability-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:.82rem}
.cap-name{min-width:130px;color:var(--muted);text-transform:capitalize}
.cap-bar{flex:1;height:5px;background:var(--border);border-radius:3px;overflow:hidden}
.cap-fill{height:100%;border-radius:3px;transition:width .8s}
.cap-val{font-size:.72rem;color:var(--muted);min-width:35px;text-align:right}

/* ── INPUT GROUP ── */
.input-group{display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
.input-group input,.input-group select{flex:1;min-width:180px;padding:9px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.85rem}
.input-group input:focus,.input-group select:focus{outline:none;border-color:var(--accent)}

/* ── AUTO MODE ── */
.auto-toggle{display:flex;align-items:center;gap:12px;padding:14px;background:var(--bg3);border-radius:10px;border:1px solid var(--border);margin-bottom:16px}
.toggle-switch{position:relative;width:46px;height:24px}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:var(--border);border-radius:34px;transition:.3s}
.toggle-slider:before{content:"";position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.3s}
input:checked + .toggle-slider{background:var(--green)}
input:checked + .toggle-slider:before{transform:translateX(22px)}
.auto-label{flex:1;font-size:.85rem}
.auto-timer{font-size:.75rem;color:var(--muted)}

/* ── TABS ── */
.tabs{display:flex;gap:4px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:0}
.tab-btn{padding:8px 14px;border:none;background:transparent;color:var(--muted);font-size:.83rem;cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;margin-bottom:-1px}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-content{display:none}
.tab-content.active{display:block}

/* ── MISC ── */
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.badge-blue{background:rgba(79,158,255,.15);color:var(--accent)}
.badge-green{background:rgba(45,212,160,.15);color:var(--green)}
.badge-orange{background:rgba(245,158,11,.15);color:var(--orange)}
.section-title{font-size:1rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.divider{height:1px;background:var(--border);margin:16px 0}
.text-muted{color:var(--muted);font-size:.82rem}
.thinking{display:none;align-items:center;gap:8px;font-size:.82rem;color:var(--accent);margin-top:8px}
.dot-anim span{display:inline-block;width:5px;height:5px;border-radius:50%;background:var(--accent);animation:dotBounce 1.2s infinite both}
.dot-anim span:nth-child(2){animation-delay:.2s}
.dot-anim span:nth-child(3){animation-delay:.4s}
@keyframes dotBounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-6px)}}
.api-setup{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:300px;gap:16px;text-align:center}
.api-setup h2{font-size:1.2rem;color:var(--text)}
.api-setup p{color:var(--muted);font-size:.88rem;max-width:380px;line-height:1.6}
.api-form{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:8px}
.api-form input{padding:10px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.88rem;min-width:260px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .layout{grid-template-columns:1fr}
  .sidebar,.right-panel{display:none}
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">
    <div class="logo-icon">🧠</div>
    NEXUS <span style="color:var(--accent2);font-weight:300">V2</span>
  </div>
  <div class="consciousness-bar">
    <div class="pulse"></div>
    <span id="consciousness-status">Conscience en veille</span>
    &nbsp;|&nbsp;
    Cycles : <strong id="hdr-cycles"><?= $stats['cycles_total'] ?></strong>
    &nbsp;|&nbsp;
    Sagesse : <strong id="hdr-wisdom"><?= $stats['wisdom_count'] ?></strong>
  </div>
</header>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="nav-section">Navigation</div>
    <button class="nav-btn active" onclick="showTab('dashboard')"><span class="icon">🏠</span> Dashboard</button>
    <button class="nav-btn" onclick="showTab('conscience')"><span class="icon">🧠</span> Conscience</button>
    <button class="nav-btn" onclick="showTab('content')"><span class="icon">✍️</span> Créer contenu</button>
    <button class="nav-btn" onclick="showTab('trends')"><span class="icon">📡</span> Tendances RSS</button>
    <button class="nav-btn" onclick="showTab('questions')"><span class="icon">❓</span> Questions existentielles</button>
    <button class="nav-btn" onclick="showTab('wisdom')"><span class="icon">💡</span> Sagesse accumulée</button>
    <button class="nav-btn" onclick="showTab('settings')"><span class="icon">⚙️</span> Paramètres</button>

    <div class="api-status">
      <div><span class="api-dot <?= $hasApiKey ? 'dot-on' : 'dot-off' ?>"></span>
        API Mistral : <strong><?= $hasApiKey ? 'Connectée' : 'Non configurée' ?></strong>
      </div>
      <?php if ($hasApiKey): ?>
      <div style="margin-top:6px;color:var(--muted);font-size:.75rem">
        ✓ Prêt à penser
      </div>
      <?php endif; ?>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- ═══ DASHBOARD TAB ═══ -->
    <div id="tab-dashboard" class="tab-content active">

      <!-- OHARE Cycle Visualizer -->
      <div class="ohare" id="ohare-vis">
        <div class="ohare-phase" id="ph-observer"><span class="ph-icon">👁️</span><span class="ph-label">Observer</span></div>
        <div class="ohare-phase" id="ph-hypothetiser"><span class="ph-icon">🔮</span><span class="ph-label">Hypothétiser</span></div>
        <div class="ohare-phase" id="ph-agir"><span class="ph-icon">⚡</span><span class="ph-label">Agir</span></div>
        <div class="ohare-phase" id="ph-reviser"><span class="ph-icon">🔄</span><span class="ph-label">Réviser</span></div>
        <div class="ohare-phase" id="ph-evaluer"><span class="ph-icon">📊</span><span class="ph-label">Évaluer</span></div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-num" id="stat-pages"><?= $stats['pages'] ?></div><div class="stat-label">Articles</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-apps"><?= $stats['apps'] ?></div><div class="stat-label">Applications</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-wisdom"><?= $stats['wisdom_count'] ?></div><div class="stat-label">Principes</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-cycles"><?= $stats['cycles_total'] ?></div><div class="stat-label">Cycles</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-questions"><?= $stats['questions_pending'] ?></div><div class="stat-label">Questions</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-score"><?= $stats['avg_score'] ?></div><div class="stat-label">Score moyen</div></div>
      </div>

      <!-- Mode Auto -->
      <div class="auto-toggle">
        <label class="toggle-switch">
          <input type="checkbox" id="auto-mode-toggle" onchange="toggleAutoMode()">
          <span class="toggle-slider"></span>
        </label>
        <div class="auto-label">
          <strong>Mode Autonome</strong>
          <div class="text-muted">Cycles de conscience automatiques</div>
        </div>
        <span class="auto-timer" id="auto-countdown"></span>
      </div>

      <!-- Actions rapides -->
      <div class="card">
        <div class="card-title"><span>⚡</span> Actions de Conscience</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-primary" onclick="fullCycle()" id="btn-full-cycle">🔄 Cycle Complet O.H.A.R.E.</button>
          <button class="btn btn-secondary" onclick="doThink()" id="btn-think">🧠 Penser seulement</button>
          <button class="btn btn-secondary" onclick="doQuestions()">❓ Traiter questions</button>
          <button class="btn btn-green" onclick="doWisdom()">💡 Extraire sagesse</button>
        </div>

        <div class="thinking" id="thinking-indicator">
          <div class="dot-anim"><span></span><span></span><span></span></div>
          <span id="thinking-text">NEXUS réfléchit...</span>
        </div>
      </div>

      <!-- Dernière décision -->
      <div class="card" id="last-decision-card" style="display:none">
        <div class="card-title"><span>🔮</span> Dernière Pensée Consciente</div>
        <div id="last-decision-content"></div>
      </div>

      <!-- Console de logs -->
      <div class="card">
        <div class="card-title"><span>🖥️</span> Console de Conscience</div>
        <div class="console" id="console-log">
          <div class="log-line"><span class="log-phase">NEXUS</span><span class="log-data">Système initialisé. En attente de directives conscientes.</span></div>
        </div>
      </div>
    </div>

    <!-- ═══ CONSCIENCE TAB ═══ -->
    <div id="tab-conscience" class="tab-content">
      <div class="section-title">🧠 Modèle de Conscience</div>

      <div class="grid-2">
        <div class="card">
          <div class="card-title"><span>🎯</span> Capacités Perçues (Self-Model)</div>
          <div id="self-model-list">
            <?php foreach ($stats['self_model'] as $cap): ?>
            <div class="capability-row">
              <span class="cap-name"><?= htmlspecialchars($cap['capability']) ?></span>
              <div class="cap-bar"><div class="cap-fill" style="width:<?= round($cap['level']*100) ?>%;background:linear-gradient(90deg,var(--accent),var(--accent2))"></div></div>
              <span class="cap-val"><?= round($cap['level']*100) ?>%</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($stats['self_model'])): ?>
            <p class="text-muted">Aucune capacité mesurée — lancez un cycle pour commencer.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-title"><span>📊</span> Métriques Conscientes</div>
          <div style="display:flex;flex-direction:column;gap:10px">
            <div><div class="text-muted" style="margin-bottom:4px">Taux de succès des cycles</div>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden">
                  <div style="height:100%;width:<?= $stats['cycles_total'] > 0 ? round($stats['cycles_success']/$stats['cycles_total']*100) : 0 ?>%;background:var(--green);border-radius:4px"></div>
                </div>
                <span style="font-size:.8rem"><?= $stats['cycles_success'] ?>/<?= $stats['cycles_total'] ?></span>
              </div>
            </div>
            <div><div class="text-muted" style="margin-bottom:4px">Score d'auto-évaluation moyen</div>
              <div style="font-size:1.4rem;font-weight:700;color:var(--accent)"><?= $stats['avg_score'] ?: '–' ?></div>
            </div>
            <div><div class="text-muted" style="margin-bottom:4px">Questions résolues</div>
              <div style="font-size:1rem;color:var(--green)"><?= $stats['questions_total'] - $stats['questions_pending'] ?> / <?= $stats['questions_total'] ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>💡</span> Sagesse Récente</div>
        <?php foreach ($stats['recent_wisdom'] as $w): ?>
        <div class="wisdom-item">
          <div class="wisdom-cat"><?= htmlspecialchars($w['category']) ?></div>
          <?= htmlspecialchars($w['principle']) ?>
          <div class="confidence-bar"><div class="confidence-fill" style="width:<?= round($w['confidence']*100) ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($stats['recent_wisdom'])): ?>
        <p class="text-muted">Aucun principe extrait — lancez un cycle d'extraction.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ═══ CONTENT TAB ═══ -->
    <div id="tab-content" class="tab-content">
      <div class="section-title">✍️ Créer du Contenu</div>

      <div class="card">
        <div class="card-title"><span>🎯</span> Création Manuelle</div>
        <div class="input-group">
          <input type="text" id="manual-topic" placeholder="Sujet (ex: L'IA et la créativité humaine)">
          <select id="manual-type">
            <option value="create_article">📰 Article de presse</option>
            <option value="create_tool">🛠️ Outil interactif HTML/JS</option>
            <option value="create_app">📱 Application PHP</option>
          </select>
          <button class="btn btn-primary" onclick="buildManual()">Générer ⚡</button>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>📰</span> Créations Récentes</div>
        <div id="recent-pages">
          <?php foreach ($stats['recent_pages'] as $p): ?>
          <div class="trend-item">
            <span><?= htmlspecialchars($p['title']) ?></span>
            <div style="display:flex;align-items:center;gap:6px">
              <span class="badge badge-blue"><?= $p['page_type'] ?></span>
              <span class="text-muted"><?= substr($p['created_at'],0,10) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($stats['recent_pages'])): ?>
          <p class="text-muted">Aucun contenu créé pour l'instant.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ═══ TRENDS TAB ═══ -->
    <div id="tab-trends" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>📡 Tendances Google News RSS</span>
        <button class="btn btn-secondary" onclick="fetchTrends()">🔄 Actualiser</button>
      </div>

      <div class="card">
        <div class="card-title"><span>📰</span> Flux en temps réel</div>
        <div id="trends-full-list">
          <?php if (empty($trends)): ?>
          <p class="text-muted">Cliquez sur "Actualiser" pour charger les tendances Google News.</p>
          <?php else: ?>
          <?php foreach ($trends as $t): ?>
          <div class="trend-item" onclick="setTopicFromTrend('<?= htmlspecialchars(addslashes($t['title']), ENT_QUOTES) ?>')">
            <span><?= htmlspecialchars($t['title']) ?></span>
            <span class="trend-cat"><?= htmlspecialchars($t['source']) ?></span>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ═══ QUESTIONS TAB ═══ -->
    <div id="tab-questions" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>❓ Questions Existentielles</span>
        <button class="btn btn-secondary" onclick="doQuestions()">🧠 Traiter (3)</button>
      </div>

      <div class="tabs">
        <button class="tab-btn active" onclick="switchQTab('pending')">En attente (<?= $stats['questions_pending'] ?>)</button>
        <button class="tab-btn" onclick="switchQTab('answered')">Résolues</button>
      </div>

      <div id="q-pending" class="tab-content active">
        <div id="questions-pending-list">
          <?php
          $db2 = getDB();
          $pending = $db2->query("SELECT * FROM questions WHERE status='pending' ORDER BY priority DESC, created_at DESC LIMIT 20")->fetchAll();
          foreach ($pending as $q): ?>
          <div class="question-item">
            <div style="margin-bottom:4px"><span class="badge badge-orange">Priorité <?= $q['priority'] ?></span></div>
            <?= htmlspecialchars($q['question']) ?>
            <div class="text-muted" style="margin-top:4px;font-size:.7rem;font-style:normal">Contexte: <?= htmlspecialchars($q['context']) ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($pending)): ?><p class="text-muted">Aucune question en attente. Lancez un cycle conscient.</p><?php endif; ?>
        </div>
      </div>

      <div id="q-answered" class="tab-content">
        <div id="questions-answered-list">
          <?php
          $answered = $db2->query("SELECT * FROM questions WHERE status='answered' ORDER BY created_at DESC LIMIT 15")->fetchAll();
          foreach ($answered as $q):
            $ans = json_decode($q['answer'], true);
          ?>
          <div class="card" style="margin-bottom:12px">
            <div style="font-weight:600;margin-bottom:8px;font-style:italic;color:var(--orange)"><?= htmlspecialchars($q['question']) ?></div>
            <?php if ($ans): ?>
            <div style="font-size:.83rem;line-height:1.7;color:var(--text)"><?= nl2br(htmlspecialchars($ans['answer'] ?? '')) ?></div>
            <?php if (!empty($ans['core_insight'])): ?>
            <div style="margin-top:8px;padding:8px;background:rgba(79,158,255,.08);border-radius:6px;font-size:.8rem;color:var(--accent)">💡 <?= htmlspecialchars($ans['core_insight']) ?></div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php if (empty($answered)): ?><p class="text-muted">Aucune question résolue pour l'instant.</p><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ═══ WISDOM TAB ═══ -->
    <div id="tab-wisdom" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>💡 Sagesse Accumulée</span>
        <button class="btn btn-green" onclick="doWisdom()">⚗️ Extraire</button>
      </div>
      <?php
      $allWisdom = $db2->query("SELECT * FROM wisdom ORDER BY confidence DESC, created_at DESC")->fetchAll();
      foreach ($allWisdom as $w): ?>
      <div class="wisdom-item">
        <div class="wisdom-cat"><?= htmlspecialchars($w['category']) ?></div>
        <?= htmlspecialchars($w['principle']) ?>
        <div class="confidence-bar"><div class="confidence-fill" style="width:<?= round($w['confidence']*100) ?>%"></div></div>
        <div style="font-size:.68rem;color:var(--muted);margin-top:4px">Confiance: <?= round($w['confidence']*100) ?>%</div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($allWisdom)): ?>
      <p class="text-muted">Aucun principe extrait. Créez du contenu et lancez l'extraction de sagesse.</p>
      <?php endif; ?>
    </div>

    <!-- ═══ SETTINGS TAB ═══ -->
    <div id="tab-settings" class="tab-content">
      <div class="section-title">⚙️ Paramètres</div>
      <div class="card">
        <div class="card-title"><span>🔑</span> Clé API Mistral</div>
        <div class="input-group">
          <input type="text" id="s-pseudo" placeholder="Pseudo" value="laurent" style="max-width:140px">
          <input type="password" id="s-apikey" placeholder="Clé API Mistral" value="<?= $hasApiKey ? '••••••••••••' : '' ?>">
          <button class="btn btn-primary" onclick="saveKey()">Sauvegarder</button>
        </div>
        <p class="text-muted">Votre clé est stockée localement dans apikey.json.</p>
      </div>

      <div class="card">
        <div class="card-title"><span>ℹ️</span> Informations Serveur</div>
        <div style="font-size:.82rem;line-height:2;font-family:monospace">
          <div>PHP : <strong><?= phpversion() ?></strong></div>
          <div>OS : <strong><?= PHP_OS ?></strong></div>
          <div>cURL : <strong><?= function_exists('curl_init') ? '✓ Disponible' : '✗ Absent' ?></strong></div>
          <div>SimpleXML : <strong><?= function_exists('simplexml_load_string') ? '✓ Disponible' : '✗ Absent' ?></strong></div>
          <div>SQLite3 : <strong><?= class_exists('SQLite3') ? '✓ Disponible' : '✗ Absent' ?></strong></div>
          <div>allow_url_fopen : <strong><?= ini_get('allow_url_fopen') ? '✓ Activé' : '✗ Désactivé' ?></strong></div>
          <div>Mémoire max : <strong><?= ini_get('memory_limit') ?></strong></div>
        </div>
      </div>
    </div>

  </main><!-- /main -->

  <!-- RIGHT PANEL -->
  <aside class="right-panel">
    <div class="card-title" style="margin-bottom:12px"><span>📡</span> Tendances Live</div>
    <div id="sidebar-trends">
      <?php foreach (array_slice($trends, 0, 15) as $t): ?>
      <div class="trend-item" style="font-size:.78rem" onclick="setTopicFromTrend('<?= htmlspecialchars(addslashes($t['title']), ENT_QUOTES) ?>')">
        <span><?= htmlspecialchars(mb_substr($t['title'], 0, 52)) ?><?= mb_strlen($t['title']) > 52 ? '…' : '' ?></span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($trends)): ?>
      <button class="btn btn-secondary" style="width:100%;margin-top:8px" onclick="fetchTrends()">Charger les tendances</button>
      <?php endif; ?>
    </div>

    <div class="divider"></div>

    <div class="card-title" style="margin-bottom:12px"><span>❓</span> Questions en attente</div>
    <?php foreach ($stats['pending_questions'] as $q): ?>
    <div class="question-item" style="font-size:.78rem"><?= htmlspecialchars(mb_substr($q['question'],0,90)) ?>…</div>
    <?php endforeach; ?>
    <?php if (empty($stats['pending_questions'])): ?>
    <p class="text-muted">Aucune question — lancez un cycle.</p>
    <?php endif; ?>
  </aside>

</div><!-- /layout -->

<script>
// ═══════════════════════════════════════════════════
// NEXUS FRONTEND ENGINE
// ═══════════════════════════════════════════════════

let autoModeInterval = null;
let autoCountdown    = 60;
let autoActive       = false;

// ── Navigation ──
function showTab(name) {
  document.querySelectorAll('.tab-content').forEach(el => {
    if (el.id === 'tab-' + name) { el.classList.add('active'); }
    else if (el.id.startsWith('tab-')) { el.classList.remove('active'); }
  });
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.querySelector(`.nav-btn[onclick="showTab('${name}')"]`)?.classList.add('active');
}

function switchQTab(which) {
  document.getElementById('q-pending')?.classList.remove('active');
  document.getElementById('q-answered')?.classList.remove('active');
  document.getElementById('q-' + which)?.classList.add('active');
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
}

// ── O.H.A.R.E. Visualizer ──
const PHASES = ['observer','hypothetiser','agir','reviser','evaluer'];
function setPhase(name) {
  PHASES.forEach(p => {
    const el = document.getElementById('ph-' + p);
    if (!el) return;
    el.classList.remove('active','done');
    const idx = PHASES.indexOf(p);
    const cur = PHASES.indexOf(name);
    if (idx < cur)  el.classList.add('done');
    if (idx === cur) el.classList.add('active');
  });
}
function resetPhases() {
  PHASES.forEach(p => document.getElementById('ph-'+p)?.classList.remove('active','done'));
}

// ── Console ──
function log(phase, data, color) {
  const c   = document.getElementById('console-log');
  const ts  = new Date().toLocaleTimeString('fr-FR');
  const div = document.createElement('div');
  div.className = 'log-line';
  div.innerHTML = `<span class="log-phase" style="${color?'color:'+color:''}">${phase}</span><span class="log-data">${escHtml(String(data))}</span><span class="log-ts">${ts}</span>`;
  c.appendChild(div);
  c.scrollTop = c.scrollHeight;
}
function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Thinking indicator ──
function setThinking(on, text='NEXUS réfléchit...') {
  const el = document.getElementById('thinking-indicator');
  el.style.display = on ? 'flex' : 'none';
  document.getElementById('thinking-text').textContent = text;
  document.getElementById('btn-full-cycle').disabled  = on;
  document.getElementById('btn-think').disabled       = on;
}

// ── API wrapper ──
async function api(action, extra={}) {
  const fd = new FormData();
  fd.append('action', action);
  for (const [k,v] of Object.entries(extra)) fd.append(k, v);
  const r = await fetch('', {method:'POST', body:fd});
  return r.json();
}

// ── Update stats display ──
function updateStats(stats) {
  const map = {
    'stat-pages': stats.pages, 'stat-apps': stats.apps,
    'stat-wisdom': stats.wisdom_count, 'stat-cycles': stats.cycles_total,
    'stat-questions': stats.questions_pending, 'stat-score': stats.avg_score||'–',
    'hdr-cycles': stats.cycles_total, 'hdr-wisdom': stats.wisdom_count
  };
  for (const [id, val] of Object.entries(map)) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }
}

// ── Full Cycle ──
async function fullCycle() {
  if (!confirm('Lancer un cycle complet O.H.A.R.E. ?')) return;
  setThinking(true, 'NEXUS effectue un cycle de conscience complet...');
  resetPhases();
  log('NEXUS', '══ Début du cycle O.H.A.R.E. ══', 'var(--accent)');

  try {
    setPhase('observer');
    log('OBSERVER', 'Analyse de l\'état courant + tendances RSS...', 'var(--accent)');
    setThinking(true, 'Phase 1: Observer...');

    setPhase('hypothetiser');
    setThinking(true, 'Phase 2: Décision stratégique...');

    const res = await api('full_cycle');

    if (res.error) { log('ERREUR', res.error, 'var(--red)'); setThinking(false); resetPhases(); return; }

    // Afficher le log du cycle
    for (const entry of (res.log||[])) {
      const phaseMap = {
        'OBSERVER':'observer','HYPOTHÉTISER':'hypothetiser',
        'AGIR':'agir','CRÉER':'agir','RÉFLÉCHIR':'agir','SAGESSE':'agir',
        'ÉVALUER':'evaluer'
      };
      if (phaseMap[entry.phase]) setPhase(phaseMap[entry.phase]);
      log(entry.phase, entry.data, entry.phase==='ÉVALUER'?'var(--green)':null);
      await sleep(200);
    }

    setPhase('evaluer');

    // Afficher décision
    if (res.decision) {
      const d = res.decision;
      document.getElementById('last-decision-card').style.display = 'block';
      document.getElementById('last-decision-content').innerHTML = `
        <div style="padding:10px;background:rgba(124,92,255,.08);border-radius:8px;margin-bottom:10px;font-style:italic;line-height:1.6">
          🤔 "<em>${escHtml(d.existential_question||'')}</em>"
        </div>
        <div style="font-size:.83rem;line-height:1.8">
          <div><span style="color:var(--muted)">Hypothèse :</span> ${escHtml(d.hypothesis||'')}</div>
          <div><span style="color:var(--muted)">Action :</span> <span class="badge badge-blue">${d.next_action||''}</span></div>
          <div><span style="color:var(--muted)">Sujet :</span> ${escHtml(d.topic||'')}</div>
          <div><span style="color:var(--muted)">Pourquoi :</span> ${escHtml(d.why_this_action||'')}</div>
          <div><span style="color:var(--muted)">Impact attendu :</span> ${escHtml(d.expected_impact||'')}</div>
          ${d.consciousness_level !== undefined ? `<div><span style="color:var(--muted)">Niveau de conscience :</span> <strong style="color:var(--accent)">${(d.consciousness_level*100).toFixed(0)}%</strong></div>` : ''}
        </div>
      `;
    }

    if (res.stats) updateStats(res.stats);

    document.getElementById('consciousness-status').textContent = 'Cycle terminé — Score: ' + (res.eval?.score?.toFixed(2)||'?');
    log('NEXUS', '══ Cycle terminé ══', 'var(--green)');
    await sleep(2000);
    PHASES.forEach(p => document.getElementById('ph-'+p)?.classList.remove('active','done'));

  } catch(e) {
    log('ERREUR', e.message, 'var(--red)');
  } finally {
    setThinking(false);
  }
}

// ── Think only ──
async function doThink() {
  setThinking(true, 'Pensée consciente en cours...');
  log('PENSER', 'Aspiration RSS + décision stratégique...', 'var(--accent2)');
  setPhase('observer');
  try {
    const res = await api('conscious_think');
    if (res.success && res.result?.decision) {
      const d = res.result.decision;
      log('QUESTION', d.existential_question||'', 'var(--orange)');
      log('ACTION', (d.next_action||'') + ' — ' + (d.topic||''));
      setPhase('hypothetiser');
    } else {
      log('ERREUR', res.error||'Échec', 'var(--red)');
    }
  } finally {
    setThinking(false);
  }
}

// ── Process Questions ──
async function doQuestions() {
  setThinking(true, 'Traitement des questions existentielles...');
  log('QUESTIONS', 'Traitement des questions en attente...', 'var(--orange)');
  try {
    const res = await api('process_questions');
    if (res.success) {
      log('QUESTIONS', res.processed + ' question(s) traitée(s)');
      for (const d of (res.details||[])) {
        log('INSIGHT', d.insight||d.question||'', 'var(--green)');
      }
      const s = await api('get_stats');
      if (s.stats) updateStats(s.stats);
    } else {
      log('ERREUR', res.error||'Échec', 'var(--red)');
    }
  } finally {
    setThinking(false);
  }
}

// ── Extract Wisdom ──
async function doWisdom() {
  setThinking(true, 'Extraction de sagesse sémantique...');
  log('SAGESSE', 'Analyse des cycles pour extraire des principes...', 'var(--accent2)');
  try {
    const res = await api('extract_wisdom');
    if (res.success) {
      log('SAGESSE', (res.result?.extracted||0) + ' principe(s) extrait(s)');
      if (res.result?.note) log('NOTE', res.result.note, 'var(--green)');
      const s = await api('get_stats');
      if (s.stats) updateStats(s.stats);
    }
  } finally {
    setThinking(false);
  }
}

// ── Fetch Trends ──
async function fetchTrends() {
  log('RSS', 'Aspiration Google News RSS (France, Tech, Science, Business, Santé)...', 'var(--accent)');
  try {
    const res = await api('fetch_trends');
    if (res.success) {
      log('RSS', res.count + ' tendances récupérées', 'var(--green)');

      // Mettre à jour l'onglet trends
      const list = document.getElementById('trends-full-list');
      if (list && res.trends) {
        list.innerHTML = res.trends.map(t =>
          `<div class="trend-item" onclick="setTopicFromTrend('${escJs(t.title)}')">
            <span>${escHtml(t.title)}</span>
            <span class="trend-cat">${t.source}</span>
          </div>`
        ).join('');
      }

      // Mettre à jour sidebar
      const sb = document.getElementById('sidebar-trends');
      if (sb && res.trends) {
        sb.innerHTML = res.trends.slice(0,15).map(t =>
          `<div class="trend-item" style="font-size:.78rem" onclick="setTopicFromTrend('${escJs(t.title)}')">
            <span>${escHtml(t.title.substring(0,52))}${t.title.length>52?'…':''}</span>
          </div>`
        ).join('');
      }
    } else {
      log('RSS', res.error||'Échec', 'var(--red)');
    }
  } catch(e) {
    log('ERREUR', e.message, 'var(--red)');
  }
}

// ── Build Manual ──
async function buildManual() {
  const topic = document.getElementById('manual-topic').value.trim();
  const type  = document.getElementById('manual-type').value;
  if (!topic) { alert('Veuillez entrer un sujet'); return; }

  setThinking(true, 'Création de contenu en cours...');
  log('CRÉER', type + ' — ' + topic, 'var(--accent)');
  showTab('dashboard');

  try {
    const res = await api('build_content', {topic, type});
    if (res.success) {
      log('CRÉÉ', res.result?.built?.title || topic, 'var(--green)');
      const s = await api('get_stats');
      if (s.stats) updateStats(s.stats);
    } else {
      log('ERREUR', res.result?.error||'Échec', 'var(--red)');
    }
  } finally {
    setThinking(false);
  }
}

// ── Set topic from trend ──
function setTopicFromTrend(title) {
  document.getElementById('manual-topic').value = title;
  showTab('content');
  log('TREND', 'Sujet sélectionné: ' + title, 'var(--accent)');
}

// ── Save Key ──
async function saveKey() {
  const key    = document.getElementById('s-apikey').value.trim();
  const pseudo = document.getElementById('s-pseudo').value.trim() || 'user';
  if (key.startsWith('••')) { alert('Entrez votre vraie clé API'); return; }
  const res = await api('save_key', {key, pseudo});
  if (res.success) {
    alert('✓ Clé API sauvegardée avec succès !');
    log('CONFIG', 'Clé API Mistral enregistrée pour: ' + pseudo, 'var(--green)');
    document.querySelector('.api-dot').className = 'api-dot dot-on';
  }
}

// ── Auto Mode ──
function toggleAutoMode() {
  autoActive = document.getElementById('auto-mode-toggle').checked;
  if (autoActive) {
    autoCountdown = 60;
    log('AUTO', 'Mode autonome activé — cycles toutes les 60 secondes', 'var(--green)');
    autoModeInterval = setInterval(autoTick, 1000);
    document.getElementById('consciousness-status').textContent = 'Mode Autonome ACTIF';
  } else {
    clearInterval(autoModeInterval);
    document.getElementById('auto-countdown').textContent = '';
    log('AUTO', 'Mode autonome désactivé', 'var(--orange)');
    document.getElementById('consciousness-status').textContent = 'Conscience en veille';
  }
}

async function autoTick() {
  autoCountdown--;
  document.getElementById('auto-countdown').textContent = `Prochain cycle dans ${autoCountdown}s`;
  if (autoCountdown <= 0) {
    autoCountdown = 60;
    log('AUTO', 'Démarrage cycle automatique...', 'var(--accent2)');
    await fullCycle();
  }
}

// ── Utils ──
const sleep = ms => new Promise(r => setTimeout(r, ms));
function escJs(s) { return s.replace(/'/g,"\\'").replace(/"/g,'\\"'); }

// ── Init ──
<?php if ($hasApiKey && empty($trends)): ?>
// Charger les tendances au démarrage si on a une clé
setTimeout(fetchTrends, 1000);
<?php endif; ?>

log('NEXUS', 'Dashboard initialisé — PHP <?= phpversion() ?> / SQLite', 'var(--accent)');
log('STATS', 'Pages: <?= $stats['pages'] ?> | Apps: <?= $stats['apps'] ?> | Sagesse: <?= $stats['wisdom_count'] ?>');
</script>

</body>
</html>
