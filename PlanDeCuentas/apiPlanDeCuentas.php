<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "contabilidad";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
/*
// Verificar si la solicitud es GET o POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Consulta para obtener todos los registros
    $sql = "SELECT * FROM plan_de_cuentas";

    // Verificar si hay parámetros de consulta (filtros)
    if (!empty($_GET)) {
        $conditions = array();

        // Construir condiciones según los parámetros de consulta
        if (isset($_GET['rubro'])) {
            $conditions[] = "rubro = '" . $conn->real_escape_string($_GET['rubro']) . "'";
        }

        if (isset($_GET['nroCuenta'])) {
            $conditions[] = "nroCuenta = " . (int)$_GET['nroCuenta'];
        }

        // Agregar condiciones a la consulta si existen
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
    }

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Verificar si hay resultados
    if ($result->num_rows > 0) {
        // Inicializar un array para almacenar los resultados
        $data = array();

        // Obtener los resultados como un array asociativo
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Mostrar los resultados en formato JSON
        echo json_encode($data);
    } else {
        // Si no hay resultados
        echo json_encode(["message" => "No se encontraron registros."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar la solicitud POST (ejemplo: insertar nuevo registro)
    // Asegúrate de validar y sanitizar los datos antes de usarlos en la consulta
    $rubro = $conn->real_escape_string($_POST['rubro']);
    $nroCuenta = (int)$_POST['nroCuenta'];
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    // Consulta para insertar un nuevo registro
    $insertQuery = "INSERT INTO plan_de_cuentas (rubro, nroCuenta, descripcion) VALUES ('$rubro', $nroCuenta, '$descripcion')";

    if ($conn->query($insertQuery) === TRUE) {
        echo json_encode(["message" => "Nuevo registro insertado con éxito."]);
    } else {
        echo "Error al insertar el registro: " . $conn->error;
    }
} else {
    // Si la solicitud no es GET ni POST
    echo "Método no permitido.";
}

// Cerrar la conexión a la base de datos
$conn->close();

*/

// Verificar el método de solicitud (GET, POST, PUT o DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Función para obtener datos de la solicitud
function get_data_from_request()
{
    $data = array();

    // Manejar datos de formulario codificado
    if ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded' && isset($_POST)) {
        foreach ($_POST as $key => $value) {
            $data[$key] = $value;
        }
    }

    // Manejar datos JSON
    elseif ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData !== null) {
            $data = $jsonData;
        } else {
            // Manejar caso donde la decodificación JSON falla
            echo json_encode(["error" => "Error al decodificar JSON."]);
            exit;
        }
    }

    return $data;
}

// Manejar la solicitud según el método
switch ($method) {
    case 'GET':
        // Consulta para obtener todos los registros
        $sql = "SELECT * FROM plan_de_cuentas";

        // Verificar si hay parámetros de consulta (filtros)
        if (!empty($_GET)) {
            $conditions = array();

            // Construir condiciones según los parámetros de consulta
            if (isset($_GET['rubro'])) {
                $conditions[] = "rubro = '" . $conn->real_escape_string($_GET['rubro']) . "'";
            }

            if (isset($_GET['nroCuenta'])) {
                $conditions[] = "nroCuenta = " . (int)$_GET['nroCuenta'];
            }

            // Agregar condiciones a la consulta si existen
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
        }

        // Ejecutar la consulta
        $result = $conn->query($sql);

        // Verificar si hay resultados
        if ($result->num_rows > 0) {
            // Inicializar un array para almacenar los resultados
            $data = array();

            // Obtener los resultados como un array asociativo
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            // Mostrar los resultados en formato JSON
            echo json_encode($data);
        } else {
            // Si no hay resultados
            echo json_encode(["message" => "No se encontraron registros."]);
        }
        break;

    case 'POST':
        // Manejar la solicitud POST (ejemplo: insertar nuevo registro)
        // Asegúrate de validar y sanitizar los datos antes de usarlos en la consulta
        $postData = get_data_from_request();

        $rubro = $conn->real_escape_string($postData['rubro']);
        $nroCuenta = (int)$postData['nroCuenta'];
        $descripcion = $conn->real_escape_string($postData['descripcion']);

        // Consulta para insertar un nuevo registro
        $insertQuery = "INSERT INTO plan_de_cuentas (rubro, nroCuenta, descripcion) VALUES ('$rubro', $nroCuenta, '$descripcion')";

        if ($conn->query($insertQuery) === TRUE) {
            echo json_encode(["message" => "Nuevo registro insertado con éxito."]);
        } else {
            echo "Error al insertar el registro: " . $conn->error;
        }
        break;

    case 'PUT':
        // Manejar la solicitud PUT (ejemplo: actualizar un registro)
        // Asegúrate de validar y sanitizar los datos antes de usarlos en la consulta
        $putData = get_data_from_request();

        $rubro = $conn->real_escape_string($putData['rubro']);
        $nroCuenta = (int)$putData['nroCuenta'];
        $descripcion = $conn->real_escape_string($putData['descripcion']);

        $updateQuery = "UPDATE plan_de_cuentas SET rubro='$rubro', descripcion='$descripcion' WHERE  nroCuenta=$nroCuenta";

        if ($conn->query($updateQuery) === TRUE) {
            echo json_encode(["message" => "Registro actualizado con éxito."]);
        } else {
            echo "Error al actualizar el registro: " . $conn->error;
        }
        break;

    case 'DELETE':
        // Manejar la solicitud DELETE (ejemplo: eliminar un registro)
        // Asegúrate de validar y sanitizar los datos antes de usarlos en la consulta
        $deleteData = get_data_from_request();

        // Verificar si la clave 'rubro' está presente en $deleteData
        $rubro = isset($deleteData['rubro']) ? $conn->real_escape_string($deleteData['rubro']) : null;
        $nroCuenta = (int)$deleteData['nroCuenta'];

        if ($rubro !== null) {
            $deleteQuery = "DELETE FROM plan_de_cuentas WHERE rubro='$rubro' AND nroCuenta=$nroCuenta";

            if ($conn->query($deleteQuery) === TRUE) {
                echo json_encode(["message" => "Registro eliminado con éxito."]);
            } else {
                echo "Error al eliminar el registro: " . $conn->error;
            }
        } else {
            echo json_encode(["error" => "La clave 'rubro' no está presente en la solicitud DELETE."]);
        }

        break;

    default:
        // Si la solicitud no es GET, POST, PUT ni DELETE
        echo "Método no permitido.";
        break;
}

// Cerrar la conexión a la base de datos
$conn->close();
