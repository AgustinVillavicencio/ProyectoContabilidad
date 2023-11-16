<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contabilidad";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(array("error" => "Conexión fallida: " . $conn->connect_error)));
}

// Manejar solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener los parámetros de la consulta
    $nroCuenta = $_GET['nroCuenta'];
    $mes = $_GET['mes'];
    $anio = $_GET['anio'];

    // Consulta SQL
    $sql = "SELECT lm.nroCuenta, pdc.descripcion, lm.mes, lm.anio, lm.total_debe, lm.total_haber, lm.saldo
            FROM libro_mayor lm
            JOIN plan_de_cuentas pdc ON lm.nroCuenta = pdc.nroCuenta 
            WHERE nroCuenta = '$nroCuenta' AND mes = '$mes' AND anio = '$anio'";

    $result = $conn->query($sql);

    // Construir el array de resultados
    $rows = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    // Devolver los resultados como JSON
    echo json_encode($rows);
}

// Manejar solicitudes PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $put_vars);
    
    // Obtener valores de la solicitud PUT
    $id = $put_vars['id'];
    $nuevoSaldo = $put_vars['nuevoSaldo'];

    // Actualizar el saldo en la base de datos
    $updateSql = "UPDATE libro_mayor SET saldo = '$nuevoSaldo' WHERE id = '$id'";
    if ($conn->query($updateSql) === TRUE) {
        echo json_encode(array("message" => "Registro actualizado con éxito"));
    } else {
        echo json_encode(array("error" => "Error al actualizar el registro: " . $conn->error));
    }
}

// Cerrar la conexión
$conn->close();
?>
