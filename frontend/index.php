<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard I Avancuar - Arkiva Digjitale</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .page-title h1 {
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #ffffff;
        }

        .page-title p {
            margin: 0;
            color: #94a3b8;
            font-size: 14px;
        }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-icon.blue { background: rgba(37, 99, 235, 0.15); color: #3b82f6; }
        .stat-icon.green { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
        .stat-icon.red { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

        .stat-data .value {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
        }

        .stat-data .label {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Controls & Filter Bar */
        .filter-bar {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 16px;
            border-radius: 16px;
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 12px 16px 12px 42px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }

        .search-box input:focus {
            border-color: #2563eb;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .custom-select {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            padding: 0 16px;
            border-radius: 10px;
            outline: none;
            cursor: pointer;
        }

        .btn-refresh {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-refresh:hover { background: #1d4ed8; }

        /* Modern Table */
        .table-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: #0f172a;
            padding: 16px 20px;
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        td {
            padding: 16px 20px;
            border-top: 1px solid #334155;
            font-size: 14px;
            color: #e2e8f0;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon-badge {
            width: 36px;
            height: 36px;
            background: #334155;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-active { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-archived { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .btn-action-group {
            display: flex;
            gap: 8px;
        }

        .btn-archive {
            background: rgba(217, 119, 6, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(217, 119, 6, 0.3);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-archive:hover {
            background: #d97706;
            color: white;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }

        .state-container {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .hidden { display: none; }
    </style>
</head>
<body style="background: #0f172a; margin: 0;">

<?php include 'components/header.php'; ?>

<main class="main-content" style="padding: 32px;">
    <div class="page-header">
        <div class="page-title">
            <h1>Menaxhimi i Dokumenteve</h1>
            <p>Pasqyra reale e arkivës digjitale dhe integrimi me Spring Boot API</p>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">📄</div>
            <div class="stat-data">
                <div class="value" id="statTotal">0</div>
                <div class="label">Gjithsej Dokumente</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-data">
                <div class="value" id="statActive">0</div>
                <div class="label">Dokumente Aktive</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">📦</div>
            <div class="stat-data">
                <div class="value" id="statArchived">0</div>
                <div class="label">Të Arkivuar</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Kërko me titull ose emër skedari..." onkeyup="filterDocuments()">
        </div>
        <select id="statusFilter" class="custom-select" onchange="filterDocuments()">
            <option value="ALL">Gjendja: Të gjitha</option>
            <option value="ACTIVE">VETËM AKTIVË</option>
            <option value="ARCHIVED">VETËM TË ARKIVUAR</option>
        </select>
        <button class="btn-refresh" onclick="fetchDocuments()">Përditëso</button>
    </div>

    <!-- Data Table -->
    <div class="table-card">
        <div id="loadingState" class="state-container">
            <h3>🔄 Duke sinkronizuar me serverin...</h3>
        </div>
        <div id="errorState" class="state-container hidden" style="color: #f87171;">
            <h3>⚠️ Lidhja dështoi!</h3>
            <p>Kontrolloni nëse Spring Boot është i ndezur në portin 8083.</p>
        </div>
        <div id="emptyState" class="state-container hidden">
            <h3>📭 Nuk ka dokumente</h3>
            <p>Nuk u gjet asnjë rekord në databazë.</p>
        </div>

        <table id="documentsTable" class="hidden">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dokumenti</th>
                    <th>Lloji / Madhësia</th>
                    <th>Statusi</th>
                    <th>Veprimet</th>
                </tr>
            </thead>
            <tbody id="documentsTableBody"></tbody>
        </table>
    </div>
</main>

<script>
    // Përditëso portën nëse përdor 8083 ose 8082
    const API_URL = "http://localhost:8083/api/documents";
    let allDocuments = [];

    async function fetchDocuments() {
        showState('loading');
        try {
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error("API Error");
            allDocuments = await response.json();
            updateStats();
            renderTable(allDocuments);
        } catch (error) {
            showState('error');
        }
    }

    function updateStats() {
        const total = allDocuments.length;
        const active = allDocuments.filter(d => !d.archived).length;
        const archived = allDocuments.filter(d => d.archived).length;

        document.getElementById('statTotal').innerText = total;
        document.getElementById('statActive').innerText = active;
        document.getElementById('statArchived').innerText = archived;
    }

    function renderTable(documents) {
        if (documents.length === 0) {
            showState('empty');
            return;
        }
        const tbody = document.getElementById('documentsTableBody');
        tbody.innerHTML = '';

        documents.forEach(doc => {
            const ext = doc.fileName ? doc.fileName.split('.').pop().toUpperCase() : 'FILE';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong style="color: #64748b;">#${doc.documentId}</strong></td>
                <td>
                    <div class="file-info">
                        <div class="file-icon-badge">📁</div>
                        <div>
                            <strong style="color: #ffffff; display: block;">${escapeHtml(doc.title)}</strong>
                            <small style="color: #94a3b8;">${escapeHtml(doc.fileName)}</small>
                        </div>
                    </div>
                </td>
                <td><span style="color: #cbd5e1;">${ext}</span> · <small style="color: #64748b;">${((doc.fileSize || 0) / 1024).toFixed(1)} KB</small></td>
                <td>
                    <span class="badge-status ${doc.archived ? 'status-archived' : 'status-active'}">
                        ${doc.archived ? 'I Arkivuar' : 'Aktiv'}
                    </span>
                </td>
                <td>
                    <div class="btn-action-group">
                        ${!doc.archived ? `<button class="btn-archive" onclick="archiveDocument(${doc.documentId})">Arkivo</button>` : ''}
                        <button class="btn-delete" onclick="deleteDocument(${doc.documentId})">Fshij</button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
        showState('table');
    }

    function showState(state) {
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorState').classList.add('hidden');
        document.getElementById('emptyState').classList.add('hidden');
        document.getElementById('documentsTable').classList.add('hidden');

        if (state === 'loading') document.getElementById('loadingState').classList.remove('hidden');
        if (state === 'error') document.getElementById('errorState').classList.remove('hidden');
        if (state === 'empty') document.getElementById('emptyState').classList.remove('hidden');
        if (state === 'table') document.getElementById('documentsTable').classList.remove('hidden');
    }

    function filterDocuments() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('statusFilter').value;
        const filtered = allDocuments.filter(doc => {
            const matchesTitle = doc.title.toLowerCase().includes(query) || doc.fileName.toLowerCase().includes(query);
            const matchesStatus = status === 'ALL' ? true : (status === 'ARCHIVED' ? doc.archived : !doc.archived);
            return matchesTitle && matchesStatus;
        });
        renderTable(filtered);
    }

    async function archiveDocument(id) {
        if (!confirm("A jeni të sigurt që dëshironi ta arkivoni këtë dokument?")) return;
        try {
            const res = await fetch(`${API_URL}/archive/${id}`, { method: 'PUT' });
            if (res.ok) {
                alert("Dokumenti u arkivua me sukses!");
                fetchDocuments(); // Rifreskon listën dhe statistikat automatikisht
            } else {
                alert("Gabim gjatë arkivimit.");
            }
        } catch (err) {
            alert("Nuk u arrit lidhja me serverin për arkivim.");
        }
    }

    async function deleteDocument(id) {
        if (!confirm("A jeni të sigurt që dëshironi ta fshini këtë dokument?")) return;
        try {
            const res = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
            if (res.status === 200 || res.status === 204) {
                fetchDocuments();
            } else {
                alert("Gabim gjatë fshirjes.");
            }
        } catch (err) {
            alert("Gabim gjatë lidhjes me serverin.");
        }
    }

    function escapeHtml(text) {
        return text ? text.replace(/[&<>"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]; }) : '';
    }

    document.addEventListener('DOMContentLoaded', fetchDocuments);
</script>

</body>
</html>