<?php
session_start();
include('admin/config/dbconn.php');

if (isset($_POST['login_btn'])) {
    $error = '';
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_id = $row['id'];
            $user_fname = $row['name'];
            $user_email = $row['email'];
            $role_as = $row['role'];
            $user_status = $row['status'];

            if (password_verify($password, $row['password'])) {
                if ($user_status == '1') {
                    $_SESSION['auth'] = true;
                    $_SESSION['auth_role'] = "$role_as";
                    $_SESSION['auth_user'] = [
                        'user_id' => $user_id,
                        'user_fname' => $user_fname,
                        'user_email' => $user_email
                    ];

                    if ($_SESSION['auth_role'] == 'admin') {
                        header('Location: admin/pages/dashboard');
                        exit(0);
                    } else if ($_SESSION['auth_role'] == '3') {
                        header('Location: staff/index.php');
                        exit(0);
                    } else if ($_SESSION['auth_role'] == "2") {
                        header('Location: fisioterapeuta/index.php');
                        exit(0);
                    } else if ($_SESSION['auth_role'] == "patient") {
                        header('Location: patient/index.php');
                        exit(0);
                    } else {
                        $_SESSION['danger'] = "Acceso denegado";
                        header('Location: login.php');
                    }
                } else if ($user_status == '4') {
                    $_SESSION['danger'] = "Aún no has confirmado tu cuenta. Por favor revisa tu bandeja de entrada y verifica tu correo electrónico.";
                    header('Location: login.php');
                } else {
                    $_SESSION['danger'] = "Lo sentimos, tu cuenta está temporalmente deshabilitada. Por favor, contacta al administrador.";
                    header('Location: login.php');
                }
            } else {
                $_SESSION['error'] = "Correo electrónico o contraseña incorrectos";
                header('Location: login.php');
            }
        }
    } else {
        $_SESSION['error'] = "Correo electrónico o contraseña incorrectos";
        echo mysqli_error($conn);
        header('Location: login.php');
    }
} else {
    $_SESSION['error'] = "Acceso denegado";
    header('Location: login.php');
}
