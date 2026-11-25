<?php
// register.php
require_once "auth_guard.php";
require_once "config.php";

if (isset($_SESSION["role"])) {
    header("Location: " . ($_SESSION["role"] === "admin" ? "admin_dashboard.php" : "user_dashboard.php"));
    exit;
}

$csrf = ensure_csrf_token();
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $error = "Kërkesa nuk u validua (CSRF). Rifreskoni faqen.";
    } else {
        $full_name = trim($_POST["full_name"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $department = trim($_POST["department"] ?? "Administratë");
        $password = $_POST["password"] ?? "";

        if ($full_name === "" || $username === "" || $email === "" || $password === "") {
            $error = "Plotësoni të gjitha fushat e detyrueshme.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Shkruani një adresë emaili të vlefshme.";
        } elseif (strlen($password) < 6) {
            $error = "Fjalëkalimi duhet të ketë të paktën 6 karaktere.";
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
            $check->execute([
                ":username" => $username,
                ":email" => $email,
            ]);

            if ($check->fetch()) {
                $error = "Ky username ose email ekziston tashmë.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "INSERT INTO users (full_name, username, email, department, password_hash, role) VALUES (:fn, :un, :em, :dp, :pw, 'user')"
                );
                $stmt->execute([
                    ":fn" => $full_name,
                    ":un" => $username,
                    ":em" => $email,
                    ":dp" => $department !== "" ? $department : "Administratë",
                    ":pw" => $hash
                ]);
                $success = "Regjistrimi u krye me sukses. Tani mund të kyçeni.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regjistrimi - Arkiva Digjitale</title>
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
                    <div class="brand-subtitle">Krijim llogarie të re përdoruesi</div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php">Arkivi Publik</a></li>
                <li><a href="login.php">Kyçu</a></li>
                <li><a href="register.php" class="active">Regjistrohu</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="panel-card" style="max-width: 500px; margin: 0 auto;">
            <h2>Regjistrohu</h2>

            <?php if ($error !== ""): ?>
                <div class="alert-err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert-ok"><?= htmlspecialchars($success) ?> <a href="login.php">Kyçu këtu</a></div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="form-grid" style="grid-template-columns: 1fr;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="form-group">
                    <label for="full_name">Emri dhe Mbiemri:*</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>

                <div class="form-group">
                    <label for="username">Username:*</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:*</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="department">Departamenti:</label>
                    <input type="text" id="department" name="department" placeholder="p.sh. Arkiv, Juridik, Financa">
                </div>

                <div class="form-group">
                    <label for="password">Fjalëkalimi (min. 6 karaktere):*</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Krijo Llogarinë</button>
            </form>

            <p style="margin-top: 16px; text-align: center; font-size: 0.92rem;">
                Keni llogari? <a href="login.php">Kyçuni këtu</a>
            </p>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>