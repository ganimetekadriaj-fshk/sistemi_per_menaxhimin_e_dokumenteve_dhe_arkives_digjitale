<?php
// contact.php
require_once "config.php";

$status_msg = "";
$is_success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emri = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $departamenti = trim($_POST["Informations"] ?? "");
    $mesazhi = trim($_POST["message"] ?? "");

    if ($emri === "" || $email === "" || $mesazhi === "") {
        $status_msg = "Ju lutem plotësoni emrin, emailin dhe mesazhin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status_msg = "Ju lutem shkruani një adresë emaili të vlefshme.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO kerkesat_kontakt (emri, email, departamenti, mesazhi) VALUES (:emri, :email, :dept, :mesazhi)"
        );
        if ($stmt->execute([
            ":emri" => $emri,
            ":email" => $email,
            ":dept" => $departamenti !== "" ? $departamenti : null,
            ":mesazhi" => $mesazhi
        ])) {
            $status_msg = "Kërkesa juaj u regjistrua me sukses në sistem.";
            $is_success = true;
        } else {
            $status_msg = "Ndodhi një gabim gjatë ruajtjes së kërkesës.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmimi i Kërkesës - Arkiva Digjitale</title>
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
                    <div class="brand-subtitle">Konfirmim i pranimit të kërkesës</div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php">Arkivi Publik</a></li>
                <li><a href="Contact.html">Mbështetje</a></li>
                <li><a href="login.php">Kyçu</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="panel-card" style="max-width: 680px; margin: 0 auto; text-align: center;">
            <h2>Statusi i Kërkesës</h2>
            <p class="<?= $is_success ? 'alert-ok' : 'alert-err' ?>" style="margin-top: 16px;">
                <?= htmlspecialchars($status_msg !== "" ? $status_msg : "Asnjë kërkesë nuk është dërguar.") ?>
            </p>
            <div class="hero-actions" style="justify-content: center; margin-top: 20px;">
                <a href="Contact.html" class="btn-primary">Kthehu te formulari</a>
                <a href="index.html" class="btn-secondary">Ballina</a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>