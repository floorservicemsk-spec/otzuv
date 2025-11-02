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
                'index.php' => 'Главная',
                'design.php' => 'Дизайн',
                'integrations.php' => 'Интеграции',
                'submissions.php' => 'Заявки'
            ];
            echo $titles[basename($_SERVER['PHP_SELF'])] ?? 'Dashboard';
            ?>
        </h3>
    </div>
    
    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?= h($_SESSION['user_email']) ?></span>
            <span class="user-role"><?= $_SESSION['user_role'] === 'admin' ? 'Администратор' : 'Клиент' ?></span>
        </div>
    </div>
</header>

<script>
function toggleMobileMenu() {
    document.querySelector('.sidebar').classList.toggle('mobile-open');
}
</script>
