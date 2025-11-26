<?php
session_start();
require_once 'header.php';

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';

// Обработка добавления товара
if ($_POST['action'] ?? null === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $selected_size = trim($_POST['selected_size'] ?? '');
    $selected_color = trim($_POST['selected_color'] ?? '');

    // Получаем категорию
    $stmt = $pdo->prepare("SELECT p.category_id, p.stock FROM products p WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $message = "Товар не найден.";
    } else {
        // Определяем, аксессуар ли это
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$product['category_id']]);
        $category = $stmt->fetch();
        $isAccessory = ($category && $category['name'] === 'Аксессуары');

        // Проверяем цвет
        if (!$selected_color) {
            $message = "Цвет не выбран.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM colors WHERE name = ?");
            $stmt->execute([$selected_color]);
            $color = $stmt->fetch();
            if (!$color) {
                $message = "Цвет не найден.";
            } else {
                $color_id = $color['id'];
                $size_id = null;

                // Для не-аксессуаров — проверяем размер
                if (!$isAccessory) {
                    if (!$selected_size) {
                        $message = "Размер не выбран.";
                    } else {
                        $stmt = $pdo->prepare("SELECT id FROM sizes WHERE name = ?");
                        $stmt->execute([$selected_size]);
                        $size = $stmt->fetch();
                        if (!$size) {
                            $message = "Размер не найден.";
                        } else {
                            $size_id = $size['id'];
                        }
                    }
                }

                // Если нет ошибок — добавляем в корзину
                if (!$message) {
                    $variant_key = $product_id . '_' . $color_id;
                    if ($size_id !== null) {
                        $variant_key .= '_' . $size_id;
                    }

                    if ($quantity > $product['stock']) {
                        $message = "Недостаточно товара на складе. Доступно: " . $product['stock'] . " шт.";
                    } else {
                        $_SESSION['cart'][$variant_key] = ($quantity + ($_SESSION['cart'][$variant_key] ?? 0));
                        $message = "Товар добавлен в корзину!";
                    }
                }
            }
        }
    }
}
// Обработка удаления по уникальному ключу
if ($_GET['remove'] ?? null) {
    $variant_key = $_GET['remove'];
    unset($_SESSION['cart'][$variant_key]);
    header('Location: cart.php');
    exit;
}

// Обработка изменения количества
if ($_POST['update'] ?? null) {
    $messages = [];
    foreach ($_POST['qty'] ?? [] as $variant_key => $qty) {
        $qty = max(0, (int)$qty);
        if ($qty == 0) {
            unset($_SESSION['cart'][$variant_key]);
        } else {
            // Разбираем ключ
            $parts = explode('_', $variant_key);
            if (count($parts) < 2 || count($parts) > 3) continue;
            [$product_id, $color_id] = array_map('intval', [$parts[0], $parts[1]]);

            // Получаем остаток
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            $max_available = $product ? $product['stock'] : 0;
            $product_name = $product ? htmlspecialchars($product['name']) : "Без названия";

            if ($qty > $max_available) {
                $messages[] = "Товар «{$product_name}»: доступно только {$max_available} шт.";
                $_SESSION['cart'][$variant_key] = $max_available;
            } else {
                $_SESSION['cart'][$variant_key] = $qty;
            }
        }
    }

    if (!empty($messages)) {
        $_SESSION['cart_error_messages'] = $messages;
    }

    header('Location: cart.php');
    exit;
}
// === ВЫВОД ОШИБОК ===
$error_messages = $_SESSION['cart_error_messages'] ?? [];
if (!empty($error_messages)) {
    unset($_SESSION['cart_error_messages']);
}

/// Получаем товары из корзины
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $variant_key => $qty) {
        // Разбираем ключ
        $parts = explode('_', $variant_key);
        if (count($parts) < 2 || count($parts) > 3) continue;

        $product_id = (int)$parts[0];
        $color_id = (int)$parts[1];
        $size_id = null;

        if (count($parts) === 3) {
            $size_id = (int)$parts[2];
        }

        // Получаем данные о товаре
        if ($size_id !== null) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.price, p.image,
                       co.name as color_name, s.name as size_name
                FROM products p
                JOIN product_variants pv ON p.id = pv.product_id AND pv.color_id = ? AND pv.size_id = ?
                JOIN colors co ON pv.color_id = co.id
                JOIN sizes s ON pv.size_id = s.id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->execute([$color_id, $size_id, $product_id]);
        } else {
            // Аксессуар — без размера
            $stmt = $pdo->prepare("
                SELECT p.id, p.name, p.price, p.image,
                       co.name as color_name
                FROM products p
                JOIN product_variants pv ON p.id = pv.product_id AND pv.color_id = ?
                JOIN colors co ON pv.color_id = co.id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->execute([$color_id, $product_id]);
        }

        $item_data = $stmt->fetch();

        if ($item_data) {
            $item_total = $item_data['price'] * $qty;
            $cart_items[] = [
                'variant_key' => $variant_key,
                'id' => $item_data['id'],
                'name' => $item_data['name'],
                'price' => $item_data['price'],
                'image' => $item_data['image'],
                'qty' => $qty,
                'color_name' => $item_data['color_name'],
                'size_name' => $item_data['size_name'] ?? '',
                'total' => $item_total
            ];
            $total += $item_total;
        }
    }
}

$isAuth = isset($_SESSION['user_id']);
?>

<main class="container cart-page">
    <h2 style="text-align: center; margin: 40px 0;">Ваша корзина</h2>

    <?php if (!empty($error_messages)): ?>
        <div style="background: #ffcccc; color: #d32f2f; padding: 12px; border-radius: 4px; text-align: center; margin-bottom: 20px;">
            <?php foreach ($error_messages as $msg): ?>
                <?= $msg ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; text-align: center; margin-bottom: 20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p style="text-align: center; font-size: 18px; color: #666;">
            Корзина пуста. <a href="catalog.php" style="color: #000; text-decoration: underline;">Перейти в каталог</a>
        </p>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="update" value="1">
            <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 40px;">
                <?php foreach ($cart_items as $item): ?>
                    <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; display: flex; gap: 20px; padding: 16px;">
                        <img src="images/<?= htmlspecialchars($item['image']) ?>" 
     alt="<?= htmlspecialchars($item['name']) ?>" 
     style="width: 100px; height: 120px; object-fit: scale-down; background: #fff; border-radius: 4px;">

                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="font-size: 16px; margin: 0 0 8px 0;"><?= htmlspecialchars($item['name']) ?></h3>
                                <div style="margin: 8px 0; font-size: 14px; color: #666;">
                                    ЦВЕТ: <?= htmlspecialchars($item['color_name']) ?><br>
                                    РАЗМЕР: <?= htmlspecialchars($item['size_name']) ?>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                                <button type="button" onclick="changeQty('<?= htmlspecialchars($item['variant_key']) ?>', -1)" style="padding: 4px 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">—</button>
                                <input type="number" name="qty[<?= htmlspecialchars($item['variant_key']) ?>]" 
       value="<?= $item['qty'] ?>" 
       min="1" max="100" 
       style="width: 60px;  text-align: center; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; padding: 0;"
       onchange="this.form.submit()">
                                <button type="button" onclick="changeQty('<?= htmlspecialchars($item['variant_key']) ?>', 1)" style="padding: 4px 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">+</button>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; justify-content: space-between; text-align: right;">
                            <div style="font-size: 16px; font-weight: 700; color: #000;">
                                <?= number_format($item['total'], 0, '', ' ') ?> ₽
                            </div>
                            <a href="cart.php?remove=<?= urlencode($item['variant_key']) ?>" 
                               style="color: #ff4d4d; text-decoration: none; font-size: 14px;">Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="font-size: 20px; font-weight: 700;">
                    Итого: <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit" class="btn" style="margin-right: 15px;">Обновить корзину</button>
                    <?php if ($isAuth): ?>
                        <a href="checkout.php" class="btn" style="background: #28a745;">Оформить заказ</a>
                    <?php else: ?>
                        <a href="login.php" class="btn">Войдите, чтобы оформить заказ</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>

<script>
function changeQty(key, delta) {
    const input = document.querySelector(`input[name="qty[${key}]"]`);
    if (input) {
        let qty = parseInt(input.value) + delta;
        if (qty < 1) qty = 1;
        input.value = qty;
        input.form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>