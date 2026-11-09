#!/bin/sh
# Arranque del contenedor en Render.
# Render asigna un puerto dinamico en la variable PORT; Apache debe escuchar
# en ese puerto (no en el 80 predeterminado).
set -e

PUERTO="${PORT:-10000}"

# Ajustar el puerto de Apache.
sed -ri "s/^Listen 80$/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

# Evitar el aviso "Could not reliably determine the server's fully qualified
# domain name" en los logs.
if ! grep -q "^ServerName " /etc/apache2/apache2.conf; then
    echo "ServerName localhost" >> /etc/apache2/apache2.conf
fi

# Crear las tablas e insertar los datos iniciales en el primer arranque.
# Se activa con AUTO_INIT_DB=true en las variables de entorno de Render.
# Si la base aun no esta disponible, el servicio arranca igualmente y se
# puede revisar el diagnostico en /salud.php.
if [ "${AUTO_INIT_DB:-false}" = "true" ]; then
    php /var/www/html/scripts/init_db.php \
        || echo "Aviso: no se pudieron crear las tablas. Revise /salud.php"
fi

exec "$@"
