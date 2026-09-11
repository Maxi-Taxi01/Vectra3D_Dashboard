<?php
/**
 * Single entry point for the Vectra3D graduation dashboard.
 *
 * Every request lands here (see .htaccess). Unauthenticated requests get the
 * login page and nothing else. Authenticated requests are served the dashboard
 * from ../private/app, which sits above the web root and has no URL of its own.
 *
 * This is server-side authentication. The protected files are never sent to the
 * browser until the session is valid — including the two source PDFs.
 */

declare(strict_types=1);

$CONFIG = require __DIR__ . '/../private/auth.php';
$APP_DIR = realpath(__DIR__ . '/../private/app');
$THROTTLE = __DIR__ . '/../private/.throttle';

if ($APP_DIR === false) {
    http_response_code(500);
    exit('Application directory not found. Check that private/app was uploaded.');
}

/* ------------------------------------------------------------------ headers */

// Per-request nonce so the login page's own inline script is allowed while
// injected script still is not. Without this the CSP below blocks it.
$NONCE = base64_encode(random_bytes(16));

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header('Cross-Origin-Opener-Policy: same-origin');
header(
    "Content-Security-Policy: default-src 'self'; " .
    "script-src 'self' 'nonce-" . $NONCE . "'; style-src 'self' 'unsafe-inline'; " .
    "img-src 'self' data:; font-src 'self'; connect-src 'self'; " .
    "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000');
}

/* ----------------------------------------------------------------- sessions */

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name('v3d_gate');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/* ---------------------------------------------------------------- throttling */

function throttle_state(string $file): array
{
    if (!is_file($file)) return [];
    $raw = @file_get_contents($file);
    if ($raw === false || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function throttle_key(): string
{
    return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'cli');
}

function throttle_check(string $file, array $cfg): int
{
    $state = throttle_state($file);
    $entry = $state[throttle_key()] ?? null;
    if (!$entry) return 0;
    if ($entry['count'] < $cfg['max_attempts']) return 0;
    $remaining = ($entry['at'] + $cfg['lockout_seconds']) - time();
    return $remaining > 0 ? $remaining : 0;
}

function throttle_record(string $file, bool $failed): void
{
    $state = throttle_state($file);
    $key = throttle_key();
    $now = time();

    // Drop entries older than an hour so the file cannot grow without bound.
    foreach ($state as $k => $v) {
        if (($v['at'] ?? 0) < $now - 3600) unset($state[$k]);
    }

    if ($failed) {
        $count = ($state[$key]['count'] ?? 0) + 1;
        $state[$key] = ['count' => $count, 'at' => $now];
    } else {
        unset($state[$key]);
    }

    @file_put_contents($file, json_encode($state), LOCK_EX);
}

/* ------------------------------------------------------------ authentication */

function verify(string $user, string $pass, array $cfg): bool
{
    $userOk = hash_equals($cfg['user'], $user);
    $calc = hash_pbkdf2('sha256', $pass, $cfg['salt'], $cfg['iter'], 32);
    $passOk = hash_equals($cfg['hash'], $calc);
    // Both comparisons always run: no early return, no timing signal on username.
    return $userOk && $passOk;
}

function authenticated(array $cfg): bool
{
    if (empty($_SESSION['ok'])) return false;
    if (($_SESSION['expires'] ?? 0) < time()) {
        $_SESSION = [];
        session_destroy();
        return false;
    }
    return true;
}

$error = null;
$lockedFor = 0;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockedFor = throttle_check($THROTTLE, $CONFIG);

    if ($lockedFor > 0) {
        $error = 'Too many attempts. Try again in ' . ceil($lockedFor / 60) . ' minutes.';
    } elseif (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $error = 'This page expired. Reload and try again.';
    } else {
        $user = (string)($_POST['user'] ?? '');
        $pass = (string)($_POST['pass'] ?? '');

        if (verify($user, $pass, $CONFIG)) {
            throttle_record($THROTTLE, false);
            session_regenerate_id(true);
            $_SESSION['ok'] = true;
            $_SESSION['expires'] = time() + $CONFIG['session_seconds'];
            header('Location: /');
            exit;
        }

        throttle_record($THROTTLE, true);
        usleep(400000); // blunt the retry rate even below the lockout threshold
        $error = 'Those details were not recognised.';
    }
}

/* -------------------------------------------------------------- file serving */

if (authenticated($CONFIG)) {
    $rel = ($path === '' || $path === '/') ? 'index.html' : ltrim($path, '/');
    $rel = rawurldecode($rel);

    $target = realpath($APP_DIR . DIRECTORY_SEPARATOR . $rel);

    // Resolved path must still sit inside the app directory. Blocks ../ traversal.
    // The separator matters: without it, a sibling directory named "app_x" would
    // satisfy a bare prefix test.
    $prefix = $APP_DIR . DIRECTORY_SEPARATOR;
    if ($target === false || strncmp($target, $prefix, strlen($prefix)) !== 0 || !is_file($target)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Not found.');
    }

    $types = [
        'html' => 'text/html; charset=utf-8',
        'css'  => 'text/css; charset=utf-8',
        'js'   => 'text/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'pdf'  => 'application/pdf',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff2' => 'font/woff2',
    ];
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));

    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($target));
    // Private: proxies and shared caches must never hold these bytes.
    header('Cache-Control: private, no-store');
    if ($ext === 'pdf') {
        header('Content-Disposition: inline; filename="' . basename($target) . '"');
    }
    readfile($target);
    exit;
}

/* --------------------------------------------------------------- login page */

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];
$lockedFor = $lockedFor ?: throttle_check($THROTTLE, $CONFIG);

http_response_code($error ? 401 : 200);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Vectra3D graduation workspace</title>
<style>
:root{
  --ink:#172f40; --muted:#536877; --nav:#163747; --accent:#096c60;
  --accent-light:#e5f4ef; --line:#d4dfe3; --bg:#f2f6f7; --white:#fff;
  --danger:#9b3333; --danger-bg:#fdf2f2; --danger-line:#e8c9c9;
  /* #9eb2bd from DESIGN.md is 2.2:1 on white and fails WCAG 1.4.11 for a
     control boundary. Darkened to 3.36:1; same hue family. */
  --field-border:#76909e; --placeholder:#60727d; --focus:#c57c0a;
  --primary-hover:#07554b;
  --ease:cubic-bezier(.22,1,.36,1);
}
*{box-sizing:border-box}
body{
  margin:0; min-height:100vh; background:var(--bg); color:var(--ink);
  font:400 15px/1.5 "Segoe UI",Arial,sans-serif;
  display:grid; grid-template-rows:auto 1fr auto;
}
.topbar{
  background:var(--nav); color:#d4e3e9; padding:10px 24px;
  font-size:12px; letter-spacing:.02em;
}
main{
  display:grid; place-items:center; padding:32px 20px;
}
.panel{
  width:100%; max-width:380px; background:var(--white);
  border:1px solid var(--line); border-radius:6px;
  padding:28px;
}
.brand{
  display:flex; align-items:center; gap:10px;
  color:var(--nav); margin-bottom:22px;
}
.brand svg{flex:none}
.brand b{display:block; font-size:15px; font-weight:650; letter-spacing:-.2px}
.brand span{display:block; font-size:12px; color:var(--muted); font-weight:400}
h1{
  margin:0 0 4px; font-size:20px; font-weight:700;
  line-height:1.3; letter-spacing:-.2px; text-wrap:balance;
}
.lede{margin:0 0 20px; font-size:14px; color:var(--muted)}
label{display:block; font-size:14px; font-weight:600; margin-bottom:6px}
.field{margin-bottom:16px}
input[type=text],input[type=password]{
  width:100%; padding:9px 10px; font:inherit; color:var(--ink);
  background:var(--white); border:1px solid var(--field-border);
  border-radius:4px; transition:border-color .15s var(--ease), box-shadow .15s var(--ease);
}
input::placeholder{color:var(--placeholder)}
input:hover{border-color:#5f7a88}
input:focus-visible{
  outline:2px solid var(--focus); outline-offset:1px;
  border-color:var(--accent);
}
button{
  width:100%; padding:9px 14px; font:inherit; font-weight:600;
  color:var(--white); background:var(--accent);
  border:1px solid transparent; border-radius:5px; cursor:pointer;
  transition:background-color .15s var(--ease), transform .15s var(--ease);
}
button:hover:not(:disabled){background:var(--primary-hover)}
button:active:not(:disabled){transform:translateY(1px)}
button:focus-visible{outline:2px solid var(--focus); outline-offset:2px}
button:disabled{background:#8aa3ab; cursor:not-allowed}
button[aria-busy=true]{color:transparent; position:relative}
button[aria-busy=true]::after{
  content:""; position:absolute; inset:0; margin:auto;
  width:15px; height:15px; border-radius:50%;
  border:2px solid rgba(255,255,255,.45); border-top-color:#fff;
  animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.alert{
  display:flex; gap:8px; margin:0 0 18px; padding:10px 12px;
  background:var(--danger-bg); border:1px solid var(--danger-line);
  border-radius:5px; font-size:14px; color:var(--danger);
}
.alert svg{flex:none; margin-top:2px}
.note{
  margin:20px 0 0; padding-top:16px; border-top:1px solid var(--line);
  font-size:12px; color:var(--muted);
}
footer{padding:16px 24px; font-size:12px; color:var(--muted); text-align:center}
@media (prefers-reduced-motion:reduce){
  *{transition-duration:.01ms !important; animation-duration:.01ms !important;
    animation-iteration-count:1 !important}
  button[aria-busy=true]::after{border-top-color:rgba(255,255,255,.45)}
}
</style>
</head>
<body>
<header class="topbar">THE HAGUE UNIVERSITY OF APPLIED SCIENCES</header>
<main>
  <form class="panel" method="post" action="/" autocomplete="on" novalidate>
    <div class="brand">
      <svg viewBox="0 0 36 36" width="30" height="30" aria-hidden="true">
        <path d="M4 7h8l6 16 6-16h8L18 33z" fill="currentColor"/>
        <path d="M14 4h8l-4 10z" fill="#83d8c5"/>
      </svg>
      <span><b>Vectra3D</b><span>Graduation workspace</span></span>
    </div>

    <h1>Sign in</h1>
    <p class="lede">This workspace is private. Entries are not shared with the university.</p>

<?php if ($error !== null): ?>
    <p class="alert" role="alert">
      <svg width="15" height="15" viewBox="0 0 16 16" aria-hidden="true" fill="currentColor">
        <path d="M8 1.5 15 14H1L8 1.5Zm0 4.2a.8.8 0 0 0-.8.9l.25 3a.55.55 0 0 0 1.1 0l.25-3a.8.8 0 0 0-.8-.9Zm0 5.3a.85.85 0 1 0 0 1.7.85.85 0 0 0 0-1.7Z"/>
      </svg>
      <span><?= htmlspecialchars($error, ENT_QUOTES) ?></span>
    </p>
<?php endif; ?>

    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <div class="field">
      <label for="user">Username</label>
      <input id="user" name="user" type="text" autocomplete="username"
             autocapitalize="none" spellcheck="false" required
             <?= $error === null ? 'autofocus' : '' ?>>
    </div>

    <div class="field">
      <label for="pass">Password</label>
      <input id="pass" name="pass" type="password" autocomplete="current-password"
             required <?= $error !== null ? 'autofocus' : '' ?>>
    </div>

    <button type="submit" <?= $lockedFor > 0 ? 'disabled' : '' ?>>
      <?= $lockedFor > 0 ? 'Locked' : 'Sign in' ?>
    </button>

    <p class="note">
      Saved entries live in this browser, on this device. Signing in on a new device
      shows an empty dashboard until you restore a backup.
    </p>
  </form>
</main>
<footer>Internal planning only · Keep raw research outside this tool</footer>
<script nonce="<?= htmlspecialchars($NONCE, ENT_QUOTES) ?>">
document.querySelector('form').addEventListener('submit', function (e) {
  var b = e.currentTarget.querySelector('button');
  if (b.disabled) { e.preventDefault(); return; }
  // Never disable synchronously inside the submit handler: some browsers treat
  // that as cancelling the submission, and the form silently does nothing.
  // A zero-delay timeout runs after the POST is already under way.
  setTimeout(function () {
    b.setAttribute('aria-busy', 'true');
    b.disabled = true;
  }, 0);
  // Re-enable if the browser restores this page from cache on Back.
  setTimeout(function () { b.disabled = false; b.removeAttribute('aria-busy'); }, 8000);
});
// Restoring from the back/forward cache leaves the button as it was on submit.
window.addEventListener('pageshow', function (ev) {
  if (!ev.persisted) return;
  var b = document.querySelector('form button');
  b.disabled = false;
  b.removeAttribute('aria-busy');
});
</script>
</body>
</html>
