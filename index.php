<?php 

require 'connexion.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: quiz.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!validateCsrfToken()) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        // Input validation
        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } elseif (strlen($username) > 100 || strlen($password) > 255) {
            $error = "Input exceeds maximum length.";
        } else {
            // Rate limiting: track failed attempts in session
            $attemptKey = 'login_attempts';
            $lockoutKey = 'login_lockout';

            if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
                $remaining = $_SESSION[$lockoutKey] - time();
                $error = "Too many failed attempts. Try again in {$remaining} seconds.";
            } else {
                // Clear lockout if expired
                if (isset($_SESSION[$lockoutKey]) && time() >= $_SESSION[$lockoutKey]) {
                    unset($_SESSION[$attemptKey], $_SESSION[$lockoutKey]);
                }

                // Fetch user by username only (never send password in query)
                $stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ?");
                $stmt->execute([$username]);
                $result = $stmt->fetch();

                $loginSuccess = false;

                if ($result) {
                    // Check if password is already hashed (bcrypt starts with $2y$)
                    if (str_starts_with($result->userPassword, '$2y$')) {
                        $loginSuccess = password_verify($password, $result->userPassword);
                    } elseif ($result->userPassword === $password) {
                        // Plain text match — auto-upgrade to bcrypt hash
                        $loginSuccess = true;
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                        $updateStmt = $pdo->prepare("UPDATE users SET userPassword = ? WHERE userId = ?");
                        $updateStmt->execute([$hashedPassword, $result->userId]);
                    }
                }

                if ($loginSuccess) {
                    // Clear login attempts
                    unset($_SESSION[$attemptKey], $_SESSION[$lockoutKey]);

                    // Prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION['user'] = $result->userName;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header("Location: quiz.php");
                    exit();
                } else {
                    // Track failed attempts
                    $_SESSION[$attemptKey] = ($_SESSION[$attemptKey] ?? 0) + 1;
                    if ($_SESSION[$attemptKey] >= 5) {
                        $_SESSION[$lockoutKey] = time() + 300; // 5-minute lockout
                    }
                    $error = "Username or password incorrect.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/Q-A.png" type="image/x-png">
    <title>Quiz Academy — Login</title>
    <link rel="stylesheet" href="./style/login.css">
</head>
<body>
    <div class="login-form">

        <form class="form" action="index.php" method="post">
            <?php echo csrfInputField(); ?>
            <p class="form-title">Sign in to your account</p>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="input-container">
                <input type="text" name="username" placeholder="Enter username" required maxlength="100" autocomplete="username">
                <i class="fas fa-user"></i>
            </div>

            <div class="input-container">
                <input type="password" name="password" placeholder="Enter password" required maxlength="255" autocomplete="current-password">
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="submit">
                    Sign in
            </button>
        </form>
    </div>
</body>
</html>