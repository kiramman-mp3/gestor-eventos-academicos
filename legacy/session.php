<?php
// session.php — sesión endurecida + helpers de autenticación/autorización

// Inicia la sesión con cookies seguras si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Roles válidos del sistema (valor almacenado en estudiantes.rol)
const ROL_ADMIN = 'administrador';
const ROL_ESTUDIANTE = 'estudiante';

// Tiempo máximo de inactividad antes de cerrar la sesión (segundos)
const SESSION_IDLE_TIMEOUT = 1800;

// =====================
// Funciones auxiliares
// =====================

function getUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

function getUserEmail() {
    return $_SESSION['email'] ?? null;
}

function getUserName() {
    return $_SESSION['nombre'] ?? null;
}

function getUserLastname() {
    return $_SESSION['apellido'] ?? null;
}

function getUserRole() {
    return $_SESSION['rol'] ?? null;
}

function getUserCarrera() {
    return $_SESSION['carrera'] ?? null;
}

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return getUserRole() === ROL_ADMIN;
}

// =====================
// Ciclo de vida de la sesión
// =====================

// Inicia sesión para un usuario autenticado. Regenera el ID para evitar fijación de sesión.
function loginUser(array $user) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $user['id'];
    $_SESSION['email'] = $user['correo'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['apellido'] = $user['apellido'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['carrera'] = $user['carrera'] ?? null;
    $_SESSION['ultima_actividad'] = time();
    unset($_SESSION['csrf_token']);
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// Cierra la sesión si superó el tiempo de inactividad
if (isLoggedIn()) {
    $ultima = $_SESSION['ultima_actividad'] ?? time();
    if (time() - $ultima > SESSION_IDLE_TIMEOUT) {
        logoutUser();
        session_start();
    } else {
        $_SESSION['ultima_actividad'] = time();
    }
}

// =====================
// Control de acceso
// =====================

// URL relativa a la raíz de la app, válida desde cualquier subcarpeta (admin/, service/, ...)
function appUrl($path) {
    $dir = realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')) ?: '';
    $root = realpath(__DIR__);
    $rel = str_starts_with($dir, $root) ? trim(str_replace('\\', '/', substr($dir, strlen($root))), '/') : '';
    $depth = $rel === '' ? 0 : substr_count($rel, '/') + 1;
    return str_repeat('../', $depth) . ltrim($path, '/');
}

// Las peticiones a service/ o con Accept JSON reciben JSON en vez de redirección
function wantsJson() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_contains($accept, 'application/json')
        || str_contains($script, '/service/')
        || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

function denyAccess($status) {
    if (wantsJson()) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $status === 401 ? 'No autenticado' : 'Acceso no autorizado']);
        exit;
    }
    if ($status === 401) {
        header('Location: ' . appUrl('login.php'));
        exit;
    }
    http_response_code(403);
    exit('Acceso no autorizado.');
}

function requireLogin() {
    if (!isLoggedIn()) {
        denyAccess(401);
    }
}

// Exige que el usuario tenga alguno de los roles indicados. 'admin' es alias de 'administrador'.
function requireRole(string ...$roles) {
    requireLogin();
    $roles = array_map(fn($r) => $r === 'admin' ? ROL_ADMIN : $r, $roles);
    if (!in_array(getUserRole(), $roles, true)) {
        denyAccess(403);
    }
}

function requireAdmin() {
    requireRole(ROL_ADMIN);
}

function requirePost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido.');
    }
}

// =====================
// CSRF
// =====================

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
    }
}

// =====================
// Salida segura
// =====================

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
