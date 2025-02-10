<?php
require_once 'api.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конвертер валют</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- Подключение Font Awesome, если нужно -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Конвертер валют</h1>
    <div class="converter-block">
        <!-- Левый блок (У меня есть) -->
        <div class="mini-block">
            <h2>У меня есть</h2>
            <div class="currency-buttons">
                <button data-currency="USD" class="active">USD</button>
                <button data-currency="EUR">EUR</button>
                <button data-currency="KZT">KZT</button>
                <button data-currency="RUB">RUB</button>
            </div>
            <div class="input-wrapper">
                <!-- Изначально можно задать название валюты, потом обновится -->
                <div class="currency-label">Американский доллар</div>
                <input type="text" class="currency-input" placeholder="0.00">
            </div>
            <div class="exchange-rate">Курс: ...</div>
        </div>

        <!-- Правый блок (Хочу приобрести) -->
        <div class="mini-block">
            <h2>Хочу приобрести</h2>
            <div class="currency-buttons">
                <button data-currency="USD">USD</button>
                <button data-currency="EUR" class="active">EUR</button>
                <button data-currency="KZT">KZT</button>
                <button data-currency="RUB">RUB</button>
            </div>
            <div class="input-wrapper">
                <div class="currency-label">Евро</div>
                <input type="text" class="currency-input" placeholder="0.00" readonly>
            </div>
            <div class="exchange-rate">Курс: ...</div>
        </div>
    </div>
</div>

<!-- Скрипт для обработки выбора валют и пересчёта -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Подгружаем данные курсов и названий валют (встраиваем JSON напрямую через PHP)
        var ratesData = <?php echo file_get_contents('cache.json'); ?>;
        var symbolsData = <?php echo file_get_contents('cache_symbols.json'); ?>;

        // Получаем оба блока
        var blocks = document.querySelectorAll('.mini-block');
        var leftBlock = blocks[0];   // блок "У меня есть"
        var rightBlock = blocks[1];  // блок "Хочу приобрести"

        // Определяем исходные валюты из активных кнопок
        var leftCurrency = leftBlock.querySelector('.currency-buttons button.active').getAttribute('data-currency');
        var rightCurrency = rightBlock.querySelector('.currency-buttons button.active').getAttribute('data-currency');

        // Функция для обновления полного названия валюты
        function updateCurrencyLabel(block, currencyCode) {
            var labelElem = block.querySelector('.currency-label');
            if (symbolsData.symbols && symbolsData.symbols[currencyCode]) {
                labelElem.textContent = symbolsData.symbols[currencyCode];
            } else {
                labelElem.textContent = currencyCode;
            }
        }

        // Функция для обновления отображения курса
        function updateExchangeRateDisplay() {
            if (ratesData.rates[leftCurrency] && ratesData.rates[rightCurrency]) {
                var rateSource = ratesData.rates[leftCurrency];
                var rateTarget = ratesData.rates[rightCurrency];
                var conversionRate = rateTarget / rateSource;
                // Обновляем текст в обоих блоках (по желанию можно оставить только в одном)
                leftBlock.querySelector('.exchange-rate').textContent =
                    "Курс: 1 " + leftCurrency + " = " + conversionRate.toFixed(4) + " " + rightCurrency;
                rightBlock.querySelector('.exchange-rate').textContent =
                    "Курс: 1 " + rightCurrency + " = " + (1 / conversionRate).toFixed(4) + " " + leftCurrency;
            }
        }

        // Функция для пересчёта суммы
        function recalcConversion() {
            var inputElem = leftBlock.querySelector('.currency-input');
            var amount = parseFloat(inputElem.value) || 0;
            if (ratesData.rates[leftCurrency] && ratesData.rates[rightCurrency]) {
                var conversionRate = ratesData.rates[rightCurrency] / ratesData.rates[leftCurrency];
                var converted = amount * conversionRate;
                rightBlock.querySelector('.currency-input').value = converted.toFixed(2);
            }
        }

        // Обработчик клика для кнопок в блоке "У меня есть"
        leftBlock.querySelectorAll('.currency-buttons button').forEach(function(button) {
            button.addEventListener('click', function() {
                // Убираем класс active у всех кнопок блока и задаём активной нажатую
                leftBlock.querySelectorAll('.currency-buttons button').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                leftCurrency = button.getAttribute('data-currency');
                updateCurrencyLabel(leftBlock, leftCurrency);
                updateExchangeRateDisplay();
                recalcConversion();
            });
        });

        // Обработчик клика для кнопок в блоке "Хочу приобрести"
        rightBlock.querySelectorAll('.currency-buttons button').forEach(function(button) {
            button.addEventListener('click', function() {
                rightBlock.querySelectorAll('.currency-buttons button').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                rightCurrency = button.getAttribute('data-currency');
                updateCurrencyLabel(rightBlock, rightCurrency);
                updateExchangeRateDisplay();
                recalcConversion();
            });
        });

        // Обработчик события ввода в левом блоке для динамического пересчёта
        leftBlock.querySelector('.currency-input').addEventListener('input', function() {
            recalcConversion();
        });

        // Инициализируем метки и курс при загрузке страницы
        updateCurrencyLabel(leftBlock, leftCurrency);
        updateCurrencyLabel(rightBlock, rightCurrency);
        updateExchangeRateDisplay();
    });
</script>
</body>
</html>
