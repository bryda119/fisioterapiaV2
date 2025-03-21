<?php
include('../../config/dbconn.php');
session_start();

if (isset($_GET['id'])) {
    $payment_id = $_GET['id'];

    try {
        // Preparamos la consulta para eliminar el pago
        $sql = "DELETE FROM payments WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Enlazamos el parámetro y ejecutamos la consulta
            $stmt->bind_param("i", $payment_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "El pago se eliminó correctamente.";
                header('Location: index.php?success=1');
                exit();
            } else {
                throw new Exception("Error al eliminar el pago: " . $stmt->error);
            }
        } else {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: index.php?error=1');
        exit();
    }
} else {
    $_SESSION['error'] = "ID de pago no válido.";
    header('Location: index.php?error=1');
    exit();
}

// Cerramos la conexión a la base de datos
$conn->close();
?>
