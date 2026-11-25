<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">📁</div>
        <div class="brand-text">
            <h2>ArkivaDigjitale</h2>
            <span>Enterprise System</span>
        </div>
    </div>

    <nav class="sidebar-menu">
        <a href="index.php" class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span>Dashboard</span>
        </a>
        
        <a href="upload.php" class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'upload.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Shto Dokument</span>
        </a>

        <!-- Opsioni i ri për Audit Trail -->
        <a href="audit-logs.php" class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'audit-logs.php') ? 'active' : ''; ?>">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <span>Gjurma e Sistemit</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="avatar">GK</div>
            <div class="user-info">
                <span class="name">Ganimete Kadriaj</span>
                <span class="role">Administrator</span>
            </div>
        </div>
    </div>
</aside>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #0f172a;
        color: #f8fafc;
        margin: 0;
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 260px;
        background: #1e293b;
        border-right: 1px solid #334155;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 24px;
        box-sizing: border-box;
        position: fixed;
        height: 100vh;
        z-index: 100;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 40px;
    }

    .brand-icon {
        font-size: 28px;
        background: rgba(37, 99, 235, 0.2);
        padding: 8px;
        border-radius: 12px;
    }

    .brand-text h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.5px;
    }

    .brand-text span {
        font-size: 11px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #94a3b8;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .menu-item:hover {
        background: #334155;
        color: #ffffff;
    }

    .menu-item.active {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #334155;
    }

    .avatar {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        color: white;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-info .name {
        font-size: 13px;
        font-weight: 600;
        color: #ffffff;
    }

    .user-info .role {
        font-size: 11px;
        color: #64748b;
    }

    .main-content {
        margin-left: 260px;
        flex: 1;
        padding: 36px 40px;
    }
</style>