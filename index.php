<?php
// ============================================
// QuizAcademy — Front Controller (Router)
// ============================================
// All requests pass through this file.
// Route: index.php?action=<name>
// ============================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/helpers.php';

$action = $_GET['action'] ?? 'login';

switch ($action) {

    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->login();
        break;

    case 'register':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->register();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController($pdo))->logout();
        break;

    case 'quiz':
        require_once __DIR__ . '/controllers/QuizController.php';
        (new QuizController($pdo))->index();
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}