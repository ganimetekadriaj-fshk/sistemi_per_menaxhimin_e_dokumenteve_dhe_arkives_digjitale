<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
if (strtoupper(trim($_SESSION['role'])) !== 'ADMIN') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentet e Arkivuara - Arkiva Digjitale</title>
    <style>
        .main-content { padding: 32px; color: white; font-family: system-ui, -apple-system, sans-serif; }
        .table-container { background: #1e293b; border: 1px solid #334155; padding: 24px; border-radius: 16px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .btn-back { background: #2563eb; color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; }
        .btn-back:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #334155; font-size: 14px; color: #94a3b8; }
        th { color: #ffffff; font-weight: 600; }
    </style>
</head>
<body style="background: #0f172a; margin: 0;">

<main class="main-content">
    <div class="table-container">
        <div class="top-bar">
            <div>
                <h2 style="margin: 0 0 4px 0;">📦 Dokumentet e Arkivuara</h2>
                <p style="margin: 0; color: #94a3b8;">Lista e dokumenteve që janë zhvendosur në arkivë.</p>
            </div>
            <a href="index.php" class="btn-back">← Kthehu te Dashboard</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulli</th>
                    <th>Emri i Skedarit</th>
                    <th>Përshkrimi</th>
                </tr>
            </thead>
            <tbody id="archivedTableBody">
                <tr><td colspan="4" style="text-align: center;">Duke ngarkuar...</td></tr>
            </tbody>
        </table>
    </div>
</main>

<script>
    const API_URL = "http://localhost:8083/api/documents";

    function escapeHtml(text) {
        return text ? String(text).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        }) : '';
    }

    async function loadArchivedDocuments() {
        const tbody = document.getElementById('archivedTableBody');
        try {
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error("API Error");
            const documents = await response.json();
            
            tbody.innerHTML = "";

            const archivedDocs = documents.filter(doc => doc.archived === true);

            if (archivedDocs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center;">Nuk ka dokumente të arkivuara.</td></tr>`;
                return;
            }

            archivedDocs.forEach(doc => {
                tbody.innerHTML += `
                    <tr>
                        <td>#${escapeHtml(doc.documentId)}</td>
                        <td style="color: #ffffff; font-weight: 500;">${escapeHtml(doc.title)}</td>
                        <td>${escapeHtml(doc.fileName)}</td>
                        <td>${escapeHtml(doc.description || '-')}</td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error("Gabim gjatë leximit të dokumenteve:", error);
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #f87171;">Gabim gjatë lidhjes me serverin (porti 8083).</td></tr>`;
        }
    }

    loadArchivedDocuments();
</script>

</body>
</html>