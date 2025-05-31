FROM php:8.1-apache

# Instala extensiones necesarias y Composer
RUN apt-get update && apt-get install -y \
    git zip unzip curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Habilita mod_rewrite
RUN a2enmod rewrite

# Copia el proyecto completo
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Ajusta el DocumentRoot de Apache a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Permite .htaccess en /public
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/slim.conf && \
    a2enconf slim

# Instala Composer y las dependencias
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer install --no-dev --optimize-autoloader

# Expone el puerto por defecto (opcional en Render)
EXPOSE 80
