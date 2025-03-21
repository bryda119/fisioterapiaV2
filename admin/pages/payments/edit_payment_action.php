<?php
include('../../config/dbconn.php');
session_start();

function validarEstadoPago($payment_status) {
    $valid_statuses = ["Pendiente", "Pagado", "Fallido"];
    return in_array($payment_status, $valid_statuses) ? $payment_status : "Pendiente";
}

if (isset($_POST['payment_id'])) {
    try {
        // Recibimos los datos del formulario
        $payment_id = $_POST['payment_id'];
        $amount = floatval($_POST['amount']);
        $payment_status = validarEstadoPago($_POST['payment_status']);
        $method = $_POST['method'];

        // Preparamos la consulta para actualizar el pago
        $sql = "UPDATE payments SET amount = ?, payment_status = ?, method = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("dssi", $amount, $payment_status, $method, $payment_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "El pago se actualizó correctamente.";
                header('Location: index.php?success=1');
                exit();
            } else {
                throw new Exception("Error al actualizar el pago: " . $stmt->error);
            }
        } else {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: index.php?error=1');
        exit();
    }
}

// Cerramos la conexión a la base de datos
$conn->close();
?>
