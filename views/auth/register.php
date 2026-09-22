<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../layouts/base.php'; ?>
    <title>Quiz Academy — Create Account</title>
    <link rel="stylesheet" href="public/css/login.css?v=<?= filemtime(__DIR__ . '/../../public/css/login.css') ?>">
</head>
<body>
    <div class="login-form">
        <form class="form" action="index.php?action=register" method="post" novalidate>
            <?php echo csrfInputField(); ?>
            <p class="form-title">Create your account</p>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="input-container">
                <input type="text" name="username" id="reg-username"
                       placeholder="Choose a username"
                       required maxlength="100" autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <i class="fas fa-user"></i>
            </div>

            <div class="input-container">
                <input type="email" name="email" id="reg-email"
                       placeholder="Enter your email"
                       required maxlength="255" autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <i class="fas fa-envelope"></i>
            </div>

            <div class="input-container">
                <input type="text" name="groupe" id="reg-groupe"
                       placeholder="Group (e.g. Group A)"
                       maxlength="100" autocomplete="organization"
                       value="<?= htmlspecialchars($_POST['groupe'] ?? '') ?>">
                <i class="fas fa-users"></i>
            </div>

            <div class="input-container">
                <input type="password" name="password" id="reg-password"
                       placeholder="Choose a password (min 8 chars)"
                       required maxlength="255" autocomplete="new-password">
                <i class="fas fa-lock"></i>
            </div>

            <div class="input-container">
                <input type="password" name="password_confirm" id="reg-password-confirm"
                       placeholder="Confirm your password"
                       required maxlength="255" autocomplete="new-password">
                <i class="fas fa-shield-alt"></i>
            </div>

            <button type="submit" class="submit">Create Account</button>

            <div class="switch-block">
                <div class="switch-divider">
                    <span>or</span>
                </div>
                <a href="index.php">
                    <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>
                    Sign in to existing account
                </a>
            </div>
        </form>
    </div>
</body>
</html>
