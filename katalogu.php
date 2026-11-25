<?php
// katalogu.php
require_once "config.php";

$q = trim($_GET["q"] ?? "");
$statusFilter = $_GET["statusi"] ?? "";
$allowedStatuses = ["Aktiv", "Ne shqyrtim", "I arkivuar"];

$sql = "
    SELECT d.id, d.titulli, d.numri_protokollit, d.statusi, d.versioni, d.pershkrimi, d.foto_path, d.created_at,
           k.emri AS kategoria, k.kodi, u.full_name AS autori
    FROM dokumentet d
    INNER JOIN kategorite k ON d.kategoria_id = k.id
    INNER JOIN users u ON d.owner_id = u.id
    WHERE 1=1
";
$params = [];

if ($q !== "") {
    $likeVal = "%" . $q . "%";
    $sql .= " AND (d.titulli LIKE :q1 OR d.numri_protokollit LIKE :q2 OR d.pershkrimi LIKE :q3)";
    $params[":q1"] = $likeVal;
    $params[":q2"] = $likeVal;
    $params[":q3"] = $likeVal;
}

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND d.statusi = :st";
    $params[":st"] = $statusFilter;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$dokumentet = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regjistri i Dokumenteve - Arkiva Digjitale</title>
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
                    <div class="brand-title">Arkivi Publik &amp; Regjistri</div>
                    <div class="brand-subtitle">Kërkim dhe shfletim i dokumenteve të protokolluara</div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php" class="active">Arkivi Publik</a></li>
                <li><a href="menu.html">Kategoritë</a></li>
                <li><a href="Contact.html">Mbështetje</a></li>
                <li><a href="login.php">Kyçu</a></li>
                <li><a href="register.php">Regjistrohu</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="panel-card">
            <h2>Regjistri i Dokumenteve</h2>
            <form method="GET" class="form-grid" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label for="q">Kërko (Titull / Protokoll / Përshkrim):</label>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="p.sh. KTR-2026-01">
                </div>
                <div class="form-group">
                    <label for="statusi">Statusi:</label>
                    <select id="statusi" name="statusi">
                        <option value="">Të gjitha</option>
                        <?php foreach ($allowedStatuses as $st): ?>
                            <option value="<?= $st ?>" <?= $statusFilter === $st ? "selected" : "" ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Filtro</button>
                </div>
            </form>

            <?php if (count($dokumentet) === 0): ?>
                <p style="color: var(--text-muted);">Nuk u gjetën dokumente sipas kriterit të zgjedhur.</p>
            <?php else: ?>
                <div class="doc-grid">
                    <?php foreach ($dokumentet as $doc): ?>
                        <?php
                            $badgeClass = match ($doc["statusi"]) {
                                "Aktiv" => "badge-active",
                                "Ne shqyrtim" => "badge-review",
                                default => "badge-archived",
                            };
                        ?>
                        <article class="doc-card">
                            <div>
                                <?php if (!empty($doc["foto_path"])): ?>
                                    <img src="<?= htmlspecialchars($doc["foto_path"]) ?>" alt="<?= htmlspecialchars($doc["titulli"]) ?>" class="doc-thumb">
                                <?php endif; ?>
                                <div class="doc-meta">
                                    <strong><?= htmlspecialchars($doc["numri_protokollit"]) ?></strong> • <?= htmlspecialchars($doc["kodi"]) ?> • <?= htmlspecialchars($doc["versioni"]) ?>
                                </div>
                                <h3><?= htmlspecialchars($doc["titulli"]) ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.92rem; margin: 8px 0;">
                                    <?= htmlspecialchars($doc["pershkrimi"] ?: "Pa përshkrim shtesë.") ?>
                                </p>
                                <div class="doc-meta">Kategoria: <?= htmlspecialchars($doc["kategoria"]) ?> | Autor: <?= htmlspecialchars($doc["autori"]) ?></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($doc["statusi"]) ?></span>
                                <a href="user_dashboard.php" class="btn-secondary" style="padding: 6px 12px; font-size: 0.84rem;">Hap në Panel</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>