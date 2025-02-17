# Используем образ PHP с Apache
FROM php:8.2-apache

# Устанавливаем необходимые PHP расширения
RUN apt-get update && apt-get install -y unzip libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql curl

# Копируем проект в контейнер
COPY . /var/www/html/

# Устанавливаем Composer и зависимости
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Открываем порт 80
EXPOSE 80
