# Imagen oficial de PHP con Apache.
# Render no tiene un runtime nativo de PHP: se despliega con Docker.
FROM php:8.4-apache

# Extension PDO para MySQL (la API usa PDO) y mod_rewrite para .htaccess.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Configuracion de PHP para produccion (errores al log, no al navegador).
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Script de arranque: escucha en el puerto que Render asigna en $PORT.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar la aplicacion completa al directorio publico de Apache.
COPY . /var/www/html

WORKDIR /var/www/html

# Render inyecta PORT (10000 por defecto); se expone como referencia.
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
