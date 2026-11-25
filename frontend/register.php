<?php
session_start();
if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regjistrohu - Arkiva Digjitale</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: #0f172a;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.35);
        }
        .auth-header { text-align: center; margin-bottom: 24px; }
        .auth-header h1 { margin: 8px 0 4px; font-size: 22px; color: #fff; }
        .auth-header p { margin: 0; font-size: 13px; color: #94a3b8; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 6px; font-weight: 500; }
        input {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            padding: 11px 14px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            outline: none;
        }
        input:focus { border-color: #2563eb; }
        .btn-submit {
            width: 100%;
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .msg {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 14px;
            display: none;
        }
        .msg.error { display: block; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
        .msg.success { display: block; background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .auth-footer { text-align: center; margin-top: 18px; font-size: 13px; color: #94a3b8; }
        .auth-footer a { color: #60a5fa; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size: 36px;">🛡️</div>
            <h1>Krijo Llogari të Re</h1>
            <p>Qasje e sigurt në Arkivën Digjitale</p>
        </div>

        <div id="msgBox" class="msg"></div>

        <form id="registerForm">
            <div class="form-group">
                <label for="fullName">Emri dhe Mbiemri</label>
                <input type="text" id="fullName" required placeholder="p.sh. Arben Krasniqi">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" required placeholder="arben@shembull.com">
            </div>
            <div class="form-group">
                <label for="password">Fjalëkalimi (min. 6 karaktere)</label>
                <input type="password" id="password" required minlength="6" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="confirmPassword">Konfirmo Fjalëkalimin</label>
                <input type="password" id="confirmPassword" required minlength="6" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-submit">Regjistrohu</button>
        </form>

        <div class="auth-footer">
            Keni llogari? <a href="login.php">Kyçuni këtu</a>
        </div>
    </div>

<script>
    const REGISTER_API = "http://localhost:8083/api/auth/register";

    document.getElementById("registerForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const msgBox = document.getElementById("msgBox");
        msgBox.className = "msg";
        msgBox.style.display = "none";

        const fullName = document.getElementById("fullName").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirmPassword").value;

        if (password !== confirmPassword) {
            msgBox.className = "msg error";
            msgBox.innerText = "Fjalëkalimet nuk përputhen!";
            return;
        }

        try {
            const res = await fetch(REGISTER_API, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ fullName, email, password, role: "USER" })
            });

            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                msgBox.className = "msg success";
                msgBox.innerText = "Llogaria u krijua me sukses! Po ridrejtoheni te kyçja...";
                setTimeout(() => { window.location.href = "login.php"; }, 1400);
            } else {
                msgBox.className = "msg error";
                msgBox.innerText = data.message || "Regjistrimi dështoi. Kontrolloni nëse email-i ekziston.";
            }
        } catch (err) {
            msgBox.className = "msg error";
            msgBox.innerText = "Nuk u arrit lidhja me serverin në portin 8083.";
        }
    });
</script>
</body>
</html>