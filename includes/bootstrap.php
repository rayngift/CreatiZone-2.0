<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/database.php';
foreach (['User', 'Challenge', 'Submission', 'Comment', 'Badge'] as $c) {
    require_once __DIR__ . "/../classes/$c.php";
}

const CATEGORIES = ['Sport', 'Culture', 'Code', 'Santé', 'Technologie', 'Design', 'Autre'];
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');

$db = Database::get();

/* ── Sortie / sécurité ── */
function e(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function redirect(string $url): never { header("Location: $url"); exit; }

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(bool $json = false): void
{
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), (string)$sent)) {
        if ($json) json_out(['success' => false, 'message' => 'Session expirée, rechargez la page.'], 419);
        http_response_code(419);
        die('Session expirée. Retournez en arrière et rechargez la page.');
    }
}

/* ── Authentification ── */
function current_user_id(): ?int { return isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : null; }

function require_login(bool $json = false): int
{
    $id = current_user_id();
    if ($id) return $id;
    if ($json) json_out(['success' => false, 'message' => 'Vous devez être connecté.'], 401);
    flash('error', 'Connectez-vous pour continuer.');
    redirect('login.php');
}

function login_user(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['id_user'] = (int)$u['id_user'];
    $_SESSION['nom']     = $u['nom'];
    $_SESSION['prenom']  = $u['prenom'];
}

/* ── Messages flash ── */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function get_flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ── JSON ── */
function json_out(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function json_input(): array
{
    $body = json_decode(file_get_contents('php://input') ?: '', true);
    return is_array($body) ? $body : $_POST;
}

/* ── Affichage ── */
function stars(?float $avg): string
{
    $n = (int)round($avg ?? 0);
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
}
function initials(string $prenom, string $nom): string
{
    return mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
}
function date_fr(string $d): string { return date('d/m/Y', strtotime($d)); }
function is_expired(string $deadline): bool { return strtotime($deadline . ' 23:59:59') < time(); }

/* ── Images ── */
function save_image(?array $file, int $maxMb = 3): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK)        throw new RuntimeException("Erreur pendant l'envoi de l'image.");
    if ($file['size'] > $maxMb * 1024 * 1024)    throw new RuntimeException("Image trop lourde (max $maxMb Mo).");

    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime]) || getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Format non autorisé (JPG, PNG, GIF ou WEBP).');
    }
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new RuntimeException("Impossible d'enregistrer l'image.");
    }
    return $name;
}
function delete_image(?string $name): void
{
    if ($name && is_file(UPLOAD_DIR . basename($name))) @unlink(UPLOAD_DIR . basename($name));
}
