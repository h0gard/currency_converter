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
    <!-- Подключаем Font Awesome для иконки стрелочки -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Убираем рамку у выбранной кнопки, если применён класс no-border */
        .currency-buttons button.no-border {
            border: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Конвертер валют</h1>
    <div class="converter-block">

        <!-- Левый блок -->
        <div class="mini-block">
            <h2>У меня есть</h2>
            <div class="currency-buttons">
                <button data-currency="USD" class="active">USD</button>
                <button data-currency="EUR">EUR</button>
                <button data-currency="KZT">KZT</button>
                <!-- Предпоследняя кнопка, по умолчанию RUB -->
                <button data-currency="RUB">RUB</button>
                <!-- Кнопка-тогглер (стрелочка) -->
                <button class="dropdown-toggle"><i class="fa fa-chevron-down"></i></button>
            </div>
            <!-- Выпадающее окно для выбора валют -->
            <div class="dropdown-menu"></div>
            <div class="input-wrapper">
                <div class="currency-label">Американский доллар</div>
                <input type="number" class="currency-input" placeholder="0.00">
            </div>
            <div class="exchange-rate">Курс: ...</div>
        </div>

        <!-- Правый блок -->
        <div class="mini-block">
            <h2>Хочу приобрести</h2>
            <div class="currency-buttons">
                <button data-currency="USD">USD</button>
                <button data-currency="EUR" class="active">EUR</button>
                <button data-currency="KZT">KZT</button>
                <!-- Предпоследняя кнопка, по умолчанию RUB -->
                <button data-currency="RUB">RUB</button>
                <!-- Кнопка-тогглер -->
                <button class="dropdown-toggle"><i class="fa fa-chevron-down"></i></button>
            </div>
            <div class="dropdown-menu"></div>
            <div class="input-wrapper">
                <div class="currency-label">Евро</div>
                <input type="number" class="currency-input" placeholder="0.00" readonly>
            </div>
            <div class="exchange-rate">Курс: ...</div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Загружаем данные курсов и названий валют
        var ratesData = <?php echo file_get_contents('cache.json'); ?>;
        var symbolsData = <?php echo file_get_contents('cache_symbols.json'); ?>;

        // Получаем mini-блоки
        var blocks = document.querySelectorAll('.mini-block');

        // Инициализация валют из активных фиксированных кнопок
        var leftCurrency = blocks[0].querySelector('.currency-buttons button.active').getAttribute('data-currency');
        var rightCurrency = blocks[1].querySelector('.currency-buttons button.active').getAttribute('data-currency');

        function updateCurrencyLabel(block, currencyCode) {
            var labelElem = block.querySelector('.currency-label');
            labelElem.textContent = symbolsData.symbols && symbolsData.symbols[currencyCode]
                ? symbolsData.symbols[currencyCode]
                : currencyCode;
        }

        function updateExchangeRateDisplay() {
            if (ratesData.rates[leftCurrency] && ratesData.rates[rightCurrency]) {
                var rateSource = ratesData.rates[leftCurrency];
                var rateTarget = ratesData.rates[rightCurrency];
                var conversionRate = rateTarget / rateSource;
                blocks[0].querySelector('.exchange-rate').textContent =
                    "Курс: 1 " + leftCurrency + " = " + conversionRate.toFixed(4) + " " + rightCurrency;
                blocks[1].querySelector('.exchange-rate').textContent =
                    "Курс: 1 " + rightCurrency + " = " + (1 / conversionRate).toFixed(4) + " " + leftCurrency;
            }
        }

        function recalcConversion() {
            var inputElem = blocks[0].querySelector('.currency-input');
            var amount = parseFloat(inputElem.value) || 0;
            if (ratesData.rates[leftCurrency] && ratesData.rates[rightCurrency]) {
                var conversionRate = ratesData.rates[rightCurrency] / ratesData.rates[leftCurrency];
                var converted = amount * conversionRate;
                blocks[1].querySelector('.currency-input').value = converted.toFixed(2);
            }
        }

        // Обработчики для фиксированных кнопок (без dropdown-toggle)
        blocks.forEach(function(block, index) {
            block.querySelectorAll('.currency-buttons button:not(.dropdown-toggle)').forEach(function(button) {
                button.addEventListener('click', function() {
                    block.querySelectorAll('.currency-buttons button:not(.dropdown-toggle)').forEach(function(btn) {
                        btn.classList.remove('active', 'no-border');
                    });
                    button.classList.add('active');
                    var currency = button.getAttribute('data-currency');
                    if (index === 0) {
                        leftCurrency = currency;
                    } else {
                        rightCurrency = currency;
                    }
                    updateCurrencyLabel(block, currency);
                    updateExchangeRateDisplay();
                    recalcConversion();
                });
            });
        });

        // Обработка dropdown для каждого mini-block
        blocks.forEach(function(block, index) {
            var dropdownToggle = block.querySelector('.dropdown-toggle');
            var dropdownMenu = block.querySelector('.dropdown-menu');

            // Заполнение меню валютами
            for (var code in symbolsData.symbols) {
                var btn = document.createElement('button');
                btn.setAttribute('data-currency', code);
                btn.innerHTML = "<strong>" + code + "</strong> " + symbolsData.symbols[code];
                dropdownMenu.appendChild(btn);
            }

            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                // Если меню уже открыто, закрываем его
                if (dropdownMenu.classList.contains('open')) {
                    dropdownMenu.classList.remove('open');
                    return;
                }
                closeAllDropdowns();
                dropdownMenu.classList.add('open');

                // Вертикальное позиционирование: сразу под блоком с кнопками + отступ 5px
                var currencyButtons = block.querySelector('.currency-buttons');
                var topOffset = currencyButtons.offsetTop + currencyButtons.offsetHeight + 5;
                dropdownMenu.style.top = topOffset + "px";

                // Горизонтальное позиционирование:
                // Левая граница меню = левая граница первого mini-block,
                // Ширина меню = расстояние от левого края первого до правого края второго mini-block с вычетом 40px
                var leftBlockRect = blocks[0].getBoundingClientRect();
                var rightBlockRect = blocks[1].getBoundingClientRect();
                var currentBlockRect = block.getBoundingClientRect();
                var desiredLeft = leftBlockRect.left - currentBlockRect.left;
                var desiredWidth = rightBlockRect.right - leftBlockRect.left - 40;
                dropdownMenu.style.left = desiredLeft + "px";
                dropdownMenu.style.width = desiredWidth + "px";
            });

            // Обработка выбора валюты из меню
            dropdownMenu.querySelectorAll('button').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var selectedCurrency = btn.getAttribute('data-currency');
                    // Обновляем предпоследнюю кнопку (с выбором валюты)
                    var currencyBtn = block.querySelector('.currency-buttons button:nth-last-child(2)');
                    currencyBtn.innerHTML = selectedCurrency;
                    currencyBtn.setAttribute('data-currency', selectedCurrency);
                    currencyBtn.classList.add('no-border');

                    block.querySelectorAll('.currency-buttons button:not(.dropdown-toggle)').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    currencyBtn.classList.add('active');

                    if (index === 0) {
                        leftCurrency = selectedCurrency;
                    } else {
                        rightCurrency = selectedCurrency;
                    }
                    updateCurrencyLabel(block, selectedCurrency);
                    updateExchangeRateDisplay();
                    recalcConversion();

                    dropdownMenu.classList.remove('open');
                });
            });
        });

        // Функция для закрытия всех открытых меню
        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu.open').forEach(function(menu) {
                menu.classList.remove('open');
            });
        }

        // Закрытие dropdown при клике вне его
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-toggle')) {
                closeAllDropdowns();
            }
        });

        blocks[0].querySelector('.currency-input').addEventListener('input', function() {
            recalcConversion();
        });

        updateCurrencyLabel(blocks[0], leftCurrency);
        updateCurrencyLabel(blocks[1], rightCurrency);
        updateExchangeRateDisplay();
    });
</script>

</body>
</html>
