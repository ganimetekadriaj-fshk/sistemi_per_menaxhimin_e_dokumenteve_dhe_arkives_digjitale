<?php
// admin_dashboard.php
require_once "auth_guard.php";
require_role("admin");
require_once "config.php";

$csrf = ensure_csrf_token();
$msg = "";
$err = "";

// Kontrollon nëse tabela users ka kolonën last_login (opsionale)
$hasLastLogin = false;
try {
    $colStmt = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    $hasLastLogin = (bool)$colStmt->fetch();
} catch (PDOException $e) {
    $hasLastLogin = false;
}

// Regjistron kohën e fundit të hyrjes së adminit në sesion
if ($hasLastLogin && empty($_SESSION["last_login_recorded"])) {
    $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id")->execute([":id" => (int)$_SESSION["user_id"]]);
    $_SESSION["last_login_recorded"] = true;
}

$katStmt = $conn->query("SELECT id, emri, kodi FROM kategorite ORDER BY emri ASC");
$kategorite = $katStmt->fetchAll();

// Fshirja e dokumentit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_doc"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $id = (int)($_POST["doc_id"] ?? 0);
        if ($id > 0) {
            $conn->prepare("DELETE FROM dokumentet WHERE id = :id")->execute([":id" => $id]);
            $msg = "Dokumenti u fshi me sukses.";
        }
    }
}

// Arkivimi i shpejtë i dokumentit nga admini
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["archive_doc_admin"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $id = (int)($_POST["doc_id"] ?? 0);
        if ($id > 0) {
            $conn->prepare("UPDATE dokumentet SET statusi = 'I arkivuar' WHERE id = :id")->execute([":id" => $id]);
            $msg = "Dokumenti u kalua në statusin 'I arkivuar'.";
        }
    }
}

// Përditësimi i dokumentit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_doc"])) {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $err = "Token i pavlefshëm CSRF.";
    } else {
        $id = (int)($_POST["id"] ?? 0);
        $titulli = trim($_POST["titulli"] ?? "");
        $nr_protokollit = trim($_POST["numri_protokollit"] ?? "");
        $kategoria_id = (int)($_POST["kategoria_id"] ?? 0);
        $versioni = trim($_POST["versioni"] ?? "v1.0");
        $statusi = $_POST["statusi"] ?? "Aktiv";

        if ($id > 0 && $titulli !== "" && $nr_protokollit !== "" && $kategoria_id > 0) {
            $stmt = $conn->prepare(
                "UPDATE dokumentet SET titulli = :tit, numri_protokollit = :nr, kategoria_id = :kat, versioni = :ver, statusi = :st WHERE id = :id"
            );
            $stmt->execute([
                ":tit" => $titulli,
                ":nr" => $nr_protokollit,
                ":kat" => $kategoria_id,
                ":ver" => $versioni,
                ":st" => $statusi,
                ":id" => $id
            ]);
            $msg = "Dokumenti u përditësua me sukses.";
        } else {
            $err = "Plotësoni fushat e detyrueshme të dokumentit.";
        }
    }
}

// Shtimi i dokumentit të ri nga admini
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_doc"])) {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $err = "Token i pavlefshëm CSRF.";
    } else {
        $titulli = trim($_POST["titulli"] ?? "");
        $nr_protokollit = trim($_POST["numri_protokollit"] ?? "");
        $kategoria_id = (int)($_POST["kategoria_id"] ?? 0);
        $versioni = trim($_POST["versioni"] ?? "v1.0");
        $statusi = $_POST["statusi"] ?? "Aktiv";

        if ($titulli !== "" && $nr_protokollit !== "" && $kategoria_id > 0) {
            $chk = $conn->prepare("SELECT id FROM dokumentet WHERE numri_protokollit = :nr LIMIT 1");
            $chk->execute([":nr" => $nr_protokollit]);
            if ($chk->fetch()) {
                $err = "Ky numër protokolli ekziston tashmë.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO dokumentet (titulli, numri_protokollit, kategoria_id, owner_id, statusi, versioni)
                     VALUES (:tit, :nr, :kat, :oid, :st, :ver)"
                );
                $stmt->execute([
                    ":tit" => $titulli,
                    ":nr" => $nr_protokollit,
                    ":kat" => $kategoria_id,
                    ":oid" => (int)$_SESSION["user_id"],
                    ":st" => $statusi,
                    ":ver" => $versioni !== "" ? $versioni : "v1.0"
                ]);
                $msg = "Dokumenti i ri u shtua në arkiv.";
            }
        } else {
            $err = "Plotësoni titullin, protokollin dhe kategorinë.";
        }
    }
}

// Menaxhimi i kërkesave të kontaktit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_contact"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $cid = (int)($_POST["cid"] ?? 0);
        if ($cid > 0) {
            $conn->prepare("DELETE FROM kerkesat_kontakt WHERE id = :id")->execute([":id" => $cid]);
            $msg = "Kërkesa e kontaktit u fshi.";
        }
    }
}

// Menaxhimi i përdoruesve
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $uid = (int)($_POST["uid"] ?? 0);
        if ($uid > 0 && $uid !== (int)($_SESSION["user_id"] ?? 0)) {
            $conn->prepare("DELETE FROM users WHERE id = :id")->execute([":id" => $uid]);
            $msg = "Përdoruesi u fshi.";
        } else {
            $err = "Nuk mund të fshini llogarinë tuaj aktive.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_user"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $uid = (int)($_POST["uid"] ?? 0);
        $role = ($_POST["role"] ?? "user") === "admin" ? "admin" : "user";
        if ($uid > 0) {
            $conn->prepare("UPDATE users SET role = :role WHERE id = :id")->execute([":role" => $role, ":id" => $uid]);
            $msg = "Roli i përdoruesit u përditësua.";
        }
    }
}

$edit_doc = null;
if (isset($_GET["edit_doc"])) {
    $eid = (int)$_GET["edit_doc"];
    if ($eid > 0) {
        $st = $conn->prepare("SELECT * FROM dokumentet WHERE id = :id LIMIT 1");
        $st->execute([":id" => $eid]);
        $edit_doc = $st->fetch();
    }
}

$dokumentet = $conn->query(
    "SELECT d.*, k.emri AS kategoria, u.full_name AS autori, u.username AS autor_username
     FROM dokumentet d
     INNER JOIN kategorite k ON d.kategoria_id = k.id
     INNER JOIN users u ON d.owner_id = u.id
     ORDER BY d.id DESC"
)->fetchAll();

$dokumentetArkivuara = array_values(array_filter(
    $dokumentet,
    fn($d) => ($d["statusi"] ?? "") === "I arkivuar"
));

$kontaktet = $conn->query("SELECT * FROM kerkesat_kontakt ORDER BY id DESC")->fetchAll();

$selectUserCols = $hasLastLogin
    ? "u.id, u.full_name, u.username, u.email, u.department, u.role, u.created_at, u.last_login"
    : "u.id, u.full_name, u.username, u.email, u.department, u.role, u.created_at";

$perdoruesit = $conn->query(
    "SELECT {$selectUserCols}, COUNT(d.id) AS nr_dokumentesh
     FROM users u
     LEFT JOIN dokumentet d ON d.owner_id = u.id
     GROUP BY u.id
     ORDER BY u.id DESC"
)->fetchAll();

$totalDocs = count($dokumentet);
$totalArchived = count($dokumentetArkivuara);
$totalUsers = count($perdoruesit);
$totalContacts = count($kontaktet);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paneli i Administratorit - Arkiva Digjitale</title>
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
                    <div class="brand-title">Paneli i Administratorit</div>
                    <div class="brand-subtitle">Kontroll i plotë — <?= htmlspecialchars($_SESSION["full_name"] ?? "Administrator") ?></div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php">Arkivi Publik</a></li>
                <li><a href="admin_dashboard.php" class="active">Admin Panel</a></li>
                <li><a href="export_xml.php" target="_blank">Eksporto XML</a></li>
                <li><a href="logout.php">Dil (<?= htmlspecialchars($_SESSION["username"] ?? "Admin") ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="stats-grid">
            <div class="stat-tile">
                <div class="stat-label">Total Dokumente</div>
                <div class="stat-value"><?= (int)$totalDocs ?></div>
            </div>
            <div class="stat-tile">
                <div class="stat-label">Të Arkivuara</div>
                <div class="stat-value"><?= (int)$totalArchived ?></div>
            </div>
            <div class="stat-tile">
                <div class="stat-label">Përdorues në Sistem</div>
                <div class="stat-value"><?= (int)$totalUsers ?></div>
            </div>
            <div class="stat-tile">
                <div class="stat-label">Kërkesa Kontakti</div>
                <div class="stat-value"><?= (int)$totalContacts ?></div>
            </div>
        </section>

        <?php if ($msg !== ""): ?><div class="alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($err !== ""): ?><div class="alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

        <section class="panel-card">
            <h2>Dokumentet e Arkivuara (<?= (int)$totalArchived ?>)</h2>
            <p style="color: var(--text-muted); margin-bottom: 12px;">Lista e dokumenteve që aktualisht kanë statusin <strong>I arkivuar</strong>.</p>
            <?php if ($totalArchived === 0): ?>
                <p style="color: var(--text-muted);">Aktualisht nuk ka dokumente të arkivuara.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Protokolli</th>
                            <th>Titulli</th>
                            <th>Kategoria</th>
                            <th>Versioni</th>
                            <th>Autori</th>
                            <th>Përditësuar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dokumentetArkivuara as $da): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($da["numri_protokollit"]) ?></strong></td>
                                <td><?= htmlspecialchars($da["titulli"]) ?></td>
                                <td><?= htmlspecialchars($da["kategoria"]) ?></td>
                                <td><?= htmlspecialchars($da["versioni"]) ?></td>
                                <td><?= htmlspecialchars($da["autori"]) ?></td>
                                <td><?= htmlspecialchars($da["updated_at"] ?? $da["created_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel-card">
            <h2>Administrimi i Të Gjitha Dokumenteve</h2>
            <form method="POST" class="form-grid" style="background: var(--surface-soft); padding: 16px; border-radius: 10px; margin-bottom: 20px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <?php if ($edit_doc): ?>
                    <input type="hidden" name="id" value="<?= (int)$edit_doc["id"] ?>">
                    <div class="form-group">
                        <label>Nr. Protokollit</label>
                        <input type="text" name="numri_protokollit" value="<?= htmlspecialchars($edit_doc["numri_protokollit"]) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Titulli</label>
                        <input type="text" name="titulli" value="<?= htmlspecialchars($edit_doc["titulli"]) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kategoria</label>
                        <select name="kategoria_id" required>
                            <?php foreach ($kategorite as $kat): ?>
                                <option value="<?= (int)$kat["id"] ?>" <?= (int)$kat["id"] === (int)$edit_doc["kategoria_id"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($kat["emri"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Versioni</label>
                        <input type="text" name="versioni" value="<?= htmlspecialchars($edit_doc["versioni"]) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Statusi</label>
                        <select name="statusi">
                            <?php foreach (["Aktiv", "Ne shqyrtim", "I arkivuar"] as $st): ?>
                                <option value="<?= $st ?>" <?= $edit_doc["statusi"] === $st ? "selected" : "" ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="update_doc" class="btn-primary">Ruaj Ndryshimet</button>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label>Nr. Protokollit</label>
                        <input type="text" name="numri_protokollit" placeholder="p.sh. KTR-2026-010" required>
                    </div>
                    <div class="form-group">
                        <label>Titulli</label>
                        <input type="text" name="titulli" placeholder="Titulli i dokumentit" required>
                    </div>
                    <div class="form-group">
                        <label>Kategoria</label>
                        <select name="kategoria_id" required>
                            <option value="">-- Kategoria --</option>
                            <?php foreach ($kategorite as $kat): ?>
                                <option value="<?= (int)$kat["id"] ?>"><?= htmlspecialchars($kat["emri"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Versioni</label>
                        <input type="text" name="versioni" value="v1.0">
                    </div>
                    <div class="form-group">
                        <label>Statusi</label>
                        <select name="statusi">
                            <option value="Aktiv">Aktiv</option>
                            <option value="Ne shqyrtim">Ne shqyrtim</option>
                            <option value="I arkivuar">I arkivuar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_doc" class="btn-primary">Shto Dokument</button>
                    </div>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Protokolli</th>
                        <th>Titulli</th>
                        <th>Kategoria</th>
                        <th>Versioni</th>
                        <th>Statusi</th>
                        <th>Autori</th>
                        <th>Veprimet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dokumentet as $d): ?>
                        <tr>
                            <td><?= (int)$d["id"] ?></td>
                            <td><strong><?= htmlspecialchars($d["numri_protokollit"]) ?></strong></td>
                            <td><?= htmlspecialchars($d["titulli"]) ?></td>
                            <td><?= htmlspecialchars($d["kategoria"]) ?></td>
                            <td><?= htmlspecialchars($d["versioni"]) ?></td>
                            <td><?= htmlspecialchars($d["statusi"]) ?></td>
                            <td><?= htmlspecialchars($d["autori"]) ?> (<?= htmlspecialchars($d["autor_username"]) ?>)</td>
                            <td style="white-space: nowrap;">
                                <a href="admin_dashboard.php?edit_doc=<?= (int)$d["id"] ?>">Ndrysho</a>
                                <?php if ($d["statusi"] !== "I arkivuar"): ?>
                                    |
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="doc_id" value="<?= (int)$d["id"] ?>">
                                        <button type="submit" name="archive_doc_admin" style="background: none; border: none; color: var(--violet-electric); cursor: pointer; font-weight: 600;">Arkivo</button>
                                    </form>
                                <?php endif; ?>
                                |
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Fshij dokumentin?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="doc_id" value="<?= (int)$d["id"] ?>">
                                    <button type="submit" name="delete_doc" style="background: none; border: none; color: var(--danger-fg); cursor: pointer; font-weight: 600;">Fshij</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="panel-card">
            <h3>Përdoruesit e Regjistruar &amp; Aktiviteti</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Emri i Plotë</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Departamenti</th>
                        <th>Dokumente</th>
                        <th>Regjistruar</th>
                        <?php if ($hasLastLogin): ?><th>Hyrja e fundit</th><?php endif; ?>
                        <th>Roli</th>
                        <th>Veprimet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perdoruesit as $u): ?>
                        <tr>
                            <td><?= (int)$u["id"] ?></td>
                            <td><?= htmlspecialchars($u["full_name"]) ?></td>
                            <td><?= htmlspecialchars($u["username"]) ?></td>
                            <td><?= htmlspecialchars($u["email"]) ?></td>
                            <td><?= htmlspecialchars($u["department"] ?? "-") ?></td>
                            <td><strong><?= (int)$u["nr_dokumentesh"] ?></strong></td>
                            <td><?= htmlspecialchars($u["created_at"] ?? "-") ?></td>
                            <?php if ($hasLastLogin): ?>
                                <td><?= htmlspecialchars($u["last_login"] ?? "Ende pa hyrje") ?></td>
                            <?php endif; ?>
                            <td>
                                <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="uid" value="<?= (int)$u["id"] ?>">
                                    <select name="role" style="padding: 5px 8px; border-radius: 6px; border: 1px solid #c6d5e5;">
                                        <option value="user" <?= $u["role"] === "user" ? "selected" : "" ?>>user</option>
                                        <option value="admin" <?= $u["role"] === "admin" ? "selected" : "" ?>>admin</option>
                                    </select>
                                    <button type="submit" name="update_user" class="btn-secondary" style="padding: 5px 10px; font-size: 0.82rem;">Ruaj</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Fshij përdoruesin?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="uid" value="<?= (int)$u["id"] ?>">
                                    <button type="submit" name="delete_user" class="btn-secondary" style="padding: 5px 10px; font-size: 0.82rem; color: var(--danger-fg);">Fshij</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="panel-card">
            <h3>Kërkesat nga Formulari i Kontaktit</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Emri</th>
                        <th>Email</th>
                        <th>Departamenti</th>
                        <th>Mesazhi</th>
                        <th>Data</th>
                        <th>Veprimi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kontaktet as $k): ?>
                        <tr>
                            <td><?= (int)$k["id"] ?></td>
                            <td><?= htmlspecialchars($k["emri"]) ?></td>
                            <td><?= htmlspecialchars($k["email"]) ?></td>
                            <td><?= htmlspecialchars($k["departamenti"] ?? "-") ?></td>
                            <td><?= htmlspecialchars($k["mesazhi"]) ?></td>
                            <td><?= htmlspecialchars($k["created_at"] ?? "-") ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Fshij kërkesën?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="cid" value="<?= (int)$k["id"] ?>">
                                    <button type="submit" name="delete_contact" class="btn-secondary" style="padding: 5px 10px; font-size: 0.82rem; color: var(--danger-fg);">Fshij</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>