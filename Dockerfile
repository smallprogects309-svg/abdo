FROM php:8.2-apache

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y libzip-dev zip git libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql

# تفعيل الـ Rewrite
RUN a2enmod rewrite

# تغيير المسار لـ public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# نسخ الملفات
COPY . /var/www/html

# تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# تثبيت المكتبات
RUN composer install --no-dev --optimize-autoloader

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# *** هذا هو السطر الجديد الذي سينفذ المايجريشن عند كل Build ***
RUN php /var/www/html/artisan migrate --force

EXPOSE 80
