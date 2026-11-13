<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shto Dokument - Arkiva Digjitale</title>
    <style>
        .form-container {
            max-width: 650px;
            margin: 0 auto;
            background: #1e293b;
            border: 1px solid #334155;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
        .form-title {
            margin-top: 0;
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 12px 16px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: #2563eb;
        }
        textarea.form-control {
            resize: vertical;
            height: 100px;
        }
        .btn-submit {
            background: #2563eb;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            width: 100%;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .alert-box {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        .alert-success { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .alert-error { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body>

<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="form-container">
        <h2 class="form-title">📄 Ngarko Dokument të Ri</h2>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px;">Regjistroni dokumentin e ri në bazën e të dhënave.</p>

        <div id="alertBox" class="alert-box"></div>

        <form id="uploadForm" onsubmit="submitDocument(event)">
            <div class="form-group">
                <label>Titulli i Dokumentit *</label>
                <input type="text" id="title" class="form-control" required placeholder="p.sh. Raporti i Analizës 2026">
            </div>

            <div class="form-group">
                <label>Përshkrimi</label>
                <textarea id="description" class="form-control" placeholder="Shkruaj një përshkrim të shkurtër..."></textarea>
            </div>

            <div class="form-group">
                <label>Zgjidh Skedarin (PDF, etj.) *</label>
                <input type="file" id="fileInput" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Kategoria (ID) *</label>
                <input type="number" id="categoryId" class="form-control" value="1" required>
            </div>

            <button type="submit" class="btn-submit">Shto Dokumentin</button>
        </form>
    </div>
</main>

<script>
    const API_URL = "http://localhost:8083/api/documents";

    async function submitDocument(event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append("title", document.getElementById('title').value);
        formData.append("description", document.getElementById('description').value);
        formData.append("categoryId", document.getElementById('categoryId').value);
        formData.append("uploadedBy", "1");
        
        const fileInput = document.getElementById('fileInput');
        if (fileInput.files.length > 0) {
            formData.append("file", fileInput.files[0]);
        }

        const alertBox = document.getElementById('alertBox');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData // Vëre se nuk vendosim headers për Content-Type, brauzeri e vendos automatikisht për multipart/form-data
            });

            if (response.ok) {
                alertBox.className = "alert-box alert-success";
                alertBox.innerHTML = "✔️ <strong>Sukses!</strong> Dokumenti dhe skedari u ruajtën me sukses.";
                alertBox.style.display = "block";
                document.getElementById('uploadForm').reset();
            } else {
                const errText = await response.text();
                throw new Error(errText || "Dështoi ruajtja");
            }
        } catch (error) {
            alertBox.className = "alert-box alert-error";
            alertBox.innerHTML = "⚠️ <strong>Gabim!</strong> " + error.message;
            alertBox.style.display = "block";
        }
    }
</script>

</body>
</html>