<?php require_once 'header.php'; ?>
 <style>
.hover-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
</style>

<!-- Баннер "OUTER WEAR. 25 NEW SEASON" -->
<div style="margin: 0; padding: 0; width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; overflow: hidden;">
    <a href="catalog.php" style="display: block;">
        <img src="images/banner_one_one.png" 
             alt="OUTER WEAR. 25 NEW SEASON" 
             style="width: 100%; height: auto; display: block;">
    </a>
</div>

<!-- Новая коллекция -->
<section style="padding: 80px 0; background: #fff;">
    <div class="container">
        <h2 style="text-align: center; font-size: 36px; font-weight: 700; margin-bottom: 50px; letter-spacing: -1px; color: #000;">
            Новая коллекция
        </h2>

        <!-- Фиксированная сетка 4 колонки -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <?php
            $products = [
                'Кардиган белый',
                'Платье черное',
                'Брюки-клеш черные',
                'Пиджак черный '
            ];
           foreach ($products as $name): 
                $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE name = ?");
                $stmt->execute([$name]);
                $item = $stmt->fetch();
                if ($item): ?>
                    <a href="product.php?id=<?= $item['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                       <div class="hover-card" 
     style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column;">

                            <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 style="width: 100%; height: 240px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                            <div style="padding: 16px; text-align: center; font-size: 14px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 15px; margin: 8px 0; font-weight: 600;"><?= htmlspecialchars($item['name']) ?></h3>
                                    <div style="margin: 8px 0; font-weight: 700; color: #000;">
                                        <?= number_format($item['price'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                                <!-- "Подробнее →" УБРАНО -->
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Баннер "Акция на аксессуары" — ВНЕ .container -->
<div style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; margin-top: 60px; margin-bottom: 60px; overflow: hidden;">
    <a href="catalog.php?category=<?= urlencode('Аксессуары') ?>" style="display: block; ">
        <img src="images/banner_acs.png" 
             alt="Акция на аксессуары" 
              style="width: 100%; height: auto; display: block;">
    </a>
</div>

<!-- Хиты продаж -->
<section style="padding: 80px 0; background: #fff;">
    <div class="container">
        <h2 style="text-align: center; font-size: 36px; font-weight: 700; margin-bottom: 50px; letter-spacing: -1px; color: #000;">
            Хиты продаж
        </h2>

        <!-- Фиксированная сетка 4 колонки -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <?php
            $products = [
                'Блузка с воланами',
                'Брюки палаццо',
                'Жакет с пуговицами',
                'Куртка-бомбер'
            ];
            foreach ($products as $name): 
                $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE name = ?");
                $stmt->execute([$name]);
                $item = $stmt->fetch();
                if ($item): ?>
                    <a href="product.php?id=<?= $item['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                       <div class="hover-card" 
     style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column;">

                            <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 style="width: 100%; height: 240px; object-fit: scale-down; background: #fff; border-bottom: 1px solid #eee;">

                            <div style="padding: 16px; text-align: center; font-size: 14px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="font-size: 15px; margin: 8px 0; font-weight: 600;"><?= htmlspecialchars($item['name']) ?></h3>
                                    <div style="margin: 8px 0; font-weight: 700; color: #000;">
                                        <?= number_format($item['price'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!-- Баннер "Акция на аксессуары" — ВНЕ .container -->
<div style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; margin-top: 60px; margin-bottom: 60px; overflow: hidden;">
    <a href="catalog.php?category=<?= urlencode('Джинсы') ?>" style="display: block; ">
        <img src="images/banner_jeans.png" 
             alt="Акция на аксессуары" 
              style="width: 100%; height: auto; display: block;">
    </a>
</div>

<?php require_once 'footer.php'; ?>