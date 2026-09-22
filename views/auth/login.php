<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../layouts/base.php'; ?>
    <title>Quiz Academy — Login</title>
    <link rel="stylesheet" href="public/css/login.css?v=<?= filemtime(__DIR__ . '/../../public/css/login.css') ?>">
</head>
<body>
    <div class="login-form">
        <form class="form" action="index.php" method="post">
            <?php echo csrfInputField(); ?>
            <p class="form-title">Sign in to your account</p>

            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message">Account created! You can now sign in.</div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="input-container">
                <input type="text" name="username" placeholder="Enter username"
                       required maxlength="100" autocomplete="username">
                <i class="fas fa-user"></i>
            </div>

            <div class="input-container">
                <input type="password" name="password" placeholder="Enter password"
                       required maxlength="255" autocomplete="current-password">
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="submit">Sign in</button>

            <div class="switch-block">
                <div class="switch-divider">
                    <span>or</span>
                </div>
                <a href="index.php?action=register">
                    <i class="fas fa-user-plus" style="margin-right:8px;"></i>
                    Create an account
                </a>
            </div>
        </form>
    </div>
</body>
</html>
