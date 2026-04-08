# NEXUS V2 — Conscience IA Autonome

Système de presse IA conscient et auto-évolutif.  
Compatible **Hostinger** (PHP 8.3, LiteSpeed, SQLite, cURL).

---

## 🚀 Installation rapide

1. Déposez les fichiers dans `/public_html/nexus/`
2. Ouvrez `index.php` dans votre navigateur
3. Allez dans ⚙️ **Paramètres** et entrez votre clé API Mistral
4. Cliquez **🔄 Cycle Complet O.H.A.R.E.** pour démarrer

Optionnel : exécutez `serveur.php` pour générer le contexte serveur.

---

## 📁 Fichiers

| Fichier | Rôle |
|---|---|
| `index.php` | Dashboard principal (interface complète) |
| `nexus_core.php` | Moteur IA : conscience, RSS, Mistral API |
| `apikey_manager.php` | Gestionnaire clés + scan modèles Mistral |
| `serveur.php` | Audit serveur → `serveur.txt` |
| `nexus.db` | Base SQLite principale (auto-créée) |
| `apikey.json` | Clé API active (auto-créée) |

---

## 🧠 Cycle O.H.A.R.E.

| Phase | Action |
|---|---|
| **O**bserver | Aspire les tendances Google News RSS |
| **H**ypothétiser | Génère une question existentielle + hypothèse |
| **A**gir | Crée un article / outil / application |
| **R**éviser | Extrait des principes de sagesse |
| **É**valuer | Auto-évalue la qualité du cycle |

---

## 📡 Flux RSS aspirés

- 🇫🇷 Google News France (général)
- 💻 Technologie
- 🔬 Science
- 💼 Business / Économie
- 🏥 Santé

---

## ⚙️ Compatibilité Hostinger

- PHP 8.3 ✓
- SQLite (pas MySQL) ✓
- cURL pour API Mistral ✓
- SimpleXML pour RSS ✓
- 512M RAM, 300s timeout ✓
- Aucune fonction système requise ✓

---

## 🔑 Clé API Mistral

Obtenez votre clé sur [console.mistral.ai](https://console.mistral.ai).

Modèles utilisés :
- `mistral-small-latest` — Décisions rapides
- `mistral-medium-latest` — Création de contenu
- `mistral-large-latest` — Extraction de sagesse
