<?php
include('../../config/dbconn.php');
date_default_timezone_set("America/Guayaquil");

session_start();

function validarEstadoPago($payment_status) {
    // Verifica si el estado de pago es válido
    $valid_statuses = ["Pendiente", "Pagado", "Fallido"];
    return in_array($payment_status, $valid_statuses) ? $payment_status : "Pendiente";
}

function subirCSV() {
    $nombreArchivo = $_FILES['file']['name'];
    $archivoTemp = $_FILES['file']['tmp_name'];
    $directorioTemp = sys_get_temp_dir(); // Obtener el directorio temporal del sistema
    $rutaDestino = $directorioTemp . '/' . $nombreArchivo;

    // Intentar mover el archivo temporal al directorio de destino (temporal)
    if (move_uploaded_file($archivoTemp, $rutaDestino)) {
        return $rutaDestino; // Devuelve la ruta del archivo
    } else {
        // Si falla la carga, devuelve false
        return false;
    }
}

if (isset($_POST['insertpayment'])) {
    try {
        // Recopilamos los datos del formulario
        $patient_id = intval($_POST['patient_id']);
        $app_id = $_POST['app_id'] ?? null;
        $payer_id = $_POST['payer_id'] ?? null;
        $ref_id = $_POST['ref_id'] ?? null;
        $payment_status = validarEstadoPago($_POST['payment_status']);
        $amount = floatval($_POST['amount']);
        $currency = $_POST['currency'] ?? 'USD';
        $txn_id = $_POST['transaction_id'] ?? null;
        $payer_email = $_POST['payer_email'] ?? null;
        $first_name = $_POST['first_name'] ?? null;
        $last_name = $_POST['last_name'] ?? null;
        $method = $_POST['payment_method'];
        $created_at = date('Y-m-d H:i:s');

        // Verificar si el patient_id existe en tblappointment
        $check_stmt = $conn->prepare("SELECT id FROM tblappointment WHERE patient_id = ? LIMIT 1");
        $check_stmt->bind_param("i", $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            throw new Exception("ID de paciente no válido en citas (tblappointment). Verifique el ID y vuelva a intentarlo.");
        }

        // Verificar si el patient_id existe en tblpatient
        $check_patient_stmt = $conn->prepare("SELECT id FROM tblpatient WHERE id = ? LIMIT 1");
        $check_patient_stmt->bind_param("i", $patient_id);
        $check_patient_stmt->execute();
        $patient_result = $check_patient_stmt->get_result();

        if ($patient_result->num_rows === 0) {
            throw new Exception("ID de paciente no existe en la base de datos de pacientes (tblpatient). Verifique el ID y vuelva a intentarlo.");
        }

        // Insertar el pago en la tabla payments
        $sql = "INSERT INTO payments (patient_id, app_id, payer_id, ref_id, payment_status, amount, currency, txn_id, payer_email, first_name, last_name, method, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                "iisssdsssssss",
                $patient_id,
                $app_id,
                $payer_id,
                $ref_id,
                $payment_status,
                $amount,
                $currency,
                $txn_id,
                $payer_email,
                $first_name,
                $last_name,
                $method,
                $created_at
            );

            if ($stmt->execute()) {
                $_SESSION['success'] = "El pago se registró correctamente.";
                header('Location: payments.php?success=1');
                exit();
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } else {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: payments.php?error=1');
        exit();
    }
}

if (isset($_POST['importSubmit'])) {
    // Intentar subir el archivo CSV
    $rutaArchivo = subirCSV();

    if ($rutaArchivo) {
        // Archivo subido exitosamente, ahora procesa el CSV
        $csvFile = fopen($rutaArchivo, 'r');

        if (!$csvFile) {
            $_SESSION['error'] = "No se pudo abrir el archivo CSV.";
            header("Location: payments.php?error=1");
            exit();
        }

        // Resto del código para procesar el CSV ...
    } else {
        $_SESSION['error'] = "Se produjo un problema al subir el archivo, por favor inténtelo de nuevo.";
        header('Location: payments.php?error=1');
    }
}

// Cerramos la conexión a la base de datos
$conn->close();
?>
