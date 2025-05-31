FROM php:8.1-apache

# Instala dependencias y Composer
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Habilita mod_rewrite
RUN a2enmod rewrite

# Copia el proyecto
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    composer install

# Configura virtualhost para Slim (pública solo /public)
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>" > /etc/apache2/conf-available/slim.conf && \
    a2enconf slim

# 🔧 Establece el directorio público como raíz del servidor
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
