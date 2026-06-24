FROM php:8.2-apache

# 1. تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y libzip-dev zip git \
    && docker-php-ext-install zip pdo pdo_mysql

# 2. تفعيل الـ Rewrite module الخاص بـ Apache (عشان الـ API يشتغل)
RUN a2enmod rewrite

# 3. تغيير مسار الـ Document Root إلى مجلد الـ public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 4. نسخ ملفات المشروع
COPY . /var/www/html

# 5. تثبيت Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 6. تثبيت المكتبات (بدون dev عشان السرعة)
RUN composer install --working-dir=/var/www/html --no-dev --optimize-autoloader

# 7. ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80