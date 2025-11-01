<nav class="sidebar">
    <div class="sidebar-header">
        <h2>Warranty SaaS</h2>
        <span class="admin-badge">Admin</span>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <span class="menu-icon">🏠</span>
                <span class="menu-text">Главная</span>
            </a>
        </li>
        
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
            <a href="users.php">
                <span class="menu-icon">👥</span>
                <span class="menu-text">Пользователи</span>
            </a>
        </li>
        
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'stats.php' ? 'active' : '' ?>">
            <a href="stats.php">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Статистика</span>
            </a>
        </li>
        
        <li class="menu-divider"></li>
        
        <li class="menu-item">
            <a href="../logout.php">
                <span class="menu-icon">🚪</span>
                <span class="menu-text">Выход</span>
            </a>
        </li>
    </ul>
</nav>
