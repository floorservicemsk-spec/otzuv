<nav class="sidebar">
    <div class="sidebar-header">
        <h2>Warranty SaaS</h2>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
            <a href="index.php">
                <span class="menu-icon">🏠</span>
                <span class="menu-text">Главная</span>
            </a>
        </li>
        
                <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'form-labels.php' ? 'active' : '' ?>">
                    <a href="form-labels.php">
                        <span class="menu-icon">📋</span>
                        <span class="menu-text">Названия полей</span>
                    </a>
                </li>
                
                <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'discount-cards.php' ? 'active' : '' ?>">
                    <a href="discount-cards.php">
                        <span class="menu-icon">🎁</span>
                        <span class="menu-text">Карточки товаров</span>
                    </a>
                </li>
        
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'design.php' ? 'active' : '' ?>">
            <a href="design.php">
                <span class="menu-icon">🎨</span>
                <span class="menu-text">Дизайн</span>
            </a>
        </li>
        
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'integrations.php' ? 'active' : '' ?>">
            <a href="integrations.php">
                <span class="menu-icon">🔌</span>
                <span class="menu-text">Интеграции</span>
            </a>
        </li>
        
        <li class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'submissions.php' ? 'active' : '' ?>">
            <a href="submissions.php">
                <span class="menu-icon">📊</span>
                <span class="menu-text">Заявки</span>
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
