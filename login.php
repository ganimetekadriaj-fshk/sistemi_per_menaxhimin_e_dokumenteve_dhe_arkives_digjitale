<?php
// login.php
require_once "auth_guard.php";
require_once "config.php";

if (isset($_SESSION["role"])) {
    header("Location: " . ($_SESSION["role"] === "admin" ? "admin_dashboard.php" : "user_dashboard.php"));
    exit;
}

$csrf = ensure_csrf_token();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $error = "Kërkesa nuk u validua (CSRF). Rifreskoni faqen.";
    } else {
        $login_input = trim($_POST["login_input"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($login_input === "" || $password === "") {
            $error = "Plotësoni username/email dhe fjalëkalimin.";
        } else {
            $stmt = $conn->prepare(
                "SELECT id, full_name, username, email, department, password_hash, role FROM users WHERE email = :email OR username = :username LIMIT 1"
            );
            $stmt->execute([
                ":email" => $login_input,
                ":username" => $login_input,
            ]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user["password_hash"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = (int)$user["id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["department"] = $user["department"];
                $_SESSION["role"] = $user["role"];

                header("Location: " . ($user["role"] === "admin" ? "admin_dashboard.php" : "user_dashboard.php"));
                exit;
            }

            $error = "Kredencialet e kyçjes janë të pasakta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kyçja - Arkiva Digjitale</title>
    <link rel="shortcut icon" href="logo min.jpg" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-top">
            <div class="brand-box">
                <svg class="brand-logo-svg" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="dmsPrismGrad" x1="0" y1="0" x2="50" y2="50" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#7c3aed"/>
                            <stop offset="55%" stop-color="#ec4899"/>
                            <stop offset="100%" stop-color="#06b6d4"/>
                        </linearGradient>
                    </defs>
                    <rect width="50" height="50" rx="14" fill="url(#dmsPrismGrad)"/>
                    <path d="M13 17.5C13 15.8431 14.3431 14.5 16 14.5H22.6L25.2 17.5H34C35.6569 17.5 37 18.8431 37 20.5V32.5C37 34.1569 35.6569 35.5 34 35.5H16C14.3431 35.5 13 34.1569 13 32.5V17.5Z" fill="#ffffff"/>
                    <path d="M18.5 24H31.5M18.5 29H26.5" stroke="#7c3aed" stroke-width="2.4" stroke-linecap="round"/>
                </svg>
                <div>
                    <div class="brand-title">Arkiva Digjitale &amp; DMS</div>
                    <div class="brand-subtitle">Kyçja në sistemin e menaxhimit të dokumenteve</div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php">Arkivi Publik</a></li>
                <li><a href="login.php" class="active">Kyçu</a></li>
                <li><a href="register.php">Regjistrohu</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="panel-card" style="max-width: 460px; margin: 0 auto;">
            <h2>Kyçu në Sistem</h2>

            <?php if ($error !== ""): ?>
                <div class="alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="form-grid" style="grid-template-columns: 1fr;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="form-group">
                    <label for="login_input">Username ose Email:</label>
                    <input type="text" id="login_input" name="login_input" required>
                </div>

                <div class="form-group">
                    <label for="password">Fjalëkalimi:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Kyçu</button>
            </form>

            <p style="margin-top: 16px; text-align: center; font-size: 0.92rem;">
                Nuk keni llogari? <a href="register.php">Regjistrohuni</a>
            </p>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>