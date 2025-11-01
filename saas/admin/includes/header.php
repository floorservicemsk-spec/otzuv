<header class="dashboard-header">
    <div class="header-left">
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <h3 class="page-title">
            <?php
            $titles = [
                'dashboard.php' => 'Админ-панель',
                'users.php' => 'Управление пользователями',
                'stats.php' => 'Статистика'
            ];
            echo $titles[basename($_SERVER['PHP_SELF'])] ?? 'Admin';
            ?>
        </h3>
    </div>
    
    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?= h($_SESSION['user_email']) ?></span>
            <span class="user-role badge badge-admin">Администратор</span>
        </div>
    </div>
</header>

<script>
function toggleMobileMenu() {
    document.querySelector('.sidebar').classList.toggle('mobile-open');
}
</script>
