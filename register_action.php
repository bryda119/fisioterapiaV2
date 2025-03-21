<?php
session_start();
include('admin/config/dbconn.php');
include('superglobal.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

date_default_timezone_set("America/Guayaquil");

function sendmail_verify($fname, $email, $verify_token, $system_name, $mail_link, $mail_host, $mail_username, $mail_password)
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mail_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mail_username;
    $mail->Password   = $mail_password;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom($mail_username, $fname);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Verificación de correo electrónico de ' . $system_name;

    $email_template = "	
            <h2> Te has registrado en $system_name </h2> 	
            <p> Por favor, haz clic en el siguiente enlace para verificar tu dirección de correo electrónico y completar el proceso de registro.</p>	
            <p> Serás redirigido automáticamente a la página de inicio de sesión.</p>	
            <p>Haz clic a continuación para activar tu cuenta:</p>	
            <a href='$mail_link/verify_email.php?token=$verify_token'> Haz clic aquí </a>	
            ";
    $mail->Body = $email_template;
    try {
        $mail->send();
        echo "El mensaje ha sido enviado.";
    } catch (Exception $e) {
        echo "El mensaje no pudo ser enviado. Error del Mailer: {$mail->ErrorInfo}";
    }
}
if (isset($_POST['register_btn'])) {
    $fname  = $_POST['fname'];
    $lname  = $_POST['lname'];
    $address = $_POST['address'];
    $dob = $_POST['birthday'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $regdate = date('Y-m-d H:i:s');
    $verify_token = md5(rand());

    $image = $_FILES['patient_image']['name'];

    if ($password == $confirmPassword) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $checkemail = "SELECT * FROM users WHERE email='$email'";
        $checkemail_run = mysqli_query($conn, $checkemail);

        if (mysqli_num_rows($checkemail_run) > 0) {
            $_SESSION['error'] = "El correo electrónico ya existe.";
            header('Location:register.php');
        } else {
            if ($image != NULL) {
                $allowed_file_format = array('jpg', 'png', 'jpeg');

                $image_extension = pathinfo($image, PATHINFO_EXTENSION);


                if (!in_array($image_extension, $allowed_file_format)) {
                    $_SESSION['error'] = "Sube un archivo válido. jpg, png";
                    header('Location:register.php');
                } else if (($_FILES['patient_image']['size'] > 5000000)) {
                    $_SESSION['error'] = "El tamaño del archivo excede los 5MB.";
                    header('Location:register.php');
                } else {
                    $filename = time() . '.' . $image_extension;
                    move_uploaded_file($_FILES['patient_image']['tmp_name'], 'upload/patients/' . $filename);
                }
            } else {
                $character = $_POST["fname"][0];
                $path = time() . ".png";
                $imagecreate = imagecreate(200, 200);
                $red = rand(0, 255);
                $green = rand(0, 255);
                $blue = rand(0, 255);
                imagecolorallocate($imagecreate, 230, 230, 230);
                $textcolor = imagecolorallocate($imagecreate, $red, $green, $blue);
                imagettftext($imagecreate, 100, 0, 55, 150, $textcolor, 'admin/font/arial.ttf', $character);
                imagepng($imagecreate, 'upload/patients/' . $path);
                imagedestroy($imagecreate);
                $filename = $path;
            }

            if ($_SESSION['error'] == '') {
                $sql = "INSERT INTO tblpatient (fname,lname,address,dob,gender,phone,email,image,password,role,verify_token,created_at)
                    VALUES ('$fname','$lname','$address','$dob','$gender','$phone','$email','$filename','$hash','patient','$verify_token','$regdate')";
                $patient_query_run = mysqli_query($conn, $sql);
                if ($patient_query_run) {
                    sendmail_verify("$fname", "$email", "$verify_token", $system_name, $mail_link, $mail_host, $mail_username, $mail_password);
                    $_SESSION['info'] = "Hemos enviado un correo electrónico a <b>$email</b>, por favor revisa tu bandeja de entrada y haz clic en el enlace para verificar.";
                    header('Location:login.php');
                } else {
                    $_SESSION['warning'] = "El registro ha fallado.";
                    header('Location:register.php');
                }
            }
        }
    } else {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header('Location:register.php');
    }
}
