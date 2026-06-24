FROM php:8.2-apache

# 1. تثبيت الحزم المطلوبة لـ PHP وللتعامل مع PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip

# 2. تفعيل mod_rewrite لأجل لارافل
RUN a2enmod rewrite

# 3. تغيير مسار الـ Document Root إلى public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 4. نسخ ملفات المشروع
COPY . /var/www/html

# 5. ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 6. تثبيت Composer وتثبيت مكتبات لارافل
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --working-dir=/var/www/html --no-dev --optimize-autoloader

EXPOSE 80
