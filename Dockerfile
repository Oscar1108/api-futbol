# Dockerfile para MySQL 8 + importación de base de datos
FROM mysql:8.0

# Variables de entorno para crear la base y usuario
ENV MYSQL_DATABASE=torneo_futbol
ENV MYSQL_USER=appuser
ENV MYSQL_PASSWORD=app123
ENV MYSQL_ROOT_PASSWORD=root123

# Copia el script SQL al contenedor (debe estar en la raiz del repo)
COPY torneo_futbol.sql /docker-entrypoint-initdb.d/

EXPOSE 3306
