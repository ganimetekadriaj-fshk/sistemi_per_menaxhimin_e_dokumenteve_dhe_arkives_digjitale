<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentet e Arkivuara - Arkiva Digjitale</title>
    <style>
        .main-content { padding: 32px; color: white; font-family: sans-serif; }
        .table-container { background: #1e293b; border: 1px solid #334155; padding: 24px; border-radius: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #334155; font-size: 14px; color: #94a3b8; }
        th { color: #ffffff; font-weight: 600; }
    </style>
</head>
<body style="background: #0f172a; margin: 0;">

<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="table-container">
        <h2>📦 Dokumentet e Arkivuara</h2>
        <p>Lista e dokumenteve që janë zhvendosur në arkivë.</p>
        
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
                <!-- Të dhënat do të ngarkohen përmes JavaScript -->
            </tbody>
        </table>
    </div>
</main>

<script>
    const API_URL = "http://localhost:8082/api/documents";

    async function loadArchivedDocuments() {
        try {
            const response = await fetch(API_URL);
            const documents = await response.json();
            
            const tbody = document.getElementById('archivedTableBody');
            tbody.innerHTML = "";

            // Filtrojmë vetëm dokumentet që kanë archived == true
            const archivedDocs = documents.filter(doc => doc.archived === true);

            if (archivedDocs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center;">Nuk ka dokumente të arkivuara.</td></tr>`;
                return;
            }

            archivedDocs.forEach(doc => {
                tbody.innerHTML += `
                    <tr>
                        <td>#${doc.documentId}</td>
                        <td style="color: #ffffff; font-weight: 500;">${doc.title}</td>
                        <td>${doc.fileName}</td>
                        <td>${doc.description || '-'}</td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error("Gabim gjatë leximit të dokumenteve:", error);
        }
    }

    loadArchivedDocuments();
</script>

</body>
</html>