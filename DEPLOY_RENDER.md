# Desplegar la API de categorías en Render

Esta guía deja el proyecto listo para publicarse en [Render](https://render.com).
La API es PHP + MySQL, así que hay dos consideraciones importantes:

1. **Render no ejecuta PHP de forma nativa.** Se despliega con Docker, usando el
   `Dockerfile` incluido en el repositorio.
2. **Render no ofrece MySQL gestionado.** Hay que usar una base MySQL externa o
   crear un servicio MySQL privado en Render con disco persistente.

---

## 1. Archivos agregados

| Archivo | Para qué sirve |
| --- | --- |
| `Dockerfile` | Imagen PHP 8.4 + Apache con la extensión `pdo_mysql`. |
| `docker/entrypoint.sh` | Hace que Apache escuche en el puerto que Render asigna (`$PORT`). |
| `docker/php.ini` | Configuración de PHP para producción (errores solo en el log). |
| `render.yaml` | Blueprint: crea el servicio web automáticamente. |
| `configuracion/datos_conexion.php` | Lee la conexión desde variables de entorno (local sigue funcionando igual). |
| `configuracion/conexion.php` | Conexión PDO con soporte opcional para TLS. |
| `scripts/init_db.php` | Crea las tablas y los datos iniciales en la base configurada. |
| `index.php` | Página informativa y health check (`/`). |
| `salud.php` | Diagnóstico: prueba MySQL y avisa si faltan tablas. |
| `docker-compose.yml` | Entorno local con MySQL + API para probar antes de desplegar. |
| `GUIA_PRUEBAS.md` | Guía de pruebas manuales y con Postman. |
| `pruebas/API_Categorias.postman_collection.json` | Colección de Postman con 17 requests y aserciones automáticas. |

Los archivos `.env.example` y `DEPLOY_RENDER.md` son solo documentación: PHP no
carga `.env` automáticamente. En Render las variables se configuran en el panel
del servicio.

---

## 2. Crear la base de datos MySQL

Elija una de estas opciones.

### Opción A: TiDB Cloud Starter (gratis y compatible con MySQL)

Es la opción gratuita más sencilla en 2026. TiDB Cloud Starter ofrece 5 GiB y
50 millones de RU al mes por instancia, con hasta 5 instancias gratis.

1. Cree una cuenta en <https://tidbcloud.com> y una instancia **Starter**
   (modo compatible con MySQL).
2. Abra **Connect** y copie el host, puerto (`4000`), usuario y contraseña.
3. En el editor SQL de TiDB ejecute:

   ```sql
   CREATE DATABASE IF NOT EXISTS ventas;
   ```

   (También puede usar la base `test` que ya existe, cambiando `DB_NAME`.)
4. TiDB exige TLS. Use `DB_SSL_VERIFICAR=false` si no va a cargar el certificado
   CA, o descargue el CA y péguelo completo en `DB_SSL_CA`.

> TiDB es compatible con el protocolo MySQL y con las consultas de este
> proyecto (`CREATE TABLE`, `INSERT IGNORE`, `SHOW TABLES`, etc.).

### Opción B: MySQL privado dentro de Render (de pago)

Es la opción más cómoda si quiere todo en Render y sin exponer la base a
Internet. Requiere plan pago para el servicio privado y el disco.

1. Use la plantilla oficial: <https://render.com/templates/mysql>.
2. Configure:
   - `MYSQL_DATABASE=ventas`
   - `MYSQL_USER=api_categorias`
   - `MYSQL_PASSWORD=<una contraseña segura>`
   - `MYSQL_ROOT_PASSWORD=<otra contraseña segura>`
3. Adjunte un disco persistente en `/var/lib/mysql`.
4. Copie el hostname interno del servicio (por ejemplo `mysql-xxxx:3306`).
   `DB_HOST` es solo el hostname; `DB_PORT` es `3306`.
5. Como la conexión va por la red privada de Render, no necesita TLS.

### Opción C: Otro proveedor MySQL

Cualquier MySQL accesible por Internet sirve (Aiven Developer de USD 5/mes,
Railway, su propio servidor, etc.). Solo asegúrese de que Render pueda conectarse
y de conocer host, puerto, base, usuario y contraseña.

> El plan gratuito de MySQL de Aiven dejó de aceptar servicios nuevos el
> 6 de noviembre de 2026.

---

## 3. Subir los cambios a GitHub

```bash
git add .
git commit -m "Preparar despliegue en Render"
git push origin master
```

Si su rama principal se llama `main`, use `git push origin main`.

---

## 4. Desplegar en Render

### Recomendado: Blueprint (`render.yaml`)

1. Entre a <https://dashboard.render.com> → **New** → **Blueprint**.
2. Conecte el repositorio `Isaac-chaves/api-categoria-`.
3. Render leerá `render.yaml` y pedirá las variables marcadas con `sync: false`:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASSWORD`
4. Complete con los datos de su base y aplique los cambios.

### Manual: crear el Web Service

1. **New** → **Web Service** → conecte el repositorio.
2. En **Language** elija **Docker**.
3. **Dockerfile Path**: `./Dockerfile`.
4. **Health Check Path**: `/`.
5. En **Environment** agregue las variables de la siguiente sección.
6. **Create Web Service**.

### Variables de entorno

| Variable | Ejemplo | Obligatoria |
| --- | --- | --- |
| `DB_HOST` | `gateway01.us-east-1.prod.aws.tidbcloud.com` | Sí |
| `DB_PORT` | `4000` (TiDB) o `3306` (MySQL) | Sí |
| `DB_NAME` | `ventas` | Sí |
| `DB_USER` | `xxxxxxxx.root` | Sí |
| `DB_PASSWORD` | `********` | Sí |
| `AUTO_INIT_DB` | `true` | Recomendada |
| `DB_SSL_CA` | Contenido PEM del certificado CA | Solo si el proveedor lo exige |
| `DB_SSL_VERIFICAR` | `false` | Solo si no verifica el certificado |
| `DATABASE_URL` | `mysql://usuario:clave@host:3306/ventas` | Alternativa a las `DB_*` |

`DATABASE_URL` tiene prioridad si las variables individuales no están definidas.
`AUTO_INIT_DB=true` ejecuta `scripts/init_db.php` en cada arranque; es seguro
porque usa `CREATE TABLE IF NOT EXISTS` e `INSERT IGNORE`.

Si prefiere inicializar manualmente, abra la **Shell** del servicio en Render y
ejecute:

```bash
php scripts/init_db.php
```

---

## 5. Verificar el despliegue

Reemplace `TU-SERVICIO` por el nombre que le dio Render.

```bash
# Información general (health check)
curl https://TU-SERVICIO.onrender.com/

# Diagnóstico de la base de datos
curl https://TU-SERVICIO.onrender.com/salud.php

# Listar categorías (requiere los encabezados de autenticación)
curl https://TU-SERVICIO.onrender.com/controlador/categoria.php \
  -H "X-Cedula: 1" \
  -H "X-Contrasena: 01"

# Consultar una categoría
curl "https://TU-SERVICIO.onrender.com/controlador/categoria.php?codigo=1" \
  -H "X-Cedula: 1" \
  -H "X-Contrasena: 01"
```

En Postman:

- **URL base**: `https://TU-SERVICIO.onrender.com/controlador/categoria.php`
- **Headers** en todos los métodos:
  - `X-Cedula: 1`
  - `X-Contrasena: 01`

`/salud.php` debe responder `{"estado":"ok", ...}`. Si responde `503` con
`tablas_faltantes`, ejecute la inicialización manual o active `AUTO_INIT_DB`.

---

## 6. Probar en local con Docker

Si tiene Docker instalado, puede probar todo el conjunto antes de subir:

```bash
docker compose up --build
```

- API: <http://localhost:18080/>
- Diagnóstico: <http://localhost:18080/salud.php>
- MySQL: `localhost:13307` (usuario `root`, contraseña `root`)

Para detenerlo:

```bash
docker compose down
```

Para borrar también los datos locales:

```bash
docker compose down -v
```

---

## 7. Solución de problemas

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| El deploy falla al construir | Error de sintaxis o dependencia | Revise los logs de Build en Render. |
| `500` al llamar a la API | No conecta a MySQL | Abra `/salud.php` y revise las variables `DB_*` y los logs. |
| `401 Autorizacion Invalida` | Faltan o son incorrectos los encabezados | Envíe `X-Cedula` y `X-Contrasena`. |
| `503` con `tablas_faltantes` | La base existe pero no está inicializada | Ejecute `php scripts/init_db.php` o use `AUTO_INIT_DB=true`. |
| Error de TLS / certificado | El proveedor exige TLS | Cargue `DB_SSL_CA` con el PEM o use `DB_SSL_VERIFICAR=false`. |
| `Connection refused` a un MySQL privado | Servicios en regiones distintas o host incorrecto | Use el hostname interno y la misma región de Render. |
| El servicio tarda en responder | Plan free de Render | El servicio se duerme tras 15 minutos sin uso; la primera petición puede tardar ~1 minuto. |

---

## 8. Notas importantes

- **Contraseñas en texto plano**: el proyecto es didáctico. En un sistema real
  use `password_hash()` / `password_verify()` y nunca guarde contraseñas así.
- **No suba secretos al repositorio**: las credenciales van en las variables de
  entorno de Render.
- **Plan free de Render**: 512 MB de RAM, el servicio se suspende por inactividad
  y no está pensado para producción.
- **HTTPS**: Render lo configura automáticamente; use siempre la URL `https://`.
- **Cambie las credenciales de prueba** (`1/01`, `2/02`, `3/03`) antes de usar
  la API con datos reales.
