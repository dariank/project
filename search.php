<?php require_once 'header.php'; ?>

<main class="container">
    <!-- Поисковая строка -->
    <div style="margin: 30px 0; display: flex; align-items: center; gap: 10px;">
        <input type="text" id="search-input" placeholder="Введите запрос, например: Джинсы женские" 
               style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button onclick="search()" class="btn" style="padding: 12px 24px; font-size: 16px;">Найти</button>
        <a href="index.php" style="color: #000; text-decoration: underline; font-size: 16px;">Закрыть</a>
    </div>

    <!-- Теги -->
    <div style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('куртка')">куртка</span>
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('пальто')">пальто</span>
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('джинсы')">джинсы</span>
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('платье')">платье</span>
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('джемпер')">джемпер</span>
        <span style="border: 1px solid #ddd; padding: 8px 16px; border-radius: 4px; cursor: pointer;" onclick="setSearch('брюки')">брюки</span>
    </div>

    <?php
if ($_GET['q'] ?? '') {
    $search_query = '%' . trim($_GET['q']) . '%';
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.name LIKE ?");
    $stmt->execute([$search_query]);
    $search_results = $stmt->fetchAll();

    if (!empty($search_results)): ?>
        <h3>Результаты поиска по запросу "<?= htmlspecialchars($_GET['q']) ?>"</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php foreach ($search_results as $p): ?>
                <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                        <img src="images/<?= htmlspecialchars($p['image']) ?>" 
                             alt="<?= htmlspecialchars($p['name']) ?>"
                             style="width: 100%; height: 300px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                        <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                            
                                <p style="font-size: 14px; color: #666; margin: 8px 0;"><?= htmlspecialchars($p['description']) ?></p>
                            </div>
                            <p style="font-size: 18px; font-weight: 700; margin: 8px 0;">
                                <?= number_format($p['price'], 0, '', ' ') ?> ₽
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="margin-top: 40px; text-align: center; font-size: 18px; color: #666;">
            Ничего не найдено по запросу "<?= htmlspecialchars($_GET['q']) ?>"
        </div>
    <?php endif;
} else {
    // Если нет поискового запроса — показываем "Часто ищут" и "Популярные товары"
    ?>
    <!-- Часто ищут + Популярные товары -->
    <div style="display: flex; gap: 40px; margin-top: 40px; flex-wrap: wrap;">
        <!-- Левый блок: Часто ищут -->
        <div style="flex: 1; min-width: 250px;">
            <h3>Часто ищут</h3>
            <ul style="list-style: none; padding: 0; margin: 10px 0;">
                <?php
                $popular_searches = [
                    'куртка ',
                    'пальто ',
                    'брюки'
                ];
                foreach ($popular_searches as $search): ?>
                    <li style="display: flex; align-items: center; gap: 8px; margin: 8px 0;">
                        <i class="bi bi-search" style="font-size: 14px; color: #888;"></i>
                        <a href="search.php?q=<?= urlencode($search) ?>" style="color: #000; text-decoration: none;"><?= htmlspecialchars($search) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Правый блок: Популярные товары -->
        <div style="flex: 2; min-width: 300px;">
            <h3>Популярные товары</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-top: 20px;">
                <?php
                $popular_names = [
                    'Брюки палаццо',
                    'Топ без рукавов',
                    'Рубашка oversize ',
                    'Пальто с поясом'
                ];

                  foreach ($popular_names as $name) {
            $stmt = $pdo->prepare("SELECT id, name, price, image, description FROM products WHERE name = ?");
            $stmt->execute([$name]);
            $p = $stmt->fetch();

            if ($p): ?>
                <a href="product.php?id=<?= $p['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                    <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                        <img src="images/<?= htmlspecialchars($p['image']) ?>" 
                             alt="<?= htmlspecialchars($p['name']) ?>" 
                             style="width: 100%; height: 300px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                        <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                               
                                <p style="font-size: 14px; color: #666; margin: 8px 0;"><?= htmlspecialchars($p['description']) ?></p>
                            </div>
                            <p style="font-size: 18px; font-weight: 700; margin: 8px 0;">
                                <?= number_format($p['price'], 0, '', ' ') ?> ₽
                            </p>
                        </div>
                    </div>
                </a>
            <?php endif;
                }
                ?>
            </div>
        </div>
    </div>
<?php }
 ?>

    <?php  ?>
</main>

<script>
function search() {
    const query = document.getElementById('search-input').value;
    if (query.trim()) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
    }
}

function setSearch(term) {
    document.getElementById('search-input').value = term;
    search();
}
</script>

<style>
/* Иконки Bootstrap */
.bi {
    vertical-align: -0.125em;
    fill: currentColor;
}

/* Адаптив */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
}
</style>

<?php require_once 'footer.php'; ?>