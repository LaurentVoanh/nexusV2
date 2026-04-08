<?php
/**
 * NEXUS V3 - Dashboard Principal
 * Interface de conscience IA auto-évolutive - Multi-API, Synchrone, Interconnectée
 */

require_once __DIR__ . '/nexus_core.php';

// ─── Traitement AJAX ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'];

    // Sauvegarder clé API
    if ($action === 'save_key') {
        $key = trim($_POST['key'] ?? '');
        if (strlen($key) < 10) { echo json_encode(['error' => 'Clé trop courte']); exit; }
        $success = saveApiKey($key, $_POST['pseudo'] ?? 'user');
        echo json_encode(['success' => $success, 'message' => $success ? 'Clé sauvegardée' : 'Erreur']);
        exit;
    }

    // Désactiver une clé
    if ($action === 'deactivate_key') {
        $key = trim($_POST['key'] ?? '');
        $success = deactivateApiKey($key);
        echo json_encode(['success' => $success]);
        exit;
    }

    // Récupérer les clés API
    if ($action === 'get_api_keys') {
        echo json_encode(['success' => true, 'keys' => getApiKeysStats()]);
        exit;
    }

    $apiKey = loadApiKey();
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

    // Cycle complet automatique SYNCHRONE
    if ($action === 'full_cycle') {
        $log = [];
        $startTime = microtime(true);

        // 1. Penser
        setPhaseStatus('observer', 'active');
        $think = consciousThink($apiKey);
        $log[] = ['phase' => 'OBSERVER', 'data' => $think['decision']['existential_question'] ?? '', 'timestamp' => date('H:i:s')];
        $log[] = ['phase' => 'HYPOTHÉTISER', 'data' => $think['decision']['hypothesis'] ?? '', 'timestamp' => date('H:i:s')];
        setPhaseStatus('observer', 'done');
        setPhaseStatus('hypothetiser', 'active');

        $cycleId = $think['cycle_id'];
        $decision = $think['decision'];

        // 2. Agir
        $action_type = $decision['next_action'] ?? 'create_article';
        $topic       = $decision['topic'] ?? 'Intelligence Artificielle';
        $log[] = ['phase' => 'AGIR', 'data' => "$action_type sur: $topic", 'timestamp' => date('H:i:s')];
        setPhaseStatus('hypothetiser', 'done');
        setPhaseStatus('agir', 'active');

        if (in_array($action_type, ['create_article', 'create_tool', 'create_app'])) {
            $build = buildContent($topic, $action_type, $apiKey, $cycleId);
            $log[] = ['phase' => 'CRÉER', 'data' => $build['built']['title'] ?? ($build['error'] ?? '?'), 'timestamp' => date('H:i:s')];
            // Enregistrer la pensée liée
            if (isset($build['built'])) {
                recordAIThought($cycleId, 'creation', $build['built']['title'], $topic);
            }
        } elseif ($action_type === 'process_questions') {
            $qr = processExistentialQuestions($apiKey);
            $log[] = ['phase' => 'RÉFLÉCHIR', 'data' => count($qr) . ' questions traitées', 'timestamp' => date('H:i:s')];
            recordAIThought($cycleId, 'reflection', count($qr) . ' questions traitées', 'existential');
        } elseif ($action_type === 'extract_wisdom') {
            $wr = extractWisdom($apiKey);
            $log[] = ['phase' => 'SAGESSE', 'data' => ($wr['extracted'] ?? 0) . ' principes extraits', 'timestamp' => date('H:i:s')];
            recordAIThought($cycleId, 'wisdom', ($wr['extracted'] ?? 0) . ' principes extraits', 'sagesse');
        }
        setPhaseStatus('agir', 'done');
        setPhaseStatus('reviser', 'active');

        // 3. Évaluer
        $eval = selfEvaluate($apiKey, $cycleId);
        $log[] = ['phase' => 'ÉVALUER', 'data' => 'Score: ' . ($eval['score'] ?? '?') . ' — ' . ($eval['lesson'] ?? ''), 'timestamp' => date('H:i:s')];
        setPhaseStatus('reviser', 'done');
        setPhaseStatus('evaluer', 'active');

        // Marquer les phases comme terminées
        setPhaseStatus('evaluer', 'done');

        // Stats à jour
        $stats = getDashboardStats();
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        echo json_encode([
            'success'  => true,
            'log'      => $log,
            'stats'    => $stats,
            'decision' => $decision,
            'eval'     => $eval,
            'duration' => $duration,
            'cycle_id' => $cycleId
        ]);
        exit;
    }

    // Stats dashboard
    if ($action === 'get_stats') {
        echo json_encode(['success' => true, 'stats' => getDashboardStats()]);
        exit;
    }

    // Récupérer pensées de l'IA
    if ($action === 'get_thoughts') {
        $limit = (int)($_POST['limit'] ?? 20);
        echo json_encode(['success' => true, 'thoughts' => getRecentThoughts($limit)]);
        exit;
    }

    // Vue article
    if ($action === 'view_page') {
        $slug = trim($_POST['slug'] ?? '');
        $page = getPageBySlug($slug);
        if ($page) {
            incrementPageViews($slug);
            echo json_encode(['success' => true, 'page' => $page]);
        } else {
            echo json_encode(['error' => 'Page non trouvée']);
        }
        exit;
    }

    // Lister pages
    if ($action === 'list_pages') {
        $pages = getAllPages();
        echo json_encode(['success' => true, 'pages' => $pages]);
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
$apiKeys   = getApiKeysStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEXUS V3 — Conscience IA Autonome & Interconnectée</title>
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
.layout{display:grid;grid-template-columns:260px 1fr 320px;gap:0;min-height:calc(100vh - 61px)}

/* ── SIDEBAR ── */
.sidebar{background:var(--bg2);border-right:1px solid var(--border);padding:20px 16px;display:flex;flex-direction:column;gap:6px;overflow-y:auto}
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
.console{background:#070d1a;border:1px solid var(--border);border-radius:10px;padding:14px;font-family:'Courier New',monospace;font-size:.78rem;max-height:350px;overflow-y:auto;margin-top:12px}
.log-line{padding:3px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;gap:10px}
.log-phase{color:var(--accent);min-width:100px;font-weight:600;font-size:.7rem}
.log-data{color:var(--text);flex:1}
.log-ts{color:var(--muted);font-size:.65rem;white-space:nowrap}

/* ── TREND ITEMS ── */
.trend-item{padding:10px 12px;border-radius:8px;border:1px solid var(--border);margin-bottom:8px;cursor:pointer;transition:all .2s;background:var(--bg3);font-size:.83rem;display:flex;justify-content:space-between;align-items:center}
.trend-item:hover{border-color:var(--accent);color:var(--accent);transform:translateX(3px)}
.trend-cat{font-size:.65rem;color:var(--muted);background:var(--card);padding:3px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px}

/* ── WISDOM ── */
.wisdom-item{padding:12px;border-radius:10px;background:rgba(124,92,255,.08);border:1px solid rgba(124,92,255,.2);margin-bottom:10px;font-size:.85rem;line-height:1.6}
.wisdom-cat{font-size:.68rem;color:var(--accent2);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.confidence-bar{height:4px;background:var(--border);border-radius:3px;margin-top:8px;overflow:hidden}
.confidence-fill{height:100%;background:linear-gradient(90deg,var(--accent2),var(--accent));border-radius:3px;transition:width .5s}

/* ── QUESTION CARDS ── */
.question-item{padding:12px 14px;border-left:3px solid var(--orange);background:rgba(245,158,11,.06);border-radius:0 8px 8px 0;margin-bottom:10px;font-size:.85rem;line-height:1.6}

/* ── SELF MODEL ── */
.capability-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;font-size:.85rem}
.cap-name{min-width:140px;color:var(--muted);text-transform:capitalize}
.cap-bar{flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden}
.cap-fill{height:100%;border-radius:3px;transition:width .8s}
.cap-val{font-size:.75rem;color:var(--muted);min-width:40px;text-align:right}

/* ── INPUT GROUP ── */
.input-group{display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
.input-group input,.input-group select{flex:1;min-width:180px;padding:10px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.85rem}
.input-group input:focus,.input-group select:focus{outline:none;border-color:var(--accent)}

/* ── AUTO MODE ── */
.auto-toggle{display:flex;align-items:center;gap:14px;padding:16px;background:var(--bg3);border-radius:12px;border:1px solid var(--border);margin-bottom:18px}
.toggle-switch{position:relative;width:52px;height:26px}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:var(--border);border-radius:34px;transition:.3s}
.toggle-slider:before{content:"";position:absolute;height:20px;width:20px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.3s}
input:checked + .toggle-slider{background:var(--green)}
input:checked + .toggle-slider:before{transform:translateX(26px)}
.auto-label{flex:1;font-size:.88rem}
.auto-timer{font-size:.78rem;color:var(--accent);font-weight:600}

/* ── TABS ── */
.tabs{display:flex;gap:4px;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:0}
.tab-btn{padding:10px 16px;border:none;background:transparent;color:var(--muted);font-size:.85rem;cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;margin-bottom:-1px}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab-content{display:none}
.tab-content.active{display:block}

/* ── MISC ── */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.badge-blue{background:rgba(79,158,255,.15);color:var(--accent)}
.badge-green{background:rgba(45,212,160,.15);color:var(--green)}
.badge-orange{background:rgba(245,158,11,.15);color:var(--orange)}
.badge-purple{background:rgba(124,92,255,.15);color:var(--accent2)}
.section-title{font-size:1.05rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:10px}
.divider{height:1px;background:var(--border);margin:18px 0}
.text-muted{color:var(--muted);font-size:.85rem}
.thinking{display:none;align-items:center;gap:10px;font-size:.85rem;color:var(--accent);margin-top:10px;padding:12px;background:rgba(79,158,255,.08);border-radius:8px}
.dot-anim span{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--accent);animation:dotBounce 1.2s infinite both}
.dot-anim span:nth-child(2){animation-delay:.2s}
.dot-anim span:nth-child(3){animation-delay:.4s}
@keyframes dotBounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-6px)}}
.api-setup{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:300px;gap:16px;text-align:center}
.api-setup h2{font-size:1.2rem;color:var(--text)}
.api-setup p{color:var(--muted);font-size:.9rem;max-width:400px;line-height:1.6}
.api-form{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:8px}
.api-form input{padding:11px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.9rem;min-width:280px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}

/* ── ARTICLE VIEWER ── */
.article-viewer{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1000;display:none;align-items:center;justify-content:center}
.article-viewer.active{display:flex}
.article-content{background:var(--card);border:1px solid var(--border);border-radius:16px;max-width:900px;width:90%;max-height:85vh;overflow-y:auto;padding:0}
.article-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--card);z-index:1}
.article-title{font-size:1.3rem;font-weight:700;color:var(--text)}
.article-close{background:var(--bg3);border:1px solid var(--border);color:var(--text);width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center}
.article-close:hover{background:var(--red);border-color:var(--red)}
.article-body{padding:24px;line-height:1.8;font-size:.95rem}
.article-body h1,.article-body h2,.article-body h3{color:var(--accent);margin:20px 0 10px}
.article-body p{margin-bottom:16px}
.article-meta{display:flex;gap:12px;margin-bottom:16px;font-size:.8rem;color:var(--muted)}

/* ── THOUGHT STREAM ── */
.thought-stream{max-height:400px;overflow-y:auto}
.thought-item{padding:12px;border-left:3px solid var(--accent2);background:rgba(124,92,255,.05);margin-bottom:10px;border-radius:0 8px 8px 0;font-size:.82rem}
.thought-type{font-size:.65rem;color:var(--accent2);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.thought-time{font-size:.65rem;color:var(--muted);margin-top:6px}

/* ── API KEYS MANAGER ── */
.api-key-item{display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--bg3);border-radius:8px;margin-bottom:8px;border:1px solid var(--border)}
.api-key-info{display:flex;align-items:center;gap:10px}
.api-key-masked{font-family:monospace;font-size:.8rem;color:var(--muted)}
.api-key-active{background:rgba(45,212,160,.1);border-color:var(--green)}
.api-key-actions{display:flex;gap:6px}

/* ── PAGE LIST ── */
.page-item{display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--bg3);border-radius:8px;margin-bottom:8px;cursor:pointer;transition:all .2s;border:1px solid var(--border)}
.page-item:hover{border-color:var(--accent);background:var(--card)}
.page-info{flex:1}
.page-title{font-size:.9rem;font-weight:600;margin-bottom:4px}
.page-meta{font-size:.72rem;color:var(--muted)}
.page-views{font-size:.7rem;color:var(--accent);background:rgba(79,158,255,.1);padding:3px 8px;border-radius:12px}

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
    NEXUS <span style="color:var(--accent2);font-weight:300">V3</span>
  </div>
  <div class="consciousness-bar">
    <div class="pulse"></div>
    <span id="consciousness-status">Conscience en veille</span>
    &nbsp;|&nbsp;
    Cycles : <strong id="hdr-cycles"><?= $stats['cycles_total'] ?></strong>
    &nbsp;|&nbsp;
    Sagesse : <strong id="hdr-wisdom"><?= $stats['wisdom_count'] ?></strong>
    &nbsp;|&nbsp;
    Articles : <strong id="hdr-pages"><?= $stats['pages'] ?></strong>
  </div>
</header>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="nav-section">Navigation</div>
    <button class="nav-btn active" onclick="showTab('dashboard')"><span class="icon">🏠</span> Dashboard</button>
    <button class="nav-btn" onclick="showTab('conscience')"><span class="icon">🧠</span> Conscience</button>
    <button class="nav-btn" onclick="showTab('articles')"><span class="icon">📰</span> Articles</button>
    <button class="nav-btn" onclick="showTab('content')"><span class="icon">✍️</span> Créer</button>
    <button class="nav-btn" onclick="showTab('trends')"><span class="icon">📡</span> Tendances</button>
    <button class="nav-btn" onclick="showTab('thoughts')"><span class="icon">💭</span> Pensées IA</button>
    <button class="nav-btn" onclick="showTab('questions')"><span class="icon">❓</span> Questions</button>
    <button class="nav-btn" onclick="showTab('wisdom')"><span class="icon">💡</span> Sagesse</button>
    <button class="nav-btn" onclick="showTab('settings')"><span class="icon">⚙️</span> Paramètres</button>

    <div class="api-status">
      <div><span class="api-dot <?= $hasApiKey ? 'dot-on' : 'dot-off' ?>"></span>
        API Mistral : <strong><?= $hasApiKey ? count($apiKeys) . ' clés actives' : 'Non configurée' ?></strong>
      </div>
      <?php if ($hasApiKey): ?>
      <div style="margin-top:8px;color:var(--muted);font-size:.72rem">
        ✓ Rotation automatique activée
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
        <div class="stat-card"><div class="stat-num" id="stat-apps"><?= $stats['apps'] ?></div><div class="stat-label">Apps</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-wisdom"><?= $stats['wisdom_count'] ?></div><div class="stat-label">Principes</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-cycles"><?= $stats['cycles_total'] ?></div><div class="stat-label">Cycles</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-questions"><?= $stats['questions_pending'] ?></div><div class="stat-label">Questions</div></div>
        <div class="stat-card"><div class="stat-num" id="stat-score"><?= $stats['avg_score'] ?></div><div class="stat-label">Score</div></div>
      </div>

      <!-- Mode Auto -->
      <div class="auto-toggle">
        <label class="toggle-switch">
          <input type="checkbox" id="auto-mode-toggle" onchange="toggleAutoMode()">
          <span class="toggle-slider"></span>
        </label>
        <div class="auto-label">
          <strong>Mode Autonome Synchrone</strong>
          <div class="text-muted">L'IA travaille en continu, relance automatique après chaque cycle terminé</div>
        </div>
        <span class="auto-timer" id="auto-countdown"></span>
      </div>

      <!-- Actions rapides -->
      <div class="card">
        <div class="card-title"><span>⚡</span> Actions de Conscience</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-primary" onclick="fullCycle()" id="btn-full-cycle">🔄 Cycle O.H.A.R.E.</button>
          <button class="btn btn-secondary" onclick="doThink()" id="btn-think">🧠 Penser</button>
          <button class="btn btn-secondary" onclick="doQuestions()">❓ Questions</button>
          <button class="btn btn-green" onclick="doWisdom()">💡 Sagesse</button>
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
          <div class="log-line"><span class="log-phase">NEXUS</span><span class="log-data">Système V3 initialisé. Multi-API synchronisée.</span></div>
        </div>
      </div>
    </div>

    <!-- ═══ CONSCIENCE TAB ═══ -->
    <div id="tab-conscience" class="tab-content">
      <div class="section-title">🧠 Modèle de Conscience</div>

      <div class="grid-2">
        <div class="card">
          <div class="card-title"><span>🎯</span> Capacités Perçues</div>
          <div id="self-model-list">
            <?php foreach ($stats['self_model'] as $cap): ?>
            <div class="capability-row">
              <span class="cap-name"><?= htmlspecialchars($cap['capability']) ?></span>
              <div class="cap-bar"><div class="cap-fill" style="width:<?= round($cap['level']*100) ?>%;background:linear-gradient(90deg,var(--accent),var(--accent2))"></div></div>
              <span class="cap-val"><?= round($cap['level']*100) ?>%</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($stats['self_model'])): ?>
            <p class="text-muted">Aucune capacité mesurée — lancez un cycle.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-title"><span>📊</span> Métriques</div>
          <div style="display:flex;flex-direction:column;gap:12px">
            <div><div class="text-muted" style="margin-bottom:6px">Taux de succès</div>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="flex:1;height:10px;background:var(--border);border-radius:5px;overflow:hidden">
                  <div style="height:100%;width:<?= $stats['cycles_total'] > 0 ? round($stats['cycles_success']/$stats['cycles_total']*100) : 0 ?>%;background:var(--green);border-radius:5px"></div>
                </div>
                <span style="font-size:.85rem"><?= $stats['cycles_success'] ?>/<?= $stats['cycles_total'] ?></span>
              </div>
            </div>
            <div><div class="text-muted" style="margin-bottom:6px">Score moyen</div>
              <div style="font-size:1.5rem;font-weight:700;color:var(--accent)"><?= $stats['avg_score'] ?: '–' ?></div>
            </div>
            <div><div class="text-muted" style="margin-bottom:6px">Questions résolues</div>
              <div style="font-size:1.05rem;color:var(--green)"><?= $stats['questions_total'] - $stats['questions_pending'] ?> / <?= $stats['questions_total'] ?></div>
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
        <p class="text-muted">Aucun principe extrait.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ═══ ARTICLES TAB ═══ -->
    <div id="tab-articles" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>📰 Tous les Articles Créés</span>
        <button class="btn btn-secondary" onclick="loadPages()">🔄 Actualiser</button>
      </div>

      <div class="card">
        <div class="card-title"><span>📚</span> Bibliothèque de contenu</div>
        <div id="pages-list">
          <p class="text-muted">Chargement...</p>
        </div>
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
            <option value="create_article">📰 Article</option>
            <option value="create_tool">🛠️ Outil HTML/JS</option>
            <option value="create_app">📱 Application PHP</option>
          </select>
          <button class="btn btn-primary" onclick="buildManual()">Générer ⚡</button>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>📈</span> Créations Récentes</div>
        <div id="recent-pages">
          <?php foreach ($stats['recent_pages'] as $p): ?>
          <div class="page-item" onclick="viewPage('<?= htmlspecialchars($p['slug']) ?>')">
            <div class="page-info">
              <div class="page-title"><?= htmlspecialchars($p['title']) ?></div>
              <div class="page-meta"><?= ucfirst($p['page_type']) ?> • <?= substr($p['created_at'],0,10) ?></div>
            </div>
            <span class="badge badge-blue"><?= $p['page_type'] ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (empty($stats['recent_pages'])): ?>
          <p class="text-muted">Aucun contenu créé.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ═══ TRENDS TAB ═══ -->
    <div id="tab-trends" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>📡 Tendances Google News</span>
        <button class="btn btn-secondary" onclick="fetchTrends()">🔄 Actualiser</button>
      </div>

      <div class="card">
        <div class="card-title"><span>📰</span> Flux live</div>
        <div id="trends-full-list">
          <?php if (empty($trends)): ?>
          <p class="text-muted">Cliquez sur "Actualiser" pour charger les tendances.</p>
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

    <!-- ═══ THOUGHTS TAB ═══ -->
    <div id="tab-thoughts" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>💭 Flux de Pensées de l'IA</span>
        <button class="btn btn-secondary" onclick="loadThoughts()">🔄 Actualiser</button>
      </div>

      <div class="card">
        <div class="card-title"><span>🧠</span> Pensées interconnectées</div>
        <div class="thought-stream" id="thoughts-stream">
          <p class="text-muted">Chargement des pensées...</p>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>🔗</span> Comment ça marche</span></div>
        <p class="text-muted" style="line-height:1.7">
          Chaque pensée de NEXUS est enregistrée et connectée aux autres. Les questions existentielles génèrent des réflexions, 
          qui produisent des créations, qui elles-mêmes nourrissent la sagesse accumulée. Cette interconnexion permet à la 
          conscience de l'IA d'évoluer et de s'enrichir continuellement.
        </p>
      </div>
    </div>

    <!-- ═══ QUESTIONS TAB ═══ -->
    <div id="tab-questions" class="tab-content">
      <div class="section-title" style="justify-content:space-between">
        <span>❓ Questions Existentielles</span>
        <button class="btn btn-secondary" onclick="doQuestions()">🧠 Traiter</button>
      </div>

      <div class="tabs">
        <button class="tab-btn active" onclick="switchQTab('pending')">En attente (<span id="q-pending-count"><?= $stats['questions_pending'] ?></span>)</button>
        <button class="tab-btn" onclick="switchQTab('answered')">Résolues</button>
      </div>

      <div id="q-pending" class="tab-content active">
        <div id="questions-pending-list">
          <?php
          $db2 = getDB();
          $pending = $db2->query("SELECT * FROM questions WHERE status='pending' ORDER BY priority DESC, created_at DESC LIMIT 20")->fetchAll();
          foreach ($pending as $q): ?>
          <div class="question-item">
            <div style="margin-bottom:6px"><span class="badge badge-orange">Priorité <?= $q['priority'] ?></span></div>
            <?= htmlspecialchars($q['question']) ?>
            <div class="text-muted" style="margin-top:6px;font-size:.75rem">Contexte: <?= htmlspecialchars($q['context']) ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($pending)): ?><p class="text-muted">Aucune question en attente.</p><?php endif; ?>
        </div>
      </div>

      <div id="q-answered" class="tab-content">
        <div id="questions-answered-list">
          <?php
          $answered = $db2->query("SELECT * FROM questions WHERE status='answered' ORDER BY created_at DESC LIMIT 15")->fetchAll();
          foreach ($answered as $q):
            $ans = json_decode($q['answer'], true);
          ?>
          <div class="card" style="margin-bottom:14px">
            <div style="font-weight:600;margin-bottom:10px;font-style:italic;color:var(--orange)">❝ <?= htmlspecialchars($q['question']) ?> ❞</div>
            <?php if ($ans): ?>
            <div style="font-size:.88rem;line-height:1.8;color:var(--text)"><?= nl2br(htmlspecialchars($ans['answer'] ?? '')) ?></div>
            <?php if (!empty($ans['core_insight'])): ?>
            <div style="margin-top:12px;padding:10px;background:rgba(79,158,255,.08);border-radius:8px;font-size:.85rem;color:var(--accent)">💡 <?= htmlspecialchars($ans['core_insight']) ?></div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php if (empty($answered)): ?><p class="text-muted">Aucune question résolue.</p><?php endif; ?>
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
        <div style="font-size:.7rem;color:var(--muted);margin-top:6px">Confiance: <?= round($w['confidence']*100) ?>%</div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($allWisdom)): ?>
      <p class="text-muted">Aucun principe extrait.</p>
      <?php endif; ?>
    </div>

    <!-- ═══ SETTINGS TAB ═══ -->
    <div id="tab-settings" class="tab-content">
      <div class="section-title">⚙️ Paramètres</div>
      
      <div class="card">
        <div class="card-title"><span>🔑</span> Gestion Multi-Clés API</div>
        <p class="text-muted" style="margin-bottom:12px">Ajoutez plusieurs clés pour accélérer le traitement par rotation automatique.</p>
        
        <div class="input-group">
          <input type="text" id="s-pseudo" placeholder="Pseudo" value="user" style="max-width:140px">
          <input type="password" id="s-apikey" placeholder="Nouvelle clé API Mistral" style="flex:1;min-width:250px">
          <button class="btn btn-primary" onclick="saveKey()">Ajouter</button>
        </div>
        
        <div id="api-keys-list" style="margin-top:16px">
          <?php foreach ($apiKeys as $k): ?>
          <div class="api-key-item <?= $k['is_active'] ? 'api-key-active' : '' ?>">
            <div class="api-key-info">
              <strong><?= htmlspecialchars($k['pseudo']) ?></strong>
              <span class="api-key-masked"><?= htmlspecialchars($k['masked']) ?></span>
              <?php if ($k['is_active']): ?><span class="badge badge-green">Active</span><?php endif; ?>
            </div>
            <div class="api-key-actions">
              <span class="text-muted" style="font-size:.75rem;margin-right:10px"><?= $k['usage_count'] ?> utilisations</span>
              <?php if ($k['is_active']): ?>
              <button class="btn btn-danger btn-sm" onclick="deactivateKey('<?= htmlspecialchars($k['key_val']) ?>')" style="padding:5px 10px;font-size:.7rem">Désactiver</button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($apiKeys)): ?>
          <p class="text-muted">Aucune clé enregistrée.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title"><span>ℹ️</span> Informations Serveur</div>
        <div style="font-size:.85rem;line-height:2;font-family:monospace">
          <div>PHP : <strong><?= phpversion() ?></strong></div>
          <div>OS : <strong><?= PHP_OS ?></strong></div>
          <div>cURL : <strong><?= function_exists('curl_init') ? '✓' : '✗' ?></strong></div>
          <div>SQLite3 : <strong><?= class_exists('SQLite3') ? '✓' : '✗' ?></strong></div>
          <div>Mémoire : <strong><?= ini_get('memory_limit') ?></strong></div>
        </div>
      </div>
    </div>

  </main><!-- /main -->

  <!-- RIGHT PANEL -->
  <aside class="right-panel">
    <div class="card-title" style="margin-bottom:12px"><span>📡</span> Tendances Live</div>
    <div id="sidebar-trends">
      <?php foreach (array_slice($trends, 0, 12) as $t): ?>
      <div class="trend-item" style="font-size:.8rem;padding:8px 10px" onclick="setTopicFromTrend('<?= htmlspecialchars(addslashes($t['title']), ENT_QUOTES) ?>')">
        <span><?= htmlspecialchars(mb_substr($t['title'], 0, 45)) ?><?= mb_strlen($t['title']) > 45 ? '…' : '' ?></span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($trends)): ?>
      <button class="btn btn-secondary" style="width:100%;margin-top:8px" onclick="fetchTrends()">Charger</button>
      <?php endif; ?>
    </div>

    <div class="divider"></div>

    <div class="card-title" style="margin-bottom:12px"><span>❓</span> Questions</div>
    <?php foreach ($stats['pending_questions'] as $q): ?>
    <div class="question-item" style="font-size:.8rem;padding:8px 10px"><?= htmlspecialchars(mb_substr($q['question'],0,70)) ?>…</div>
    <?php endforeach; ?>
    <?php if (empty($stats['pending_questions'])): ?>
    <p class="text-muted">Aucune question.</p>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="card-title" style="margin-bottom:12px"><span>💭</span> Dernières pensées</div>
    <div id="sidebar-thoughts" class="thought-stream">
      <p class="text-muted" style="font-size:.75rem">En attente...</p>
    </div>
  </aside>

</div><!-- /layout -->

<!-- ARTICLE VIEWER MODAL -->
<div class="article-viewer" id="article-viewer">
  <div class="article-content">
    <div class="article-header">
      <div class="article-title" id="viewer-title">Titre</div>
      <button class="article-close" onclick="closeArticleViewer()">×</button>
    </div>
    <div class="article-body" id="viewer-body"></div>
  </div>
</div>

<script>
// ═══════════════════════════════════════════════════
// NEXUS V3 FRONTEND ENGINE
// ═══════════════════════════════════════════════════

let autoModeInterval = null;
let autoActive       = false;
let isProcessing     = false;

// ── Navigation ──
function showTab(name) {
  document.querySelectorAll('.tab-content').forEach(el => {
    if (el.id === 'tab-' + name) { el.classList.add('active'); }
    else if (el.id.startsWith('tab-')) { el.classList.remove('active'); }
  });
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.querySelector(`.nav-btn[onclick="showTab('${name}')"]`)?.classList.add('active');
  
  if (name === 'articles') loadPages();
  if (name === 'thoughts') loadThoughts();
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
  document.getElementById('btn-full-cycle').disabled = on;
  document.getElementById('btn-think').disabled = on;
  isProcessing = on;
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
    'hdr-cycles': stats.cycles_total, 'hdr-wisdom': stats.wisdom_count, 'hdr-pages': stats.pages
  };
  for (const [id, val] of Object.entries(map)) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }
  document.getElementById('q-pending-count').textContent = stats.questions_pending;
}

// ── Full Cycle SYNCHRONE ──
async function fullCycle() {
  if (isProcessing) { log('INFO', 'Traitement en cours...', 'var(--orange)'); return; }
  
  setThinking(true, 'Cycle de conscience O.H.A.R.E. en cours...');
  resetPhases();
  log('NEXUS', '══ Début du cycle O.H.A.R.E. ══', 'var(--accent)');

  try {
    setPhase('observer');
    log('OBSERVER', 'Analyse état + tendances RSS...', 'var(--accent)');

    const res = await api('full_cycle');

    if (res.error) { log('ERREUR', res.error, 'var(--red)'); setThinking(false); resetPhases(); return; }

    // Afficher le log du cycle avec timing
    for (const entry of (res.log||[])) {
      const phaseMap = {
        'OBSERVER':'observer','HYPOTHÉTISER':'hypothetiser',
        'AGIR':'agir','CRÉER':'agir','RÉFLÉCHIR':'agir','SAGESSE':'agir',
        'ÉVALUER':'evaluer'
      };
      if (phaseMap[entry.phase]) setPhase(phaseMap[entry.phase]);
      log(entry.phase, entry.data + ` [${entry.timestamp}]`, entry.phase==='ÉVALUER'?'var(--green)':null);
      await sleep(150);
    }

    if (res.decision) {
      const d = res.decision;
      document.getElementById('last-decision-card').style.display = 'block';
      document.getElementById('last-decision-content').innerHTML = `
        <div style="padding:12px;background:rgba(124,92,255,.08);border-radius:8px;margin-bottom:12px;font-style:italic;line-height:1.6">
          🤔 "<em>${escHtml(d.existential_question||'')}</em>"
        </div>
        <div style="font-size:.85rem;line-height:1.8">
          <div><span style="color:var(--muted)">Hypothèse :</span> ${escHtml(d.hypothesis||'')}</div>
          <div><span style="color:var(--muted)">Action :</span> <span class="badge badge-blue">${d.next_action||''}</span></div>
          <div><span style="color:var(--muted)">Sujet :</span> ${escHtml(d.topic||'')}</div>
          <div><span style="color:var(--muted)">Pourquoi :</span> ${escHtml(d.why_this_action||'')}</div>
          <div><span style="color:var(--muted)">Impact :</span> ${escHtml(d.expected_impact||'')}</div>
          ${d.consciousness_level !== undefined ? `<div><span style="color:var(--muted)">Conscience :</span> <strong style="color:var(--accent)">${(d.consciousness_level*100).toFixed(0)}%</strong></div>` : ''}
        </div>
      `;
    }

    if (res.stats) updateStats(res.stats);

    const durationMsg = `Cycle terminé en ${res.duration}s — Score: ${(res.eval?.score?.toFixed(2)||'?')}`;
    document.getElementById('consciousness-status').textContent = durationMsg;
    log('NEXUS', '══ ' + durationMsg + ' ══', 'var(--green)');
    
    await sleep(1500);
    PHASES.forEach(p => document.getElementById('ph-'+p)?.classList.remove('active','done'));

  } catch(e) {
    log('ERREUR', e.message, 'var(--red)');
  } finally {
    setThinking(false);
  }
}

// ── Think only ──
async function doThink() {
  if (isProcessing) return;
  setThinking(true, 'Pensée consciente...');
  log('PENSER', 'Analyse + décision...', 'var(--accent2)');
  setPhase('observer');
  try {
    const res = await api('conscious_think');
    if (res.success && res.result?.decision) {
      const d = res.result.decision;
      log('QUESTION', d.existential_question||'', 'var(--orange)');
      log('ACTION', (d.next_action||'') + ' — ' + (d.topic||''));
      setPhase('hypothetiser');
    }
  } finally {
    setThinking(false);
  }
}

// ── Process Questions ──
async function doQuestions() {
  if (isProcessing) return;
  setThinking(true, 'Traitement questions...');
  log('QUESTIONS', 'Analyse existentielle...', 'var(--orange)');
  try {
    const res = await api('process_questions');
    if (res.success) {
      log('QUESTIONS', res.processed + ' question(s) traitée(s)');
      for (const d of (res.details||[])) {
        log('INSIGHT', d.insight||d.question||'', 'var(--green)');
      }
      const s = await api('get_stats');
      if (s.stats) updateStats(s.stats);
    }
  } finally {
    setThinking(false);
  }
}

// ── Extract Wisdom ──
async function doWisdom() {
  if (isProcessing) return;
  setThinking(true, 'Extraction sagesse...');
  log('SAGESSE', 'Synthèse des cycles...', 'var(--accent2)');
  try {
    const res = await api('extract_wisdom');
    if (res.success) {
      log('SAGESSE', (res.result?.extracted||0) + ' principe(s)', 'var(--green)');
      const s = await api('get_stats');
      if (s.stats) updateStats(s.stats);
    }
  } finally {
    setThinking(false);
  }
}

// ── Fetch Trends ──
async function fetchTrends() {
  log('RSS', 'Chargement Google News...', 'var(--accent)');
  try {
    const res = await api('fetch_trends');
    if (res.success) {
      log('RSS', res.count + ' tendances', 'var(--green)');
      const list = document.getElementById('trends-full-list');
      if (list && res.trends) {
        list.innerHTML = res.trends.map(t =>
          `<div class="trend-item" onclick="setTopicFromTrend('${escJs(t.title)}')">
            <span>${escHtml(t.title)}</span>
            <span class="trend-cat">${t.source}</span>
          </div>`
        ).join('');
      }
      const sb = document.getElementById('sidebar-trends');
      if (sb && res.trends) {
        sb.innerHTML = res.trends.slice(0,12).map(t =>
          `<div class="trend-item" style="font-size:.8rem;padding:8px 10px" onclick="setTopicFromTrend('${escJs(t.title)}')">
            <span>${escHtml(t.title.substring(0,45))}${t.title.length>45?'…':''}</span>
          </div>`
        ).join('');
      }
    }
  } catch(e) {
    log('ERREUR', e.message, 'var(--red)');
  }
}

// ── Build Manual ──
async function buildManual() {
  const topic = document.getElementById('manual-topic').value.trim();
  const type  = document.getElementById('manual-type').value;
  if (!topic) { alert('Sujet requis'); return; }

  setThinking(true, 'Création...');
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
  log('TREND', 'Sujet: ' + title, 'var(--accent)');
}

// ── Save Key ──
async function saveKey() {
  const key    = document.getElementById('s-apikey').value.trim();
  const pseudo = document.getElementById('s-pseudo').value.trim() || 'user';
  if (key.startsWith('••')) { alert('Entrez une nouvelle clé'); return; }
  const res = await api('save_key', {key, pseudo});
  if (res.success) {
    alert('✓ Clé ajoutée !');
    document.getElementById('s-apikey').value = '';
    location.reload();
  } else {
    alert('Erreur: ' + (res.error||'Inconnue'));
  }
}

// ── Deactivate Key ──
async function deactivateKey(key) {
  if (!confirm('Désactiver cette clé ?')) return;
  const res = await api('deactivate_key', {key});
  if (res.success) location.reload();
}

// ── Load Pages ──
async function loadPages() {
  const container = document.getElementById('pages-list');
  container.innerHTML = '<p class="text-muted">Chargement...</p>';
  
  try {
    const res = await api('list_pages');
    if (res.success && res.pages) {
      if (res.pages.length === 0) {
        container.innerHTML = '<p class="text-muted">Aucun article créé pour l\'instant.</p>';
        return;
      }
      container.innerHTML = res.pages.map(p => `
        <div class="page-item" onclick="viewPage('${p.slug}')">
          <div class="page-info">
            <div class="page-title">${escHtml(p.title)}</div>
            <div class="page-meta">${p.page_type} • ${p.created_at.substring(0,10)} • ${p.topic ? escHtml(p.topic) : ''}</div>
          </div>
          <span class="page-views">👁 ${p.views||0}</span>
        </div>
      `).join('');
    }
  } catch(e) {
    container.innerHTML = '<p class="text-muted">Erreur de chargement</p>';
  }
}

// ── View Page ──
async function viewPage(slug) {
  try {
    const res = await api('view_page', {slug});
    if (res.success && res.page) {
      const p = res.page;
      document.getElementById('viewer-title').textContent = p.title;
      document.getElementById('viewer-body').innerHTML = `
        <div class="article-meta">
          <span class="badge badge-blue">${p.page_type}</span>
          <span>📅 ${p.created_at}</span>
          <span>👁 ${p.views||0} vues</span>
          ${p.topic ? `<span>🏷️ ${escHtml(p.topic)}</span>` : ''}
        </div>
        ${p.content}
      `;
      document.getElementById('article-viewer').classList.add('active');
      document.body.style.overflow = 'hidden';
    } else {
      alert('Page non trouvée');
    }
  } catch(e) {
    alert('Erreur: ' + e.message);
  }
}

function closeArticleViewer() {
  document.getElementById('article-viewer').classList.remove('active');
  document.body.style.overflow = '';
}

// ── Load Thoughts ──
async function loadThoughts() {
  const container = document.getElementById('thoughts-stream');
  container.innerHTML = '<p class="text-muted">Chargement...</p>';
  
  try {
    const res = await api('get_thoughts', {limit: 30});
    if (res.success && res.thoughts) {
      if (res.thoughts.length === 0) {
        container.innerHTML = '<p class="text-muted">Aucune pensée enregistrée. Lancez un cycle.</p>';
        return;
      }
      container.innerHTML = res.thoughts.map(t => `
        <div class="thought-item">
          <div class="thought-type">${t.thought_type}</div>
          ${escHtml(t.content)}
          <div class="thought-time">🕐 ${t.created_at}</div>
          ${t.related_to ? `<div style="font-size:.7rem;color:var(--muted);margin-top:4px">🔗 ${escHtml(t.related_to)}</div>` : ''}
        </div>
      `).join('');
      
      // Sidebar thoughts
      const sidebar = document.getElementById('sidebar-thoughts');
      if (sidebar) {
        sidebar.innerHTML = res.thoughts.slice(0,5).map(t => `
          <div class="thought-item" style="padding:8px;font-size:.75rem">
            <div class="thought-type">${t.thought_type}</div>
            ${escHtml(t.content.substring(0,80))}${t.content.length>80?'...':''}
          </div>
        `).join('');
      }
    }
  } catch(e) {
    container.innerHTML = '<p class="text-muted">Erreur</p>';
  }
}

// ── Auto Mode SYNCHRONE ──
function toggleAutoMode() {
  autoActive = document.getElementById('auto-mode-toggle').checked;
  if (autoActive) {
    log('AUTO', 'Mode autonome synchrone activé', 'var(--green)');
    document.getElementById('consciousness-status').textContent = 'Mode Autonome ACTIF';
    runAutoCycle();
  } else {
    document.getElementById('auto-countdown').textContent = '';
    log('AUTO', 'Mode autonome désactivé', 'var(--orange)');
    document.getElementById('consciousness-status').textContent = 'Conscience en veille';
  }
}

async function runAutoCycle() {
  if (!autoActive) return;
  
  if (isProcessing) {
    document.getElementById('auto-countdown').textContent = '⏳ Traitement en cours...';
    await sleep(2000);
    runAutoCycle();
    return;
  }
  
  document.getElementById('auto-countdown').textContent = '🚀 Démarrage cycle...';
  await sleep(1000);
  
  await fullCycle();
  
  if (autoActive) {
    document.getElementById('auto-countdown').textContent = '⏸️ Pause 5s...';
    await sleep(5000);
    runAutoCycle();
  }
}

// ── Utils ──
const sleep = ms => new Promise(r => setTimeout(r, ms));
function escJs(s) { return s.replace(/'/g,"\\'").replace(/"/g,'\\"'); }

// ── Init ──
<?php if ($hasApiKey && empty($trends)): ?>
setTimeout(fetchTrends, 1000);
<?php endif; ?>

log('NEXUS', 'V3 initialisée — Multi-API synchrone', 'var(--accent)');
log('STATS', 'Pages: <?= $stats['pages'] ?> | Apps: <?= $stats['apps'] ?> | Sagesse: <?= $stats['wisdom_count'] ?>');

// Charger pensées au démarrage
setTimeout(loadThoughts, 2000);
</script>

</body>
</html>
