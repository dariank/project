<?php require_once 'header.php'; ?>

<!-- Боковое меню -->
<div class="sidebar-menu">
    <div class="sidebar-header">
        <span>📍 Нижний Новгород</span>
        <a href="index.php" style="float: right; font-size: 24px; color: #000;">×</a>
    </div>

    <div class="sidebar-categories">
        <div style="display: flex; gap: 20px; margin: 20px 0;">
            <a href="#" class="gender active">ЖЕНЩИНАМ</a>
        </div>

        <div class="category-list">
            <a href="new_collection.php">НОВАЯ КОЛЛЕКЦИЯ</a>
            <a href="catalog.php">ВСЯ КОЛЛЕКЦИЯ</a> 
            <a href="clothing.php">ОДЕЖДА</a>
            <a href="catalog.php?category=<?= urlencode('Аксессуары') ?>">АКСЕССУАРЫ</a>
           <a href="sale.php" style="color: #ff4d4d;">РАСПРОДАЖА</a>
        </div>

        </div>
    </div>
</div>

<!-- Фон для затемнения основного контента -->
<div class="overlay"></div>

<style>
.sidebar-menu {
    position: fixed;
    left: 0;
    top: 0;
    width: 300px;
    height: 100vh;
    background: #fff;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    padding: 20px;
    overflow-y: auto;
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.gender {
    text-decoration: none;
    color: #000;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.gender.active {
    border-bottom: 2px solid #000;
}

.category-list a,
.additional-links a {
    display: block;
    padding: 10px 0;
    text-decoration: none;
    color: #000;
    font-weight: 600;
    transition: color 0.2s;
}
.category-list a:hover,
.additional-links a:hover {
    color: #666;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
}

/* Адаптив */
@media (max-width: 768px) {
    .sidebar-menu {
        width: 80%;
    }
}
</style>

<?php require_once 'footer.php'; ?>