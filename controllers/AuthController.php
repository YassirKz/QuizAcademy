<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

class AuthController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // --------------------------------------------------------
    // Action : afficher et traiter le formulaire de connexion
    // --------------------------------------------------------
    public function login(): void
    {
        // Redirect if already logged in
        if (isset($_SESSION['user'])) {
            header("Location: index.php?action=quiz");
            exit();
        }

        $error = null;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            if (!validateCsrfToken()) {
                $error = "Invalid request. Please try again.";
            } else {
                $username = trim($_POST["username"] ?? '');
                $password = $_POST["password"] ?? '';

                if (empty($username) || empty($password)) {
                    $error = "Please fill in all fields.";
                } elseif (strlen($username) > 100 || strlen($password) > 255) {
                    $error = "Input exceeds maximum length.";
                } else {
                    $attemptKey = 'login_attempts';
                    $lockoutKey = 'login_lockout';

                    if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
                        $remaining = $_SESSION[$lockoutKey] - time();
                        $error = "Too many failed attempts. Try again in {$remaining} seconds.";
                    } else {
                        // Clear expired lockout
                        if (isset($_SESSION[$lockoutKey]) && time() >= $_SESSION[$lockoutKey]) {
                            unset($_SESSION[$attemptKey], $_SESSION[$lockoutKey]);
                        }

                        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userName = ?");
                        $stmt->execute([$username]);
                        $result = $stmt->fetch();

                        $loginSuccess = false;

                        if ($result) {
                            if (str_starts_with($result->userPassword, '$2y$')) {
                                $loginSuccess = password_verify($password, $result->userPassword);
                            } elseif ($result->userPassword === $password) {
                                // Plain text — auto-upgrade to bcrypt
                                $loginSuccess = true;
                                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                                $updateStmt = $this->pdo->prepare("UPDATE users SET userPassword = ? WHERE userId = ?");
                                $updateStmt->execute([$hashedPassword, $result->userId]);
                            }
                        }

                        if ($loginSuccess) {
                            unset($_SESSION[$attemptKey], $_SESSION[$lockoutKey]);
                            session_regenerate_id(true);
                            $_SESSION['user'] = $result->userName;
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            header("Location: index.php?action=quiz");
                            exit();
                        } else {
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

        require __DIR__ . '/../views/auth/login.php';
    }

    // --------------------------------------------------------
    // Action : déconnexion
    // --------------------------------------------------------
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: index.php");
        exit();
    }

    // --------------------------------------------------------
    // Action : afficher et traiter le formulaire d'inscription
    // --------------------------------------------------------
    public function register(): void
    {
        // Redirect if already logged in
        if (isset($_SESSION['user'])) {
            header("Location: index.php?action=quiz");
            exit();
        }

        $error   = null;
        $success = null;

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            if (!validateCsrfToken()) {
                $error = "Invalid request. Please try again.";
            } else {
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email']    ?? '');
                $groupe   = trim($_POST['groupe']   ?? '');
                $password = $_POST['password']         ?? '';
                $confirm  = $_POST['password_confirm'] ?? '';

                // ── Validation ──────────────────────────────
                if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
                    $error = "Please fill in all required fields.";
                } elseif (strlen($username) > 100) {
                    $error = "Username must be 100 characters or fewer.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = "Please enter a valid email address.";
                } elseif (strlen($email) > 255) {
                    $error = "Email must be 255 characters or fewer.";
                } elseif (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters.";
                } elseif ($password !== $confirm) {
                    $error = "Passwords do not match.";
                } else {
                    // ── Check uniqueness ────────────────────
                    $stmt = $this->pdo->prepare(
                        "SELECT userId FROM users WHERE userName = ? OR userEmail = ? LIMIT 1"
                    );
                    $stmt->execute([$username, $email]);

                    if ($stmt->fetch()) {
                        $error = "This username or email is already taken.";
                    } else {
                        // ── Insert new user ─────────────────
                        $hashed = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $this->pdo->prepare(
                            "INSERT INTO users (userName, userPassword, userEmail, userGroupe)
                             VALUES (?, ?, ?, ?)"
                        );
                        $stmt->execute([$username, $hashed, $email, $groupe]);

                        // Redirect to login with success flash
                        header("Location: index.php?registered=1");
                        exit();
                    }
                }
            }
        }

        require __DIR__ . '/../views/auth/register.php';
    }
}
