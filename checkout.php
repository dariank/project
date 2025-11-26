<?php
session_start();
require_once 'db.php';

// Только для авторизованных
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Проверяем, есть ли товары в корзине
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = '';

// Получаем товары из корзины (новая структура)
$cart_items = [];
$total = 0;

foreach ($_SESSION['cart'] as $variant_key => $qty) {
    $parts = explode('_', $variant_key);
    if (count($parts) !== 3) continue;
    [$product_id, $color_id, $size_id] = array_map('intval', $parts);
    if (!$product_id) continue;

    // Получаем цену и остаток
    $stmt = $pdo->prepare("SELECT id, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $qty <= $product['stock']) {
        $cart_items[] = [
            'product_id' => $product_id,
            'quantity' => $qty,
            'price' => $product['price']
        ];
        $total += $product['price'] * $qty;
    } else {
        $error = 'Некоторые товары недоступны или закончились на складе.';
        break;
    }
}

if (!$error && empty($cart_items)) {
    $error = 'Корзина пуста или товары недоступны.';
}

if (!$error) {
    try {
        $pdo->beginTransaction();

        // 1. Создаём заказ
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $stmt->execute([$user_id, $total]);
        $order_id = $pdo->lastInsertId();

        // 2. Добавляем позиции заказа
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        // 3. Уменьшаем остатки на складе
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // 4. Очищаем корзину
        unset($_SESSION['cart']);

        $pdo->commit();
        $success = true;

    } catch (Exception $e) {
        $pdo->rollback();
        $error = 'Ошибка при оформлении заказа. Попробуйте позже.';
    }
}
?>

<?php require_once 'header.php'; ?>

<main class="container" style="text-align: center; margin: 80px auto; max-width: 600px;">
    <?php if ($success): ?>
        <h2 style="color: #28a745;">✅ Заказ оформлен!</h2>
        <p style="font-size: 18px; margin: 20px 0;">Спасибо за покупку в Couteruly!</p>
        <p>Ваш заказ №<?= $order_id ?> находится в статусе <strong>«Обрабатывается»</strong>.</p>
        <p>Скоро с вами свяжется менеджер.</p>
        <div style="margin-top: 30px;">
            <a href="catalog.php" class="btn">Продолжить покупки</a>
            <a href="profile.php" style="display: block; margin-top: 15px; color: #000; text-decoration: underline;">
                Перейти в личный кабинет → Мои заказы
            </a>
        </div>
    <?php else: ?>
        <h2>Ошибка оформления</h2>
        <p style="color: #d32f2f; margin: 20px 0;"><?= htmlspecialchars($error) ?></p>
        <a href="cart.php" class="btn">Вернуться в корзину</a>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>