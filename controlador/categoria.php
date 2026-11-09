<?php
/*
 * CONTROLADOR DE CATEGORIAS.
 * Recibe solicitudes HTTP de Postman y devuelve respuestas JSON.
 * METODOS HTTP Y OPERACIONES:
 * GET    = Obtener una categoria o todas las categorias (SELECT en SQL).
 * POST   = Insertar una categoria (INSERT en SQL).
 * PUT    = Actualizar una categoria (UPDATE en SQL).
 * DELETE = Eliminar una categoria (DELETE en SQL).
 *
 * GET, POST, PUT y DELETE son metodos HTTP.
 * SELECT, INSERT, UPDATE y DELETE son instrucciones SQL.
 *
 * CODIGOS HTTP DE RESPUESTA:
 * 200 = Operacion exitosa, por ejemplo consultar o actualizar.
 * 201 = Recurso creado correctamente.
 * 204 = Operacion exitosa sin contenido de respuesta (no se usa aqui).
 * 400 = Solicitud incorrecta o incompleta.
 * 404 = Recurso no encontrado.
 * 500 = Error interno del servidor.
 * 405 = Metodo HTTP no permitido (tambien se utiliza en este controlador).
 * 401 = Autorizacion Invalida (X-Cedula o X-Contrasena incorrectos).
 *
 * IMPORTANTE: http_response_code() indica el resultado de la solicitud.
 * echo json_encode() envia el mensaje o los datos en formato JSON.
 * Si no se establece otro codigo, PHP normalmente responde con 200.
 * No debe enviarse JSON en una respuesta 204.
 */

// Indicar que la respuesta se enviara en formato JSON.
header("Content-Type: application/json; charset=utf-8");

// Importar el modelo y el repositorio de categorias.
require_once __DIR__ . '/../modelos/Categoria.php';
require_once __DIR__ . '/../repositorios/CategoriaRepositorio.php';
require_once __DIR__ . '/../repositorios/ProgramadorRepositorio.php';

// Identificar que metodo HTTP utilizo Postman: GET, POST, PUT o DELETE.
$metodo = $_SERVER["REQUEST_METHOD"];

try {
    // Obtener los encabezados enviados desde Postman.
    $encabezados = getallheaders();

    // Obtener la cedula enviada en el encabezado X-Cedula.
    // Si el encabezado no existe, se asigna una cadena vacia ("").
    $cedula = $encabezados["X-Cedula"] ?? "";

    // Obtener la contrasena enviada en el encabezado X-Contrasena.
    // Si el encabezado no existe, se asigna una cadena vacia ("").
    $contrasena = $encabezados["X-Contrasena"] ?? "";

    // Crear una instancia del repositorio para consultar la base de datos.
    $repositorio = new ProgramadorRepositorio();

    // Verificar si existe un programador con la cedula y contrasena recibidas.
    // El metodo validar() devuelve true si las credenciales son correctas
    // y false si no existe un programador con esos datos.
    if (!$repositorio->validar($cedula, $contrasena)) {

        // Devolver el codigo HTTP 401 (No autorizado).
        http_response_code(401);

        // Enviar un mensaje de error en formato JSON.
        echo json_encode(["Error" => "Autorizacion Invalida"]);

        // Detener la ejecucion para impedir el acceso al API de categorias.
        exit;
    }

    // El CRUD de categorias se conserva sin cambios.
    $repositorio = new CategoriaRepositorio();

    // GET = Obtener: consultar una categoria o todas las categorias.
    if ($metodo == "GET") {
        // Si la URL incluye ?codigo=1, consultar solamente esa categoria.
        if (isset($_GET["codigo"])) {
            $categoria = $repositorio->buscar($_GET["codigo"]);

            if ($categoria) {
                // HTTP 200: consulta exitosa (codigo predeterminado).
                echo json_encode($categoria); // Devolver la categoria encontrada.
            } else {
                // HTTP 404: no existe una categoria con ese codigo.
                http_response_code(404);
                echo json_encode(["Error" => "Categoria no encontrada"]);
            }
        } else {
            // Si no se envia codigo, devolver todas las categorias.
            $categorias = $repositorio->listar();
            // HTTP 200: consulta exitosa; tambien se devuelve 200 si la lista esta vacia.
            echo json_encode($categorias);
        }

    } elseif ($metodo == "POST") {
        // POST = Insertar: guardar una categoria nueva.
        // Leer y convertir en un arreglo el JSON enviado en el cuerpo de Postman.
        $body = json_decode(file_get_contents("php://input"), true);

        // Comprobar que el JSON tenga codigo y nombre.
        if (!isset($body["codigo"]) || !isset($body["nombre"])) {
            // HTTP 400: la solicitud no contiene todos los datos necesarios.
            http_response_code(400);
            echo json_encode(["Error" => "Faltan datos"]);
            exit;
        }

        // Crear el objeto y ejecutar INSERT por medio del repositorio.
        $categoria = new Categoria($body["codigo"], $body["nombre"]);
        $repositorio->guardar($categoria);
        // HTTP 201: se creo una nueva categoria correctamente.
        http_response_code(201);
        echo json_encode(["Correcto" => "Insercion realizada"]);

    } elseif ($metodo == "PUT") {
        // PUT = Actualizar: modificar una categoria existente.
        // Leer el JSON con el codigo existente y el nuevo nombre.
        $body = json_decode(file_get_contents("php://input"), true);

        if (!isset($body["codigo"]) || !isset($body["nombre"])) {
            // HTTP 400: la solicitud no contiene todos los datos necesarios.
            http_response_code(400);
            echo json_encode(["Error" => "Faltan datos"]);
            exit;
        }

        // Crear el objeto y ejecutar UPDATE por medio del repositorio.
        $categoria = new Categoria($body["codigo"], $body["nombre"]);
        $filas = $repositorio->actualizar($categoria);

        // HTTP 200: solicitud procesada correctamente (codigo predeterminado).
        // En MySQL, 0 tambien puede significar que el nombre ya era el mismo.
        echo json_encode(["Correcto" => "Actualizacion ejecutada", "Filas modificadas" => $filas]);

    } elseif ($metodo == "DELETE") {
        // DELETE = Eliminar: borrar una categoria por su codigo.
        // Leer el codigo desde el JSON enviado en el Body de Postman.
        $body = json_decode(file_get_contents("php://input"), true);

        if (!isset($body["codigo"])) {
            // HTTP 400: no se envio el codigo de la categoria a eliminar.
            http_response_code(400);
            echo json_encode(["Error" => "Falta el codigo"]);
            exit;
        }

        // Ejecutar DELETE y conocer cuantas filas se eliminaron.
        $filas = $repositorio->eliminar($body["codigo"]);

        if ($filas > 0) {
            // HTTP 200: eliminacion correcta y respuesta JSON.
            // Podriamos devolver 204, pero entonces no enviariamos el JSON.
            echo json_encode(["Correcto" => "Eliminacion realizada"]);
        } else {
            // HTTP 404: no existe la categoria que se intenta eliminar.
            http_response_code(404);
            echo json_encode(["Error" => "Categoria no encontrada"]);
        }

    } else {
        // Respuesta cuando se utiliza un metodo HTTP no permitido.
        // HTTP 405: la API solo admite GET, POST, PUT y DELETE.
        http_response_code(405);
        header("Allow: GET, POST, PUT, DELETE");
        echo json_encode(["Error" => "Metodo no permitido"]);
    }

} catch (PDOException $e) {
    // Evitar mostrar al usuario informacion interna sobre la base de datos.
    // HTTP 500: ocurrio un error interno al comunicarse con MySQL.
    http_response_code(500);
    echo json_encode(["Error" => "Ocurrio un error al consultar MySQL"]);
}
