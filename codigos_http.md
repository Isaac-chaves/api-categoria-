# Metodos HTTP y codigos de respuesta - API de Categorias

## Metodos HTTP: que operacion realiza cada uno?

| Metodo HTTP | Operacion | Instruccion SQL | Explicacion |
|---|---|---|---|
| `GET` | **Obtener** | `SELECT` | Obtiene una categoria por su codigo o todas las categorias. |
| `POST` | **Insertar** | `INSERT` | Inserta una nueva categoria. |
| `PUT` | **Actualizar** | `UPDATE` | Modifica el nombre de una categoria existente. |
| `DELETE` | **Eliminar** | `DELETE` | Elimina una categoria por su codigo. |

`GET`, `POST`, `PUT` y `DELETE` son metodos HTTP utilizados por Postman y la API.
`SELECT`, `INSERT`, `UPDATE` y `DELETE` son instrucciones SQL ejecutadas en MySQL.
Por ejemplo, **PUT** es el metodo HTTP para **actualizar**, mientras que **UPDATE** es la instruccion SQL correspondiente.

## Codigos de respuesta HTTP

En una API, `http_response_code(...)` comunica el resultado de la solicitud,
y `echo json_encode(...)` devuelve el mensaje o los datos en JSON.

| Codigo | Significado |
|---|---|
| `200` | Operacion exitosa, como consultar o actualizar. |
| `201` | Recurso creado correctamente. |
| `204` | Operacion exitosa sin contenido de respuesta. |
| `400` | Solicitud incorrecta. |
| `404` | Recurso no encontrado. |
| `401` | Credenciales de los encabezados incorrectas o ausentes. |
| `500` | Error interno del servidor. |

Tambien se utiliza `405` (metodo HTTP no permitido).

## Que codigo se usa en cada operacion?

| Operacion | Codigo | Cuando ocurre |
|---|---|---|
| GET (Obtener) | `200` | Se obtuvo una categoria o la lista. |
| GET (Obtener) | `404` | No existe el codigo solicitado. |
| POST (Insertar) | `201` | Se inserto una categoria. |
| POST (Insertar) | `400` | Falta el codigo o nombre. |
| PUT (Actualizar) | `200` | Se ejecuto la actualizacion. |
| PUT (Actualizar) | `400` | Faltan datos para actualizar. |
| DELETE (Eliminar) | `200` | Se elimino y se devolvio un JSON. |
| DELETE (Eliminar) | `400` | No se indico el codigo. |
| DELETE (Eliminar) | `404` | No existe la categoria. |
| General | `405` | El metodo HTTP no es GET, POST, PUT o DELETE. |
| General | `500` | Ocurre un error de base de datos. |

> **Importante:** `204` no se utiliza en este proyecto, porque significa
> que la respuesta no tendra contenido. Para mostrar mensajes JSON despues
> de eliminar, el ejemplo utiliza `200`.

En el controlador, cuando una operacion se ejecuta correctamente sin
indicar un codigo de forma explicita, PHP normalmente responde `200`.

Para ver como probar cada solicitud, consulta `ejemplo_postman.txt`.


## Autenticacion agregada al ejemplo

En todas las solicitudes agregar `X-Cedula` y `X-Contrasena` en los encabezados.
Los pares de practica son `1 / 01`, `2 / 02` y `3 / 03`.
El controlador utiliza `getallheaders()` para leer `X-Cedula` y `X-Contrasena`,
y llama a `ProgramadorRepositorio::validar()`. El repositorio comprueba
ambos valores en `ventas.Programadores`. En Postman use esos nombres exactos.
Si no son validas, responde **HTTP 401** con `{"Error":"Autorizacion Invalida"}`.
Esta validacion personalizada no representa OAuth 2.0.

### Encabezados obligatorios para los ejemplos

En Postman, abra **Headers** y agregue estos dos pares en **cada** solicitud (GET, POST, PUT y DELETE):

```http
X-Cedula: 1
X-Contrasena: 01
```

Tambien puede utilizar `X-Cedula: 2` con `X-Contrasena: 02`, o
`X-Cedula: 3` con `X-Contrasena: 03`.

Ejemplo con una contrasena incorrecta:

```http
GET http://localhost/api_categorias/controlador/categoria.php
X-Cedula: 1
X-Contrasena: 99
```

Respuesta: **HTTP 401 Unauthorized**

```json
{"Error":"Autorizacion Invalida"}
```

**Aviso:** las contrasenas 01, 02 y 03 se guardan como texto solo para esta practica local. No use este esquema en una API publica.
