<?php
include('../config/dbconn.php');
include('../../superglobal.php');
use Twilio\Rest\Client;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Twilio\Exceptions\TwilioException;

require('../../vendor/autoload.php');

function logCronOutput($message)
{
    $logFile = '/home/u932929812/logs/cron_log.txt';  // Cambia esta ruta según la configuración de tu servidor
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

function sendTextMessage($patient_name, $patient_phone, $text)
{
    include('../config/dbconn.php');
    $sql = "SELECT * FROM sms_settings WHERE id='1'";
    $query_run = mysqli_query($conn, $sql);
    if (mysqli_num_rows($query_run) > 0) {
        foreach ($query_run as $row) {
            $sid = $row['sid'];
            $token = $row['token'];
            $sender = $row['sender'];
        }
    }
    $client = new Client($sid, $token);

    try {
        $client->messages->create(
            $patient_phone,
            [
                'from' => $sender,
                'body' => 'Estimado/a ' . $patient_name . ', ' . $text . ''
            ]
        );
    } catch (TwilioException $e) {
        logCronOutput('Error SMS: ' . $e->getCode());
    }
}

function sendEmail($patient_name, $patient_email, $patient_date, $patient_time, $patient_phone, $treatment, $date_submission, $system_name)
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';  // Configurado para Hostinger
    $mail->SMTPAuth   = true;
    $mail->Username   = 'administardor@digitalmasterbmc.com';  // Tu dirección de correo
    $mail->Password   = 'Brayesa061120.';  // Tu contraseña
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Usando TLS
    $mail->Port       = 587;  // Puerto para TLS

    $mail->setFrom('administardor@digitalmasterbmc.com', 'System Name');
    $mail->addAddress($patient_email);

    // Establecer prioridad alta
    $mail->Priority = 1; // 1 = Alta prioridad
    $mail->addCustomHeader('X-Priority', '1');  // Establece la prioridad alta

    $mail->isHTML(true);
    $mail->Subject = 'Cita programada | ' . $system_name;
    $email_template =
        '<p>Cita programada el ' . $date_submission . '</p>
                    <p>Detalles de la cita<br>
                    Nombre: ' . $patient_name . '<br>
                    Número de contacto: ' . $patient_phone . '<br>
                    Correo: ' . $patient_email . '<br>
                    Fecha preferida: ' . $patient_date . '<br>
                    Hora: ' . $patient_time . '</p>
                    <p>Tratamiento: ' . $treatment . '</p>
                    <p>Recordatorio: No olvide usar mascarilla para reducir la propagación del coronavirus</p>
                    <p>¡Gracias!<br>
                    Equipo de ' . $system_name . '</p>';
    $mail->Body = $email_template;

    try {
        $mail->send();
        logCronOutput("Correo enviado a $patient_email para $patient_name.");
    } catch (Exception $e) {
        logCronOutput("Error en el correo: {$mail->ErrorInfo}");
    }
}

// Verificar citas programadas para el siguiente día
function sendReminders()
{
    include('../config/dbconn.php');
    // Recordatorio un día antes
    $reminder_date = date('Y-m-d', strtotime('tomorrow'));
    $sql = "SELECT a.*, p.phone AS patient_phone, p.email AS patient_email FROM tblappointment a 
            INNER JOIN tblpatient p ON a.patient_id = p.id 
            WHERE a.schedule = '$reminder_date' AND a.reminder_sent = 0";  // Se verifica que reminder_sent esté en 0
    $query_run = mysqli_query($conn, $sql);
    
    if (!$query_run) {
        logCronOutput("Error SQL: " . mysqli_error($conn));
        return;
    }

    while ($row = mysqli_fetch_array($query_run)) {
        $patient_name = $row['patient_name'];
        $patient_phone = $row['patient_phone'];
        $patient_email = $row['patient_email'];
        $patient_date = $row['schedule'];
        $patient_time = $row['starttime'];
        $treatment = $row['reason'];

        // Enviar primer recordatorio (1 día antes)
        if (!empty($patient_phone)) {
            $text = 'Recordatorio: Su cita es mañana a las ' . $patient_time . '. ¡No olvide usar mascarilla!';
            sendTextMessage($patient_name, $patient_phone, $text);
        }

        if (!empty($patient_email)) {
            sendEmail($patient_name, $patient_email, $patient_date, $patient_time, $patient_phone, $treatment, $patient_date, 'System Name');
        }

        // Marcar como enviado el primer recordatorio (cambiar reminder_sent a 1)
        $update_sql = "UPDATE tblappointment SET reminder_sent = 1 WHERE id = " . $row['id'];
        mysqli_query($conn, $update_sql);
    }

    // Recordatorio dos horas antes
    $reminder_time = date('Y-m-d H:i:s', strtotime('-2 hours'));
    $sql = "SELECT a.*, p.phone AS patient_phone, p.email AS patient_email FROM tblappointment a 
            INNER JOIN tblpatient p ON a.patient_id = p.id 
            WHERE a.starttime = '$reminder_time' AND a.reminder_sent = 1"; // Solo se envía si reminder_sent = 1
    $query_run = mysqli_query($conn, $sql);

    if (!$query_run) {
        logCronOutput("Error SQL: " . mysqli_error($conn));
        return;
    }

    while ($row = mysqli_fetch_array($query_run)) {
        $patient_name = $row['patient_name'];
        $patient_phone = $row['patient_phone'];
        $patient_email = $row['patient_email'];
        $patient_date = $row['schedule'];
        $patient_time = $row['starttime'];
        $treatment = $row['reason'];

        // Enviar segundo recordatorio (2 horas antes)
        if (!empty($patient_phone)) {
            $text = 'Recordatorio: Su cita es en 2 horas a las ' . $patient_time . '. ¡No olvide usar mascarilla!';
            sendTextMessage($patient_name, $patient_phone, $text);
        }

        if (!empty($patient_email)) {
            sendEmail($patient_name, $patient_email, $patient_date, $patient_time, $patient_phone, $treatment, $patient_date, 'System Name');
        }

        // Marcar como enviado el segundo recordatorio (cambiar reminder_sent a 2)
        $update_sql = "UPDATE tblappointment SET reminder_sent = 2 WHERE id = " . $row['id'];
        mysqli_query($conn, $update_sql);
    }
}

// Ejecutar recordatorios
sendReminders();

logCronOutput("Cron job ejecutado a las: " . date('Y-m-d H:i:s'));
?>
