# Guía de pruebas — API de Categorías

Esta guía sirve para comprobar el funcionamiento de la API tanto en local
(Docker) como en Render.

---

## 1. Antes de empezar

### URL base

| Entorno | URL base |
| --- | --- |
| Local con Docker Compose | `http://localhost:18080` |
| Render | `https://TU-SERVICIO.onrender.com` |

### Credenciales de prueba

| Cédula | Nombre | Contraseña |
| --- | --- | --- |
| `1` | Luis | `01` |
| `2` | Ana | `02` |
| `3` | Maria | `03` |

### Encabezados obligatorios

Todos los métodos (`GET`, `POST`, `PUT`, `DELETE`) necesitan:

```
X-Cedula: 1
X-Contrasena: 01
```

> El nombre de los encabezados debe ser exacto. Si falta uno o es incorrecto,
> la API responde `401`.

### Herramientas

- **Postman** (recomendado): importe
  `pruebas/API_Categorias.postman_collection.json`.
- **curl**: para pruebas rápidas desde terminal.
- **PowerShell**: ejemplos incluidos más abajo para Windows.

---

## 2. Probar en local con Docker

```bash
docker compose up -d --build
```

Espere unos segundos y compruebe el diagnóstico:

```bash
curl http://localhost:18080/salud.php
```

Debe responder:

```json
{
  "estado": "ok",
  "base_de_datos": "conectada",
  "base": "ventas"
}
```

Si responde `503`, revise los logs:

```bash
docker compose logs api
docker compose logs mysql
```

Para detener el entorno:

```bash
docker compose down
```

Para reiniciar también la base de datos (borra los datos de prueba):

```bash
docker compose down -v
docker compose up -d --build
```

---

## 3. Probar en Render

Después del despliegue:

```bash
curl https://TU-SERVICIO.onrender.com/
curl https://TU-SERVICIO.onrender.com/salud.php
```

Resultados esperados:

| Ruta | Código | Resultado |
| --- | --- | --- |
| `/` | `200` | JSON con la información de la API |
| `/salud.php` | `200` | `"estado": "ok"` |
| `/salud.php` | `503` | La base no responde o faltan tablas |

> En el plan gratuito el servicio se suspende tras 15 minutos sin uso. La
> primera petición puede tardar alrededor de un minuto.

---

## 4. Pruebas con Postman

1. Abra Postman → **Import** → seleccione
   `pruebas/API_Categorias.postman_collection.json`.
2. Abra la colección y edite sus variables:

   | Variable | Valor local | Valor en Render |
   | --- | --- | --- |
   | `baseUrl` | `http://localhost:18080` | `https://TU-SERVICIO.onrender.com` |
   | `cedula` | `1` | `1` |
   | `clave` | `01` | `01` |

3. Ejecute la colección completa con **Run** (Collection Runner).
4. Todas las pruebas deben quedar en verde.

La colección incluye aserciones automáticas de código HTTP y de contenido. El
primer request limpia la categoría de prueba (`codigo=5`) para que la colección
se pueda ejecutar varias veces sin errores de clave duplicada.

---

## 5. Pruebas manuales con curl

### Variables para bash / Linux / macOS / WSL

```bash
API="http://localhost:18080"
H=(-H "X-Cedula: 1" -H "X-Contrasena: 01")
JSON=(-H "Content-Type: application/json")
```

En Windows use `curl.exe` y ejecute cada comando en una sola línea, o use
Postman. Los ejemplos con `\` son para bash.

### Casos principales

```bash
# 1. Información general (sin credenciales)
curl -s "$API/"

# 2. Diagnóstico de la base
curl -s "$API/salud.php"

# 3. Listar todas las categorías
curl -s "$API/controlador/categoria.php" "${H[@]}"

# 4. Consultar la categoría 1
curl -s "$API/controlador/categoria.php?codigo=1" "${H[@]}"

# 5. Consultar una categoría que no existe (404)
curl -s -i "$API/controlador/categoria.php?codigo=999" "${H[@]}"

# 6. Sin credenciales (401)
curl -s -i "$API/controlador/categoria.php"

# 7. Credenciales incorrectas (401)
curl -s -i "$API/controlador/categoria.php" \
  -H "X-Cedula: 1" -H "X-Contrasena: incorrecta"

# 8. Crear la categoría 5 (201)
curl -s -i -X POST "$API/controlador/categoria.php" "${H[@]}" "${JSON[@]}" \
  -d '{"codigo":5,"nombre":"Monitores"}'

# 9. Verificar que se creó
curl -s "$API/controlador/categoria.php?codigo=5" "${H[@]}"

# 10. Actualizar la categoría 5 (200)
curl -s -i -X PUT "$API/controlador/categoria.php" "${H[@]}" "${JSON[@]}" \
  -d '{"codigo":5,"nombre":"Monitores LED"}'

# 11. Enviar datos incompletos (400)
curl -s -i -X POST "$API/controlador/categoria.php" "${H[@]}" "${JSON[@]}" \
  -d '{"codigo":6}'

# 12. Método no permitido (405)
curl -s -i -X PATCH "$API/controlador/categoria.php" "${H[@]}"

# 13. Eliminar la categoría 5 (200)
curl -s -i -X DELETE "$API/controlador/categoria.php" "${H[@]}" "${JSON[@]}" \
  -d '{"codigo":5}'

# 14. Verificar que ya no existe (404)
curl -s -i "$API/controlador/categoria.php?codigo=5" "${H[@]}"

# 15. Eliminar de nuevo (404)
curl -s -i -X DELETE "$API/controlador/categoria.php" "${H[@]}" "${JSON[@]}" \
  -d '{"codigo":5}'
```

### Ejemplo completo en PowerShell (Windows)

```powershell
$API = "http://localhost:18080"
$headers = @{ "X-Cedula" = "1"; "X-Contrasena" = "01" }
$json = @{ "Content-Type" = "application/json" }

# Listar
Invoke-RestMethod -Method Get -Uri "$API/controlador/categoria.php" -Headers $headers

# Crear
$body = @{ codigo = 5; nombre = "Monitores" } | ConvertTo-Json
Invoke-RestMethod -Method Post -Uri "$API/controlador/categoria.php" `
  -Headers $headers -ContentType "application/json" -Body $body

# Actualizar
$body = @{ codigo = 5; nombre = "Monitores LED" } | ConvertTo-Json
Invoke-RestMethod -Method Put -Uri "$API/controlador/categoria.php" `
  -Headers $headers -ContentType "application/json" -Body $body

# Eliminar
$body = @{ codigo = 5 } | ConvertTo-Json
Invoke-RestMethod -Method Delete -Uri "$API/controlador/categoria.php" `
  -Headers $headers -ContentType "application/json" -Body $body
```

Para ver el código HTTP de un error en PowerShell:

```powershell
try {
    Invoke-WebRequest -Method Get -Uri "$API/controlador/categoria.php?codigo=999" -Headers $headers
} catch {
    $_.Exception.Response.StatusCode.value__
}
```

---

## 6. Matriz de casos de prueba

| ID | Caso | Método y ruta | Datos | Código esperado |
| --- | --- | --- | --- | --- |
| CP-01 | Información de la API | `GET /` | — | `200` |
| CP-02 | Base conectada y tablas creadas | `GET /salud.php` | — | `200` + `estado: ok` |
| CP-03 | Listar categorías | `GET /controlador/categoria.php` | — | `200` + 4 categorías |
| CP-04 | Consultar categoría existente | `GET ...?codigo=1` | — | `200` |
| CP-05 | Consultar categoría inexistente | `GET ...?codigo=999` | — | `404` |
| CP-06 | Petición sin credenciales | `GET /controlador/categoria.php` | sin headers | `401` |
| CP-07 | Credenciales incorrectas | `GET /controlador/categoria.php` | clave errónea | `401` |
| CP-08 | Crear categoría | `POST /controlador/categoria.php` | `{"codigo":5,"nombre":"Monitores"}` | `201` |
| CP-09 | Consultar la categoría creada | `GET ...?codigo=5` | — | `200` + `Monitores` |
| CP-10 | Actualizar categoría | `PUT /controlador/categoria.php` | `{"codigo":5,"nombre":"Monitores LED"}` | `200` + 1 fila |
| CP-11 | Consultar la categoría actualizada | `GET ...?codigo=5` | — | `200` + `Monitores LED` |
| CP-12 | POST con datos incompletos | `POST /controlador/categoria.php` | `{"codigo":6}` | `400` |
| CP-13 | Método no permitido | `PATCH /controlador/categoria.php` | — | `405` + header `Allow` |
| CP-14 | Eliminar categoría | `DELETE /controlador/categoria.php` | `{"codigo":5}` | `200` |
| CP-15 | Verificar eliminación | `GET ...?codigo=5` | — | `404` |
| CP-16 | Eliminar categoría inexistente | `DELETE /controlador/categoria.php` | `{"codigo":5}` | `404` |
| CP-17 | POST con código duplicado | `POST /controlador/categoria.php` | `{"codigo":1,"nombre":"Repetida"}` | `500` (ver nota) |

> **CP-17 — comportamiento actual:** el repositorio inserta directamente y no
> captura la clave duplicada, por lo que MySQL lanza una excepción y el
> controlador responde `500`. Es una mejora pendiente: se podría validar
> primero o capturar el error `1062` y responder `409 Conflict`.

---

## 7. Respuestas esperadas

### Listar categorías (`GET`)

```json
[
  {"codigo": 1, "nombre": "Computadoras"},
  {"codigo": 2, "nombre": "Telefonos"},
  {"codigo": 3, "nombre": "Impresoras"},
  {"codigo": 4, "nombre": "Accesorios"}
]
```

### Crear (`POST`)

Código `201`:

```json
{"Correcto": "Insercion realizada"}
```

### Actualizar (`PUT`)

Código `200`:

```json
{"Correcto": "Actualizacion ejecutada", "Filas modificadas": 1}
```

Si `"Filas modificadas"` es `0`, el código no existe o el nombre ya era el
mismo. El controlador no distingue esos dos casos.

### Eliminar (`DELETE`)

Código `200`:

```json
{"Correcto": "Eliminacion realizada"}
```

### Errores

| Situación | Código | Respuesta |
| --- | --- | --- |
| Sin autenticación | `401` | `{"Error":"Autorizacion Invalida"}` |
| Datos incompletos | `400` | `{"Error":"Faltan datos"}` o `{"Error":"Falta el codigo"}` |
| No encontrado | `404` | `{"Error":"Categoria no encontrada"}` |
| Método no permitido | `405` | `{"Error":"Metodo no permitido"}` |
| Error de MySQL | `500` | `{"Error":"Ocurrio un error al consultar MySQL"}` |
| Base caída o tablas faltantes | `503` | Diagnóstico en `/salud.php` |

---

## 8. Reiniciar los datos de prueba

### Local

```bash
docker compose down -v
docker compose up -d --build
```

### Render

En la **Shell** del servicio:

```bash
php scripts/init_db.php
```

Ese script crea las tablas si no existen y vuelve a insertar las categorías
`1` a `4` y los usuarios de prueba. No borra filas adicionales. Para dejar la
base completamente limpia, ejecute en el editor SQL de su proveedor:

```sql
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS Programadores;
```

y después:

```bash
php scripts/init_db.php
```

---

## 9. Solución de problemas

| Problema | Qué revisar |
| --- | --- |
| `401` en todas las peticiones | Los headers `X-Cedula` y `X-Contrasena` deben existir y tener el nombre exacto. |
| `500` al consultar | Abra `/salud.php`; luego revise los logs del servicio en Render (`Logs`). |
| `/salud.php` responde `503` con `tablas_faltantes` | Ejecute `php scripts/init_db.php` o active `AUTO_INIT_DB=true`. |
| `/salud.php` responde `503` sin tablas faltantes | La base no es accesible: revise `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` y las reglas de firewall. |
| Error de TLS/certificado | Configure `DB_SSL_CA` con el PEM o `DB_SSL_VERIFICAR=false`. |
| El servicio tarda en responder | Plan gratuito de Render: se suspende por inactividad. |
| `404` al abrir la API en Render | La ruta correcta incluye `/controlador/categoria.php`. |

---

## 10. Lista de verificación final

- [ ] `/` responde `200`.
- [ ] `/salud.php` responde `200` con `estado: ok`.
- [ ] GET sin credenciales responde `401`.
- [ ] GET con credenciales lista las 4 categorías iniciales.
- [ ] POST crea una categoría y responde `201`.
- [ ] PUT actualiza la categoría y devuelve `Filas modificadas: 1`.
- [ ] DELETE elimina la categoría y responde `200`.
- [ ] Una segunda eliminación responde `404`.
- [ ] Un método no permitido responde `405`.
- [ ] La colección de Postman termina sin fallos.
