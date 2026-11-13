<?php
include 'components/header.php';
?>

<!-- Shtohet wrapper-i kryesor me margin-left për të mos u mbuluar nga sidebar -->
<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="text-white fw-bold">Gjurma e Sistemit (Audit Logs)</h2>
                <p class="text-muted">Historiku i plotë i veprimeve dhe ngjarjeve të regjistruara në server në kohë reale.</p>
            </div>
        </div>

        <!-- Tabela e Log-eve -->
        <div class="card bg-dark text-white border-secondary shadow-lg">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
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
document.addEventListener("DOMContentLoaded", function() {
    fetch('http://localhost:8082/api/documents/audit-logs')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('auditTableBody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Nuk ka asnjë veprim të regjistruar ende.</td></tr>`;
                return;
            }

            data.reverse().forEach(log => {
                let badgeColor = 'bg-secondary';
                if (log.action.includes('CREATE')) badgeColor = 'bg-success';
                else if (log.action.includes('DELETE')) badgeColor = 'bg-danger';
                else if (log.action.includes('DOWNLOAD')) badgeColor = 'bg-info text-dark';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold">#${log.auditId}</td>
                    <td><span class="badge ${badgeColor}">${log.action}</span></td>
                    <td>${log.details}</td>
                    <td class="text-muted small">${new Date(log.timestamp).toLocaleString()}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Gabim gjatë marrjes së log-eve:', error);
            document.getElementById('auditTableBody').innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Gabim në lidhje me serverin API (Spring Boot). Kontrollo a është ndezur porti 8082.</td></tr>`;
        });
});
</script>