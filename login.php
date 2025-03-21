<?php
session_start();
include('includes/header.php');
include('admin/config/dbconn.php');
if (isset($_SESSION['auth'])) {
    if ($_SESSION['auth_role'] == "admin") {
        $_SESSION['status'] = "Ya has iniciado sesión";
        header('Location: admin/pages/dashboard');
        exit(0);
    } else if ($_SESSION['auth_role'] == "patient") {
        $_SESSION['status'] = "Ya has iniciado sesión";
        header('Location: patient/index.php');
        exit(0);
    } else if ($_SESSION['auth_role'] == "2") {
        $_SESSION['status'] = "Ya has iniciado sesión";
        header('Location: fisioterapeuta/index.php');
        exit(0);
    } else if ($_SESSION['auth_role'] == "3") {
        $_SESSION['status'] = "Ya has iniciado sesión";
        header('Location: staff/index.php');
        exit(0);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #0072ff, #00c6ff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
            margin: 0;
        }
        .login-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-box h3 {
            font-size: 24px;
            font-weight: bold;
            color: #0072ff;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #0072ff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056cc;
        }
        .form-control {
            border-radius: 5px;
            box-shadow: none;
        }
        .input-group-text {
            background: #0072ff;
            color: #fff;
            border: none;
        }
        .input-group-text:hover {
            background: #0056cc;
        }
        a {
            color: #0072ff;
            text-decoration: none;
        }
        a:hover {
            color: #0056cc;
            text-decoration: underline;
        }
        .alert {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <?php
        if (isset($_SESSION['auth_status'])) {
        ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['auth_status']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php
            unset($_SESSION['auth_status']);
        }
        ?>
        <h3>Bienvenido a <?php echo $system_name; ?></h3>
        <p class="text-center">Por favor, inicia sesión para continuar</p>
        <?php include('admin/message.php'); ?>
        <form action="logincode.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Ingresa tu correo electrónico" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" name="login_btn" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>
        <div class="mt-3 text-center">
            <a href="password-reset.php">¿Olvidaste tu contraseña?</a>
        </div>
        <div class="mt-2 text-center">
            <a href="register.php">Crear una cuenta nueva</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
