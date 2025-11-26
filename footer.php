<?php
// footer.php
?>
<footer class="footer" style="background: #f5f5f5; padding: 60px 0 30px; font-size: 14px; color: #666;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

            <!-- Левая часть: Подписка + Соцсети -->
            <div>
                <h4 style="font-size: 16px; margin: 0 0 20px 0;">Распродажи, акции и специальные промокоды на скидку в наших рассылках. Подписывайтесь!</h4>

                <form method="POST" style="margin-top: 20px; display: flex; gap: 10px;">
                    <input type="email" name="email" placeholder="Ваш email" style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px;" required>
                    <button type="submit" class="btn" style="padding: 12px 20px; font-size: 14px; background: #000; color: #fff; border: none; border-radius: 4px;">ПОДПИСАТЬСЯ</button>
                </form>

                <p style="font-size: 12px; color: #666; margin-top: 15px; line-height: 1.4;">
                    Нажимая на кнопку «Подписаться», вы соглашаетесь с <a href="#" style="color: #000; text-decoration: underline;">Пользовательским соглашением</a>, <a href="#" style="color: #000; text-decoration: underline;">условиями Политики конфиденциальности и обработки персональных данных</a>, а также даёте согласие на обработку ваших персональных данных
                </p>

                <!-- "Следите за нами" на одном уровне с заголовком -->
                <div style="margin-top: 30px; display: flex; align-items: center; gap: 15px;">
                    <h4 style="font-size: 16px; margin: 0;">Следите за нами</h4>
                    <div style="display: flex; gap: 15px;">
                        <!-- VK -->
                        <a href="#" style="width: 40px; height: 40px; background: #4C75A3; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; font-size: 20px;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <!-- Одноклассники -->
                        <a href="#" style="width: 40px; height: 40px; background: #FA7E00; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; font-size: 20px;">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <!-- Telegram -->
                        <a href="#" style="width: 40px; height: 40px; background: #0088cc; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; font-size: 20px;">
                            <i class="bi bi-telegram"></i>
                        </a>
                        <!-- Instagram -->
                        <a href="#" style="width: 40px; height: 40px; background: #E1306C; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; font-size: 20px;">
                            <i class="bi bi-instagram"></i>
                        </a>
                        
                    </div>
                </div>
            </div>

            <!-- Правая часть: Ссылки -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">

                <!-- Колонка 1: О компании -->
                <div>
                    <h4 style="font-size: 16px; margin: 0 0 20px 0;">О компании</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="#" style="color: #666; text-decoration: none;">О бренде</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Сотрудничество</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Аренда</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Закупки</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Партнерская программа</a></li>
                    </ul>
                </div>

                <!-- Колонка 2: События -->
                <div>
                    <h4 style="font-size: 16px; margin: 0 0 20px 0;">События</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li><a href="#" style="color: #666; text-decoration: none;">Новости и акции</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Блог</a></li>
                        <li><a href="#" style="color: #666; text-decoration: none;">Лукбук</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Копирайт -->
        <div style="border-top: 1px solid #ddd; padding: 30px 0 20px; font-size: 14px; color: #666; text-align: center;">
            © 2025 COUTERULY. Все права защищены.
        </div>
    </div>
</footer>