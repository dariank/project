<?php
require_once 'header.php';

$message_sent = false;

// Обработка формы обратной связи
if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Сохраняем сообщение в файл (можно позже в БД)
        $log = "Имя: $name\nEmail: $email\nСообщение: $message\n---\n";
        file_put_contents('messages.txt', $log, FILE_APPEND | LOCK_EX);
        $message_sent = true;
    }
}
?>

<main class="container" style="margin: 50px auto; max-width: 800px;">
    <h2 style="text-align: center; margin-bottom: 40px;">Контакты</h2>

    <?php if ($message_sent): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 30px;">
            Спасибо! Ваше сообщение отправлено.
        </div>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 40px;">
        <!-- Контактная информация -->
        <div style="flex: 1; min-width: 300px;">
            <h3>Наш адрес</h3>
            <p>г. Нижний Новгород, ул. Студенческая, д. 6</p>
            <p>Метро: Горьковская </p>

            <h3 style="margin-top: 25px;">Телефон</h3>
            <p>+7 (495) 123-45-67</p>
            <p style="color: #666; font-size: 14px;">Пн–Вс: 10:00–22:00</p>

            <h3 style="margin-top: 25px;">Email</h3>
            <p>info@couturely.ru</p>

            <h3 style="margin-top: 25px;">Мы в соцсетях</h3>
            <p>
                <a href="#" style="margin-right: 15px; color: #000; text-decoration: none;">Instagram</a>
                <a href="#" style="margin-right: 15px; color: #000; text-decoration: none;">VK</a>
                <a href="#" style="color: #000; text-decoration: none;">Telegram</a>
            </p>
        </div>

        <!-- Карта (заглушка изображения) -->
        <div style="flex: 1; min-width: 300px;">
            <h3>Как нас найти</h3>
          <div style="margin-bottom: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
    <iframe 
       src="https://yandex.ru/map-widget/v1/?ll=43.988254%2C56.302612&mode=search&ol=biz&z=17" 
        width="100%" 
        height="250" 
        frameborder="0" 
        style="border: none; border-radius: 8px;" 
        allowfullscreen="" 
        loading="lazy">
    </iframe>
</div>
            <p style="font-size: 14px; color: #666;">Откройте в Яндекс.Картах:  
                <a href="https://yandex.ru/maps/?text=Нижний Новгород, Студенческая 6" target="_blank" style="color: #000;">Нижний Новгород, Студенческая 6</a>
            </p>
        </div>
    </div>

    <!-- Форма обратной связи -->
    <div style="margin-top: 60px;">
        <h3 style="text-align: center; margin-bottom: 20px;">Напишите нам</h3>
        <form method="POST" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <label for="name">Имя</label>
                <input type="text" id="name" name="name" required 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="message">Сообщение</label>
                <textarea id="message" name="message" rows="5" required
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            <button type="submit" class="btn" style="align-self: flex-start;">Отправить</button>
        </form>
    </div>
</main>

<?php require_once 'footer.php'; ?>