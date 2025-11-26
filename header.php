<?php
session_start();
require_once 'db.php';

$isAuth = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COUTERULY</title>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 0;">
            <!-- Логотип -->
            <div class="header__logo" style="margin-right: 20px;">
                <a href="index.php" style="font-size: 26px; font-weight: 700; text-decoration: none; color: #000; letter-spacing: -0.5px;">
                    COUTERULY
                </a>
            </div>

            <!-- Основное меню -->
            <div style="display: flex; align-items: center; gap: 30px; margin-right: auto;">
                <!-- Каталог -->
                <div class="header__catalog">
                    <a href="menu.php" style="text-decoration: none; color: #000; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <span>☰</span>
                        КАТАЛОГ
                    </a>
                </div>

                <!-- Быстрые ссылки -->
                <nav class="header__quick-links" style="display: flex; gap: 20px; align-items: center;">
                    <a href="sale.php" class="quick-link" style="color: #ff4d4d; text-decoration: none; font-weight: 500; font-size: 14px;">РАСПРОДАЖА</a>
                    <a href="new_collection.php" class="quick-link" style="text-decoration: none; font-weight: 500; font-size: 14px;">НОВАЯ КОЛЛЕКЦИЯ</a>
                    <a href="contacts.php" class="quick-link" style="text-decoration: none; font-weight: 500; font-size: 14px;">О НАС</a>
                </nav>
            </div>
           


            <!-- Иконки + Админ-панель -->
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="search.php"><i class="bi bi-search" style="font-size: 20px; color: #000;"></i></a>
                <a href="<?= $isAuth ? 'profile.php' : 'login.php' ?>"><i class="bi bi-person" style="font-size: 20px; color: #000;"></i></a>
                <a href="cart.php" style="position: relative;">
                    <i class="bi bi-bag" style="font-size: 20px; color: #000;"></i>
                    <?php if ($isAuth && !empty($_SESSION['cart'])): ?>
                        <span style="position: absolute; top: -8px; right: -8px; background: red; color: white; font-size: 10px; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <?= count($_SESSION['cart']) ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Админ-панель (только для ID=6) -->
                <?php if (($_SESSION['user_id'] ?? null) == 6): ?>
                    <a href="admin/index.php" style="color: #000; text-decoration: underline; font-weight: 600; font-size: 14px; white-space: nowrap;">
                        АДМИН-ПАНЕЛЬ
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
