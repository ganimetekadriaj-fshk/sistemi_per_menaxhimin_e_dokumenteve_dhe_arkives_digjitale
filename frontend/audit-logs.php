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

include 'components/header.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2 class="text-white fw-bold mb-1">Gjurma e Sistemit (Audit Logs)</h2>
                <p class="text-muted mb-0">Historiku i plotë i veprimeve dhe ngjarjeve të regjistruara në server në kohë reale.</p>
            </div>
            <div class="col-auto">
                <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            </div>
        </div>

        <div class="card bg-dark text-white border-secondary shadow-lg">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Veprimi (Action)</th>
                                <th>Detajet e Veprimit</th>
                                <th>Koha e Regjistrimit (Timestamp)</th>
                            </tr>
                        </thead>
                        <tbody id="auditTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Duke ngarkuar të dhënat e auditimit...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(text) {
    return text !== null && text !== undefined
        ? String(text).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        })
        : '';
}

document.addEventListener("DOMContentLoaded", function() {
    fetch('http://localhost:8083/api/documents/audit-logs')
        .then(response => {
            if (!response.ok) throw new Error("HTTP " + response.status);
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('auditTableBody');
            tbody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Nuk ka asnjë veprim të regjistruar ende.</td></tr>`;
                return;
            }

            [...data].reverse().forEach(log => {
                const actionText = String(log.action || '');
                let badgeColor = 'bg-secondary';
                if (actionText.includes('CREATE') || actionText.includes('UPLOAD')) badgeColor = 'bg-success';
                else if (actionText.includes('DELETE')) badgeColor = 'bg-danger';
                else if (actionText.includes('ARCHIVE')) badgeColor = 'bg-warning text-dark';
                else if (actionText.includes('DOWNLOAD')) badgeColor = 'bg-info text-dark';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold">#${escapeHtml(log.auditId)}</td>
                    <td><span class="badge ${badgeColor}">${escapeHtml(actionText)}</span></td>
                    <td>${escapeHtml(log.details || '-')}</td>
                    <td class="text-muted small">${log.timestamp ? escapeHtml(new Date(log.timestamp).toLocaleString()) : '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Gabim gjatë marrjes së log-eve:', error);
            document.getElementById('auditTableBody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Gabim në lidhje me serverin API (Spring Boot). Kontrollo a është ndezur porti 8083.</td></tr>`;
        });
});
</script>