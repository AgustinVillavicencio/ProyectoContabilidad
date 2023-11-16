<?php
// Conectar a la base de datos (reemplaza con tus propias credenciales)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contabilidad";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

// Manejar solicitudes GET y POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener parámetros de la solicitud GET
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
    $nroAsiento = isset($_GET['nroAsiento']) ? $_GET['nroAsiento'] : null;
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;

    if ($tipo === 'libro_diario') {
        // Consulta para obtener datos del libro_diario
        $sql = "SELECT ld.nroAsiento, pdc.descripcion, ld.debe, ld.haber
                FROM libro_diario ld
                JOIN plan_de_cuentas pdc ON ld.FK_plan_de_cuentas = pdc.nroCuenta
                WHERE fecha = '$fecha'";

        if ($nroAsiento != 0) {
            $sql .= " AND nroAsiento = $nroAsiento";
        }

        $result = $conn->query($sql);

        // Crear un array para almacenar los resultados
        $data = array();

        // Obtener los datos de la consulta
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Calcular el total y agregar la fila con la descripción "TOTAL" y los totales calculados
        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($data as $entry) {
            $totalDebe += $entry['debe'];
            $totalHaber += $entry['haber'];
        }

        $data[] = array('nroAsiento' => '-', 'descripcion' => 'TOTAL', 'debe' => $totalDebe, 'haber' => $totalHaber);

        // Devolver los datos del libro_diario como JSON
        echo json_encode($data);
    } elseif ($tipo === 'descripciones_plan_de_cuentas') {
        // Consulta para obtener las descripciones de plan_de_cuentas
        $sql = "SELECT descripcion FROM plan_de_cuentas";

        $result = $conn->query($sql);

            // Crear un array para almacenar las descripciones
        $data = array();

            // Obtener las descripciones de la consulta
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['descripcion'];
        }

            // Devolver las descripciones como JSON
        echo json_encode($data);
    } elseif ($tipo === 'datos_cuenta') {
        $cuenta = isset($_GET['cuenta']) ? $_GET['cuenta'] : null;

        if ($cuenta) {
                // Obtener FK_mayor para la cuenta seleccionada
            $sql = "SELECT FK_libro_mayor,nroCuenta FROM plan_de_cuentas WHERE descripcion = '$cuenta'";
            $result = $conn->query($sql);

            $data = array();

            if ($row = $result->fetch_assoc()) {
                $data['FK_mayor'] = $row['FK_libro_mayor'];
                $data['nroCuenta'] = $row['nroCuenta'];
            } else {
                $data['FK_mayor'] = null;
                $data['nroCuenta'] = null;
            }

            echo json_encode($data);
        } else {
            http_response_code(400); // Solicitud incorrecta
            echo json_encode(array('message' => 'Parámetros incorrectos para obtener FK_mayor.'));
        }
    } else {
        // Tipo no válido
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(array('error' => 'Tipo de solicitud no válido'));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodificar los datos JSON enviados en la solicitud POST
    $postData = json_decode(file_get_contents("php://input"), true);
    echo "Datos decodificados POST: " . print_r($postData, true);
    // Asegurarse de que los valores no sean nulos antes de asignarlos
    $nroAsiento = isset($postData['nroAsiento']) ? $postData['nroAsiento'] : null;
    $fecha = isset($postData['fecha']) ? $postData['fecha'] : null;
    $importe = isset($postData['importe']) ? $postData['importe'] : null;
    $nroCuenta = isset($postData['nroCuenta']) ? $postData['nroCuenta'] : null;
    $mayor = isset($postData['mayor']) ? $postData['mayor'] : null;
    $tipoMovimiento = isset($postData['tipoMovimiento']) ? $postData['tipoMovimiento'] : null;
    $valorCero=0;

    // Validar y limpiar datos
    $nroAsiento = isset($postData['nroAsiento']) ? htmlspecialchars($postData['nroAsiento']) : null;
    $fecha = isset($postData['fecha']) ? htmlspecialchars($postData['fecha']) : null;
    $importe = isset($postData['importe']) ? htmlspecialchars($postData['importe']) : null;
    $nroCuenta = isset($postData['nroCuenta']) ? htmlspecialchars($postData['nroCuenta']) : null;
    $mayor = isset($postData['mayor']) ? htmlspecialchars($postData['mayor']) : null;
    $tipoMovimiento = isset($postData['tipoMovimiento']) ? htmlspecialchars($postData['tipoMovimiento']) : null;

    // Verificar que los valores requeridos no sean nulos antes de insertar
    if ($nroAsiento !== null && $fecha !== null && $importe !== null && $nroCuenta !== null && $mayor !== null && $tipoMovimiento !== null) {
        // Construir consulta SQL de manera segura
        if ($tipoMovimiento == "debe") {
            $sql = "INSERT INTO libro_diario (nroAsiento, fecha, debe, haber, FK_mayor, FK_plan_de_cuentas) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isddii", $nroAsiento, $fecha, $importe, $valorCero, $mayor, $nroCuenta);
        } else {
            $sql = "INSERT INTO libro_diario (nroAsiento, fecha, debe, haber, FK_mayor, FK_plan_de_cuentas) 
                    VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isddii", $nroAsiento, $fecha, $valorCero,$importe, $mayor, $nroCuenta);
        }

        if ($stmt->execute()) {
            echo json_encode(array('message' => 'Asiento guardado correctamente'));
        } else {
            echo json_encode(array('error' => 'Error al guardar el asiento: ' . $stmt->error));
        }

        // Cerrar la declaración preparada
        $stmt->close();
    } else {
        http_response_code(400); // Solicitud incorrecta
        echo json_encode(array('error' => 'Valores requeridos nulos en la solicitud POST'));
    }
} else {
    // Tipo de solicitud no válido
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(array('error' => 'Tipo de solicitud no valido'));
}

// Cerrar la conexión
$conn->close();
?>
