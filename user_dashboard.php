<?php
// user_dashboard.php
require_once "auth_guard.php";
require_role("user");
require_once "config.php";

$user_id = (int)$_SESSION["user_id"];
$csrf = ensure_csrf_token();
$msg = "";
$err = "";

function upload_document_photo(array $file, ?string &$errorMsg): ?string
{
    $errorMsg = null;
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errorMsg = "Ngarkimi i fotos dështoi. Provoni përsëri.";
        return null;
    }
    if (($file["size"] ?? 0) > 5 * 1024 * 1024) {
        $errorMsg = "Fotoja nuk duhet të kalojë 5 MB.";
        return null;
    }

    $tmpName = $file["tmp_name"] ?? "";
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $tmpName) : mime_content_type($tmpName);
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];
    if (!isset($allowed[$mime])) {
        $errorMsg = "Lejohen vetëm foto JPG, PNG ose WEBP.";
        return null;
    }

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = "doc_" . uniqid("", true) . "." . $allowed[$mime];
    $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmpName, $destPath)) {
        $errorMsg = "Nuk u arrit të ruhet fotoja në dosjen uploads/.";
        return null;
    }

    return "uploads/" . $filename;
}

$userStmt = $conn->prepare("SELECT full_name, username, email, department FROM users WHERE id = :uid LIMIT 1");
$userStmt->execute([":uid" => $user_id]);
$profile = $userStmt->fetch();

$katStmt = $conn->query("SELECT id, emri, kodi FROM kategorite ORDER BY emri ASC");
$kategorite = $katStmt->fetchAll();

// Regjistrimi i një dokumenti të ri
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_doc"])) {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $err = "Token i pavlefshëm CSRF.";
    } else {
        $titulli = trim($_POST["titulli"] ?? "");
        $nr_protokollit = trim($_POST["numri_protokollit"] ?? "");
        $kategoria_id = (int)($_POST["kategoria_id"] ?? 0);
        $versioni = trim($_POST["versioni"] ?? "v1.0");
        $statusi = $_POST["statusi"] ?? "Aktiv";
        $pershkrimi = trim($_POST["pershkrimi"] ?? "");

        $allowedStatuses = ["Aktiv", "Ne shqyrtim", "I arkivuar"];
        if (!in_array($statusi, $allowedStatuses, true)) {
            $statusi = "Aktiv";
        }

        if ($titulli === "" || $nr_protokollit === "" || $kategoria_id <= 0) {
            $err = "Plotësoni titullin, numrin e protokollit dhe kategorinë.";
        } else {
            $chk = $conn->prepare("SELECT id FROM dokumentet WHERE numri_protokollit = :nr LIMIT 1");
            $chk->execute([":nr" => $nr_protokollit]);
            if ($chk->fetch()) {
                $err = "Ky numër protokolli ekziston tashmë.";
            } else {
                $uploadErr = null;
                $fotoPath = null;
                if (isset($_FILES["foto_dokumenti"])) {
                    $fotoPath = upload_document_photo($_FILES["foto_dokumenti"], $uploadErr);
                }

                if ($uploadErr !== null) {
                    $err = $uploadErr;
                } else {
                    $ins = $conn->prepare(
                        "INSERT INTO dokumentet (titulli, numri_protokollit, kategoria_id, owner_id, statusi, versioni, pershkrimi, foto_path)
                         VALUES (:tit, :nr, :kat, :oid, :st, :ver, :persh, :foto)"
                    );
                    $ins->execute([
                        ":tit" => $titulli,
                        ":nr" => $nr_protokollit,
                        ":kat" => $kategoria_id,
                        ":oid" => $user_id,
                        ":st" => $statusi,
                        ":ver" => $versioni !== "" ? $versioni : "v1.0",
                        ":persh" => $pershkrimi !== "" ? $pershkrimi : null,
                        ":foto" => $fotoPath
                    ]);
                    $msg = "Dokumenti u regjistrua me sukses në sistem.";
                }
            }
        }
    }
}

// Arkivimi i shpejtë i një dokumenti të përdoruesit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["archive_doc"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $docId = (int)($_POST["doc_id"] ?? 0);
        if ($docId > 0) {
            $upd = $conn->prepare("UPDATE dokumentet SET statusi = 'I arkivuar' WHERE id = :id AND owner_id = :uid");
            $upd->execute([":id" => $docId, ":uid" => $user_id]);
            $msg = "Dokumenti u kalua në statusin 'I arkivuar'.";
        }
    }
}

// Fshirja e një dokumenti të përdoruesit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_doc"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $docId = (int)($_POST["doc_id"] ?? 0);
        if ($docId > 0) {
            $del = $conn->prepare("DELETE FROM dokumentet WHERE id = :id AND owner_id = :uid");
            $del->execute([":id" => $docId, ":uid" => $user_id]);
            $msg = "Dokumenti u fshi me sukses.";
        }
    }
}

// Shtimi te të preferuarat
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_fav"])) {
    if (verify_csrf_token($_POST["csrf_token"] ?? null)) {
        $favDocId = (int)($_POST["fav_doc_id"] ?? 0);
        if ($favDocId > 0) {
            $insFav = $conn->prepare("INSERT IGNORE INTO te_preferuara_dok (user_id, dokument_id) VALUES (:uid, :did)");
            $insFav->execute([":uid" => $user_id, ":did" => $favDocId]);
            $msg = "Dokumenti u shtua te të preferuarat.";
        }
    }
}

if (isset($_GET["remove_fav"])) {
    $remId = (int)$_GET["remove_fav"];
    if ($remId > 0) {
        $delFav = $conn->prepare("DELETE FROM te_preferuara_dok WHERE user_id = :uid AND dokument_id = :did");
        $delFav->execute([":uid" => $user_id, ":did" => $remId]);
        $msg = "Dokumenti u hoq nga lista e të preferuarave.";
    }
}

$myDocsStmt = $conn->prepare(
    "SELECT d.id, d.titulli, d.numri_protokollit, d.statusi, d.versioni, d.foto_path, d.created_at, k.emri AS kategoria
     FROM dokumentet d
     INNER JOIN kategorite k ON d.kategoria_id = k.id
     WHERE d.owner_id = :uid
     ORDER BY d.created_at DESC"
);
$myDocsStmt->execute([":uid" => $user_id]);
$myDocs = $myDocsStmt->fetchAll();

$allDocsStmt = $conn->query("SELECT id, numri_protokollit, titulli FROM dokumentet ORDER BY titulli ASC");
$allDocs = $allDocsStmt->fetchAll();

$favStmt = $conn->prepare(
    "SELECT d.id, d.titulli, d.numri_protokollit, f.created_at
     FROM te_preferuara_dok f
     INNER JOIN dokumentet d ON f.dokument_id = d.id
     WHERE f.user_id = :uid
     ORDER BY f.created_at DESC"
);
$favStmt->execute([":uid" => $user_id]);
$favorites = $favStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paneli i Përdoruesit - Arkiva Digjitale</title>
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
                    <div class="brand-title">Paneli i Përdoruesit</div>
                    <div class="brand-subtitle">Menaxhimi i dokumenteve personale dhe arkivimit</div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <ul>
                <li><a href="index.html">Ballina</a></li>
                <li><a href="katalogu.php">Arkivi Publik</a></li>
                <li><a href="user_dashboard.php" class="active">Paneli Im</a></li>
                <li><a href="logout.php">Dil (<?= htmlspecialchars($_SESSION["username"] ?? "") ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="panel-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <h2>Mirë se erdhe, <?= htmlspecialchars($profile["full_name"] ?? "Përdorues") ?></h2>
                <span class="badge badge-archived">Roli: Përdorues • <?= htmlspecialchars($profile["department"] ?? "Administratë") ?></span>
            </div>

            <?php if ($msg !== ""): ?>
                <div class="alert-ok"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <?php if ($err !== ""): ?>
                <div class="alert-err"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <h3>Regjistro Dokument të Ri</h3>
            <form method="POST" enctype="multipart/form-data" class="form-grid" style="background: var(--surface-soft); padding: 18px; border-radius: 10px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                <div class="form-group">
                    <label for="titulli">Titulli i dokumentit:*</label>
                    <input type="text" id="titulli" name="titulli" required>
                </div>

                <div class="form-group">
                    <label for="numri_protokollit">Nr. Protokollit:*</label>
                    <input type="text" id="numri_protokollit" name="numri_protokollit" placeholder="p.sh. ARK-2026-004" required>
                </div>

                <div class="form-group">
                    <label for="kategoria_id">Kategoria:*</label>
                    <select id="kategoria_id" name="kategoria_id" required>
                        <option value="">-- Zgjidh kategorinë --</option>
                        <?php foreach ($kategorite as $kat): ?>
                            <option value="<?= (int)$kat["id"] ?>"><?= htmlspecialchars($kat["kodi"] . " - " . $kat["emri"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="versioni">Versioni:</label>
                    <input type="text" id="versioni" name="versioni" value="v1.0">
                </div>

                <div class="form-group">
                    <label for="statusi">Statusi:</label>
                    <select id="statusi" name="statusi">
                        <option value="Aktiv">Aktiv</option>
                        <option value="Ne shqyrtim">Ne shqyrtim</option>
                        <option value="I arkivuar">I arkivuar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="foto_dokumenti">Foto / Skanim (JPG, PNG, WEBP):</label>
                    <input type="file" id="foto_dokumenti" name="foto_dokumenti" accept=".jpg,.jpeg,.png,.webp,image/*">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="pershkrimi">Përshkrimi / Shënime:</label>
                    <textarea id="pershkrimi" name="pershkrimi" rows="2"></textarea>
                </div>

                <button type="submit" name="create_doc" class="btn-primary">Ruaj Dokumentin</button>
            </form>
        </section>

        <section class="panel-card">
            <h3>Dokumentet e Mia</h3>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Protokolli</th>
                        <th>Titulli</th>
                        <th>Kategoria</th>
                        <th>Versioni</th>
                        <th>Statusi</th>
                        <th>Data</th>
                        <th>Veprimet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myDocs as $doc): ?>
                        <?php
                            $badgeClass = match ($doc["statusi"]) {
                                "Aktiv" => "badge-active",
                                "Ne shqyrtim" => "badge-review",
                                default => "badge-archived",
                            };
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($doc["foto_path"])): ?>
                                    <img src="<?= htmlspecialchars($doc["foto_path"]) ?>" alt="Foto dokumenti" class="doc-thumb-sm">
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($doc["numri_protokollit"]) ?></strong></td>
                            <td><?= htmlspecialchars($doc["titulli"]) ?></td>
                            <td><?= htmlspecialchars($doc["kategoria"]) ?></td>
                            <td><?= htmlspecialchars($doc["versioni"]) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($doc["statusi"]) ?></span></td>
                            <td><?= htmlspecialchars($doc["created_at"]) ?></td>
                            <td style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <?php if ($doc["statusi"] !== "I arkivuar"): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <input type="hidden" name="doc_id" value="<?= (int)$doc["id"] ?>">
                                        <button type="submit" name="archive_doc" class="btn-secondary" style="padding: 5px 10px; font-size: 0.82rem;">Arkivo</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Fshij këtë dokument?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="doc_id" value="<?= (int)$doc["id"] ?>">
                                    <button type="submit" name="delete_doc" class="btn-secondary" style="padding: 5px 10px; font-size: 0.82rem; color: var(--danger-fg);">Fshij</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="panel-card">
            <h3>Dokumente të Preferuara (Shkurtore)</h3>
            <form method="POST" class="form-grid" style="margin-bottom: 14px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="form-group">
                    <select name="fav_doc_id" required>
                        <option value="">-- Zgjidh dokumentin --</option>
                        <?php foreach ($allDocs as $ad): ?>
                            <option value="<?= (int)$ad["id"] ?>"><?= htmlspecialchars($ad["numri_protokollit"] . " - " . $ad["titulli"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" name="add_fav" class="btn-primary">Shto te të preferuarat</button>
                </div>
            </form>

            <ul style="list-style: none; display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($favorites as $f): ?>
                    <li style="background: var(--surface-soft); border: 1px solid var(--border); padding: 7px 14px; border-radius: 999px; font-size: 0.88rem;">
                        <strong><?= htmlspecialchars($f["numri_protokollit"]) ?></strong> — <?= htmlspecialchars($f["titulli"]) ?>
                        <a href="user_dashboard.php?remove_fav=<?= (int)$f["id"] ?>" style="margin-left: 8px; color: var(--danger-fg); font-weight: bold;">✕</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Sistemi i Menaxhimit të Dokumenteve dhe Arkivës Digjitale</p>
    </footer>
</body>
</html>