<?php
require_once 'db.php'; // ← только подключение к БД, без HTML

session_start();
$isAuth = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';

// Обработка отправки отзыва — ДО ЛЮБОГО ВЫВОДА
if ($_POST['add_review'] ?? null) {
    $user_name = trim($_POST['user_name'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? null;

    // Если пользователь авторизован — возьми имя из БД
    if ($user_id && empty($user_name)) {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if ($user) {
            $user_name = $user['name'];
        }
    }

    // Обязательно: имя должно быть
    if ($user_name && $rating >= 1 && $rating <= 5 && $comment && $product_id) {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $user_name, $rating, $comment]);
        // Перенаправляем без якоря — просто обновляем страницу
        header('Location: product.php?id=' . $product_id);
        exit;
    } else {
        $message = 'Заполните все поля';
    }
}

// Теперь можно подключать шапку
require_once 'header.php';

// Остальной код без изменений...
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Товар не найден.');
}

$product_id = (int)$_GET['id'];

// Получаем товар из БД
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die('Товар не найден.');
}

// Проверяем, является ли товар аксессуаром
$isAccessory = ($product['category_name'] === 'Аксессуары');

// Получаем цвета и размеры для этого товара
$stmt = $pdo->prepare("
    SELECT pv.*, co.name as color_name, co.hex_code, s.name as size_name
    FROM product_variants pv
    JOIN colors co ON pv.color_id = co.id
    JOIN sizes s ON pv.size_id = s.id
    WHERE pv.product_id = ?
");
$stmt->execute([$product_id]);
$variants = $stmt->fetchAll();

// Группируем по цветам
$colors = [];
foreach ($variants as $v) {
    if (!isset($colors[$v['color_name']])) {
        $colors[$v['color_name']] = [
            'hex' => $v['hex_code'],
            'sizes' => []
        ];
    }
    $colors[$v['color_name']]['sizes'][] = $v;
}

$firstColor = reset($colors);
$uniqueSizes = array_unique(array_column($firstColor['sizes'], 'size_name'));
?>

<main class="container product-page">
    <div style="display: flex; flex-wrap: wrap; gap: 40px; margin: 50px 0;">
        <!-- Фото товара -->
        <div style="flex: 1; min-width: 300px;">
            <img src="images/<?= htmlspecialchars($product['image']) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 style="width: 400px; height: 500px; object-fit: scale-down; background: #fff; border-radius: 8px;">
        </div>

        <!-- Информация о товаре -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: center;">
            <div>
                <span style="color: #888; font-size: 14px;"><?= htmlspecialchars($product['category_name']) ?></span>
                <h1 style="font-size: 32px; margin: 10px 0;"><?= htmlspecialchars($product['name']) ?></h1>
                <p style="font-size: 24px; font-weight: 700; margin: 15px 0;">
                    <?= number_format($product['price'], 0, '', ' ') ?> ₽
                </p>

                <!-- Цвет -->
                <?php if (!empty($colors)): ?>
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 10px;">ЦВЕТ:</label>
                        <div style="font-size: 16px; font-weight: 600; color: #000;">
                            <?= htmlspecialchars($firstColor['sizes'][0]['color_name']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Размер (только если не аксессуар) -->
                <?php if (!$isAccessory && !empty($colors)): ?>
                    <div style="margin: 20px 0;">
                        <label style="display: block; margin-bottom: 10px;">РАЗМЕР:</label>
                        <div id="selected-size" style="font-size: 16px; font-weight: 600; color: #000; margin-bottom: 10px;">
                            <?= htmlspecialchars($firstColor['sizes'][0]['size_name']) ?>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php foreach ($uniqueSizes as $sizeName): ?>
                                <label style="cursor: pointer; padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 50px; text-align: center; position: relative;">
                                    <input type="radio" name="size" value="<?= $sizeName ?>" style="display: none;" <?= $firstColor['sizes'][0]['size_name'] === $sizeName ? 'checked' : '' ?>>
                                    <span><?= $sizeName ?></span>
                                    <?php
                                    if ($firstColor['sizes'][0]['stock'] > 0): ?>
                                        <span style="position: absolute; top: -5px; right: -5px; background: orange; color: white; font-size: 8px; width: 10px; height: 10px; border-radius: 50%;"></span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <a href="javascript:void(0)" onclick="openSizeTable()" style="font-size: 12px; color: #000; text-decoration: underline; margin-top: 5px; display: inline-block;">ТАБЛИЦА РАЗМЕРОВ</a>
                    </div>
                <?php endif; ?>
<!-- Описание и состав -->
<div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 0; text-align: center;">
    <!-- Заголовок аккордеона -->
    <div style="
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        width: 100%;
        padding: 12px;
        text-align: left;
        color: #000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    " onclick="toggleAccordion()">
        <span>ОПИСАНИЕ И СОСТАВ</span>
        <span id="accordion-icon" style="font-size: 20px; color: #000;">+</span>
    </div>

    <!-- Раскрытый контент -->
    <div id="accordion-content" style="display: none; margin-top: 0; padding: 15px; background: #fff; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; font-size: 14px; line-height: 1.5; text-align: left;">
        <div style="margin-bottom: 12px; padding-left: 0;">
            <strong>Описание:</strong><br>
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
        <?php if (!empty($product['composition'])): ?>
            <div style="padding-left: 0;">
                <strong>Состав:</strong><br>
                <?= htmlspecialchars($product['composition']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAccordion() {
    const content = document.getElementById('accordion-content');
    const icon = document.getElementById('accordion-icon');
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = '−';
    } else {
        content.style.display = 'none';
        icon.textContent = '+';
    }
}
</script>

                <!-- Кнопка "Добавить в корзину" -->
<form action="cart.php" method="POST" style="margin-top: 30px;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="selected_color" value="<?= htmlspecialchars($firstColor['sizes'][0]['color_name']) ?>">
   <?php if (!$isAccessory): ?>
    <input type="hidden" name="selected_size" id="selected-size-input" value="<?= htmlspecialchars($firstColor['sizes'][0]['size_name']) ?>">
<?php endif; ?>
    <button type="submit" name="action" value="add" class="btn" style="width: 100%; padding: 12px; font-size: 16px;">
        ДОБАВИТЬ В КОРЗИНУ
    </button>
</form>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <a href="catalog.php" style="color: #000; text-decoration: underline;">← Вернуться в каталог</a>
    </div>
</main>

<!-- Отзывы -->
<div style="margin-top: 60px; border-top: 1px solid #eee; padding-top: 40px;">
    <h3 style="text-align: center; margin-bottom: 30px;">Отзывы покупателей</h3>

    <?php
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();

    if (!empty($reviews)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php foreach ($reviews as $review): ?>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <!-- Имя -->
                    <div style="font-weight: 600; color: #000; margin-bottom: 8px;">
                        <?= htmlspecialchars($review['user_name']) ?>
                    </div>
                    <!-- Рейтинг -->
                    <div style="margin-bottom: 10px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span style="color: <?= $i <= $review['rating'] ? '#FFD700' : '#ddd' ?>; font-size: 20px;">★</span>
                        <?php endfor; ?>
                    </div>
                    <!-- Отзыв -->
                    <p style="font-size: 16px; margin-bottom: 10px; line-height: 1.5;">
                        <?= nl2br(htmlspecialchars($review['comment'])) ?>
                    </p>
                    <!-- Дата -->
                    <div style="font-size: 12px; color: #666;">
                        <?= date('d.m.Y', strtotime($review['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; font-size: 18px; color: #666;">
            Пока нет отзывов. Будьте первым!
        </p>
    <?php endif; ?>
</div>

   <!-- Форма добавления отзыва (только для авторизованных) -->
<?php if ($isAuth): ?>
      <div style="margin-top: 40px; background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 8px; width: 100%; max-width: 100%;">
        <h4 style="margin-bottom: 20px;">Оставить отзыв</h4>
        <form id="reviewForm" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label>Ваше имя:</label><br>
                <input type="text" name="user_name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Покупатель') ?>" required style="padding: 8px; width: 100%; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div>
                <label>Оценка:</label><br>
                <div style="display: flex; gap: 5px; margin: 5px 0;" id="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="rating" value="<?= $i ?>" id="star_<?= $product['id'] ?>_<?= $i ?>" style="display: none;" required>
                        <label for="star_<?= $product['id'] ?>_<?= $i ?>" class="star-label" style="cursor: pointer; font-size: 20px; color: #ddd; transition: color 0.2s;">
                            ★
                        </label>
                    <?php endfor; ?>
                </div>
            </div>

            <div>
                <label>Текст отзыва:</label><br>
                <textarea name="comment" rows="4" required style="padding: 8px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>

            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <button type="submit" name="add_review" class="btn" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 4px; align-self: start;">
                Отправить отзыв
            </button>
        </form>

        <div id="reviewMessage" style="margin-top: 10px; display: none;"></div>
    </div>

    <script>
    document.getElementById('reviewForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        const response = await fetch('add_review.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        const msg = document.getElementById('reviewMessage');
        msg.style.display = 'block';

        if (result.success) {
            msg.innerHTML = '<div style="color: green;">Отзыв добавлен!</div>';
            msg.style.color = 'green';
            // Добавляем отзыв в список сразу
            setTimeout(() => {
                location.reload(); // Просто обновляем страницу (надёжно!)
            }, 1000);
        } else {
            msg.innerHTML = '<div style="color: red;">' + result.error + '</div>';
        }
    });
    </script>
<?php else: ?>
    <div style="margin-top: 40px; text-align: center; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px;">
        <p>Чтобы оставить отзыв, <a href="login.php" style="color: #000; text-decoration: underline;">войдите</a>.</p>
    </div>
<?php endif; ?>

             <!-- Модальное окно -->
<div id="sizeTableModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; max-width: 90%; max-height: 90%; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Таблица размеров</h3>
            <button onclick="closeSizeTable()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px; text-align: left;">Российский размер</th>
                    <th style="padding: 10px; text-align: center;">40</th>
                    <th style="padding: 10px; text-align: center;">42</th>
                    <th style="padding: 10px; text-align: center;">44</th>
                    <th style="padding: 10px; text-align: center;">46</th>
                    <th style="padding: 10px; text-align: center;">48</th>
                    <th style="padding: 10px; text-align: center;">50</th>
                    <th style="padding: 10px; text-align: center;">52</th>
                    <th style="padding: 10px; text-align: center;">54</th>
                    <th style="padding: 10px; text-align: center;">56</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">Размер couteruly</td>
                    <td style="padding: 10px; text-align: center;">25</td>
                    <td style="padding: 10px; text-align: center;">26</td>
                    <td style="padding: 10px; text-align: center;">28</td>
                    <td style="padding: 10px; text-align: center;">30</td>
                    <td style="padding: 10px; text-align: center;">31</td>
                    <td style="padding: 10px; text-align: center;">33</td>
                    <td style="padding: 10px; text-align: center;">34</td>
                    <td style="padding: 10px; text-align: center;">36</td>
                    <td style="padding: 10px; text-align: center;">37</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">Международный размер</td>
                    <td style="padding: 10px; text-align: center;">XXS</td>
                    <td style="padding: 10px; text-align: center;">XS</td>
                    <td style="padding: 10px; text-align: center;">S</td>
                    <td style="padding: 10px; text-align: center;">M</td>
                    <td style="padding: 10px; text-align: center;">L</td>
                    <td style="padding: 10px; text-align: center;">XL</td>
                    <td style="padding: 10px; text-align: center;">XXL</td>
                    <td style="padding: 10px; text-align: center;">XXXL</td>
                    <td style="padding: 10px; text-align: center;">4XL</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">Обхват талии, в см</td>
                    <td style="padding: 10px; text-align: center;">61-63</td>
                    <td style="padding: 10px; text-align: center;">63-66</td>
                    <td style="padding: 10px; text-align: center;">68-70</td>
                    <td style="padding: 10px; text-align: center;">72-75</td>
                    <td style="padding: 10px; text-align: center;">75-77</td>
                    <td style="padding: 10px; text-align: center;">80-82</td>
                    <td style="padding: 10px; text-align: center;">82-85</td>
                    <td style="padding: 10px; text-align: center;">87-90</td>
                    <td style="padding: 10px; text-align: center;">90-92</td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">Обхват бедер, в см</td>
                    <td style="padding: 10px; text-align: center;">86-90</td>
                    <td style="padding: 10px; text-align: center;">90-94</td>
                    <td style="padding: 10px; text-align: center;">96-98</td>
                    <td style="padding: 10px; text-align: center;">100-104</td>
                    <td style="padding: 10px; text-align: center;">104-106</td>
                    <td style="padding: 10px; text-align: center;">108-112</td>
                    <td style="padding: 10px; text-align: center;">112-114</td>
                    <td style="padding: 10px; text-align: center;">116-118</td>
                    <td style="padding: 10px; text-align: center;">118-120</td>
                </tr>
                <tr>
                    
            </tbody>
        </table>
    </div>
</div>

<script>
<?php if (!$isAccessory): ?>
function openSizeTable() {
    document.getElementById('sizeTableModal').style.display = 'flex';
}
function closeSizeTable() {
    document.getElementById('sizeTableModal').style.display = 'none';
}
document.querySelectorAll('input[name="size"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const selectedSize = this.value;
        document.getElementById('selected-size').textContent = selectedSize;
        document.getElementById('selected-size-input').value = selectedSize;
    });
});
<?php endif; ?>
</script>
<script>
// Подсветка звёзд при клике и загрузке
document.addEventListener('DOMContentLoaded', function() {
    const starLabels = document.querySelectorAll('.star-label');
    const starInputs = document.querySelectorAll('input[name="rating"]');

    // Обработчик клика по звездам
    starLabels.forEach((label, index) => {
        label.addEventListener('click', () => {
            // Устанавливаем checked для радиокнопки
            const input = document.getElementById(label.htmlFor);
            if (input) {
                input.checked = true;
            }

            // Подсвечиваем все звезды до текущей (включительно)
            for (let i = 0; i <= index; i++) {
                starLabels[i].style.color = '#FFD700';
            }
            // Остальные — серые
            for (let i = index + 1; i < starLabels.length; i++) {
                starLabels[i].style.color = '#ddd';
            }
        });
    });

    // Обновляем подсветку при загрузке страницы (если есть выбранная оценка)
    const selectedInput = document.querySelector('input[name="rating"]:checked');
    if (selectedInput) {
        const selectedIndex = parseInt(selectedInput.value) - 1;
        for (let i = 0; i <= selectedIndex; i++) {
            starLabels[i].style.color = '#FFD700';
        }
        for (let i = selectedIndex + 1; i < starLabels.length; i++) {
            starLabels[i].style.color = '#ddd';
        }
    }
});
</script>
<?php require_once 'footer.php'; ?>