<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$currentUserRole = strtoupper(trim($_SESSION['role']));
$currentUserName = $_SESSION['full_name'] ?? 'Përdorues';
$isAdmin = ($currentUserRole === 'ADMIN');
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard i Avancuar - Arkiva Digjitale</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #0f172a;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            color: #f8fafc;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e293b;
            border-right: 1px solid #334155;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px;
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .sidebar-brand h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
        }

        .sidebar-brand p {
            margin: 0;
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #2563eb;
            color: #ffffff;
        }

        .sidebar-user {
            border-top: 1px solid #334155;
            padding-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 15px;
            flex-shrink: 0;
        }

        /* Main Area */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-user-bar {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            color: #e2e8f0;
            font-size: 14px;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 8px;
        }

        .role-admin {
            background: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(37, 99, 235, 0.4);
        }

        .role-user {
            background: rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 7px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: white;
        }

        .main-content {
            padding: 32px;
            flex: 1;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 28px;
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

        .btn-primary-link {
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-primary-link:hover {
            background: #1d4ed8;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
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

        /* Filter Bar */
        .filter-bar {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 16px;
            border-radius: 16px;
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 220px;
        }

        .search-box input {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 11px 16px 11px 40px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #2563eb;
        }

        .search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .custom-select {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            padding: 10px 14px;
            border-radius: 10px;
            outline: none;
            cursor: pointer;
        }

        .btn-refresh {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-refresh:hover {
            background: #1d4ed8;
        }

        /* Table */
        .table-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 680px;
        }

        th {
            background: #0f172a;
            padding: 15px 20px;
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        td {
            padding: 15px 20px;
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
            flex-shrink: 0;
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-archived {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

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
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }

        .state-container {
            text-align: center;
            padding: 56px 20px;
            color: #94a3b8;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 820px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #334155;
            }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div>
            <div class="sidebar-brand">
                <div style="font-size: 32px;">📁</div>
                <div>
                    <h2>ArkivaDigjitale</h2>
                    <p>Enterprise System</p>
                </div>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">🏠 Dashboard</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="upload.php">➕ Shto Dokument</a></li>
                    <li><a href="audit.php">🛡️ Gjurma e Sistemit</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <?= htmlspecialchars(mb_strtoupper(mb_substr($currentUserName, 0, 2))) ?>
            </div>
            <div style="overflow: hidden;">
                <strong style="font-size: 13px; display: block; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($currentUserName) ?>
                </strong>
                <span style="font-size: 11px; color: #94a3b8;">
                    <?= $isAdmin ? 'Administrator' : 'Përdorues' ?>
                </span>
            </div>
        </div>
    </aside>

    <div class="main-wrapper">
        <div class="top-user-bar">
            <div>
                👤 Përdoruesi aktiv: <strong><?= htmlspecialchars($currentUserName) ?></strong>
                <span class="role-badge <?= $isAdmin ? 'role-admin' : 'role-user' ?>">
                    <?= htmlspecialchars($currentUserRole) ?>
                </span>
            </div>
            <a href="logout.php" class="btn-logout">Dil (Logout)</a>
        </div>

        <main class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Menaxhimi i Dokumenteve</h1>
                    <p>Pasqyra reale e arkivës digjitale dhe integrimi me Spring Boot API</p>
                </div>
                <?php if ($isAdmin): ?>
                    <a href="upload.php" class="btn-primary-link">+ Shto Dokument të Ri</a>
                <?php endif; ?>
            </div>

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
                <?php if ($isAdmin): ?>
                <div class="stat-card">
                    <div class="stat-icon red">📦</div>
                    <div class="stat-data">
                        <div class="value" id="statArchived">0</div>
                        <div class="label">Të Arkivuar</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="filter-bar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Kërko me titull ose emër skedari..." onkeyup="filterDocuments()">
                </div>
                <?php if ($isAdmin): ?>
                <select id="statusFilter" class="custom-select" onchange="filterDocuments()">
                    <option value="ALL">Gjendja: Të gjitha</option>
                    <option value="ACTIVE">VETËM AKTIVË</option>
                    <option value="ARCHIVED">VETËM TË ARKIVUAR</option>
                </select>
                <?php endif; ?>
                <button class="btn-refresh" onclick="fetchDocuments()">Përditëso</button>
            </div>

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
    </div>

<script>
    const API_URL = "http://localhost:8083/api/documents";
    const userRole = <?php echo json_encode($currentUserRole); ?>;
    let allDocuments = [];

    function isDocArchived(doc) {
        if (typeof doc.archived === 'boolean') return doc.archived;
        if (typeof doc.isArchived === 'boolean') return doc.isArchived;
        if (typeof doc.status === 'string') {
            const s = doc.status.toUpperCase();
            return s === 'ARCHIVED' || s === 'I ARKIVUAR';
        }
        return false;
    }

    async function fetchDocuments() {
        showState('loading');
        try {
            const response = await fetch(API_URL);
            if (!response.ok) throw new Error("API Error");
            const rawDocuments = await response.json();

            if (userRole === 'ADMIN') {
                allDocuments = rawDocuments;
            } else {
                allDocuments = rawDocuments.filter(doc => !isDocArchived(doc));
            }

            updateStats();
            renderTable(allDocuments);
        } catch (error) {
            showState('error');
        }
    }

    function updateStats() {
        const total = allDocuments.length;
        const active = allDocuments.filter(d => !isDocArchived(d)).length;
        const archived = allDocuments.filter(d => isDocArchived(d)).length;

        document.getElementById('statTotal').innerText = total;
        document.getElementById('statActive').innerText = active;

        const statArchivedEl = document.getElementById('statArchived');
        if (statArchivedEl) {
            statArchivedEl.innerText = archived;
        }
    }

    function renderTable(documents) {
        if (documents.length === 0) {
            showState('empty');
            return;
        }
        const tbody = document.getElementById('documentsTableBody');
        tbody.innerHTML = '';

        documents.forEach(doc => {
            const archived = isDocArchived(doc);
            const ext = doc.fileName ? doc.fileName.split('.').pop().toUpperCase() : 'FILE';

            let actionButtons = `<span style="color: #64748b; font-size: 12px;">🔒 Vetëm lexim</span>`;
            if (userRole === 'ADMIN') {
                actionButtons = `
                    <div class="btn-action-group">
                        ${!archived ? `<button class="btn-archive" onclick="archiveDocument(${doc.documentId})">Arkivo</button>` : ''}
                        <button class="btn-delete" onclick="deleteDocument(${doc.documentId})">Fshij</button>
                    </div>
                `;
            }

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
                    <span class="badge-status ${archived ? 'status-archived' : 'status-active'}">
                        ${archived ? 'I Arkivuar' : 'Aktiv'}
                    </span>
                </td>
                <td>${actionButtons}</td>
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
        const statusSelect = document.getElementById('statusFilter');
        const status = (userRole === 'ADMIN' && statusSelect) ? statusSelect.value : 'ACTIVE';

        const filtered = allDocuments.filter(doc => {
            const archived = isDocArchived(doc);
            const matchesTitle =
                (doc.title || '').toLowerCase().includes(query) ||
                (doc.fileName || '').toLowerCase().includes(query);

            if (userRole !== 'ADMIN') {
                return matchesTitle && !archived;
            }

            const matchesStatus =
                status === 'ALL' ? true :
                (status === 'ARCHIVED' ? archived : !archived);

            return matchesTitle && matchesStatus;
        });

        renderTable(filtered);
    }

    async function archiveDocument(id) {
        if (userRole !== 'ADMIN') return;
        if (!confirm("A jeni të sigurt që dëshironi ta arkivoni këtë dokument?")) return;
        try {
            const res = await fetch(`${API_URL}/archive/${id}`, { method: 'PUT' });
            if (res.ok) {
                fetchDocuments();
            } else {
                alert("Gabim gjatë arkivimit.");
            }
        } catch (err) {
            alert("Nuk u arrit lidhja me serverin për arkivim.");
        }
    }

    async function deleteDocument(id) {
        if (userRole !== 'ADMIN') return;
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
        return text ? String(text).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        }) : '';
    }

    document.addEventListener('DOMContentLoaded', fetchDocuments);
</script>

</body>
</html>