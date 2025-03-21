<?php
// Incluye el archivo de conexión a la base de datos
include('../../config/dbconn.php');

// Inicializa la variable de salida
$output = '';

// Verifica si se ha enviado una consulta de búsqueda
if (isset($_POST['query'])) {
    // Obtiene el valor de búsqueda
    $search = $_POST['query'];

    // Escapa los caracteres especiales para evitar inyección de SQL
    $search = mysqli_real_escape_string($conn, $search);

    // Consulta SQL para buscar pacientes en tblpatient y relacionarlos con citas
    $sql = "SELECT p.id, CONCAT(p.fname, ' ', p.lname) AS full_name
            FROM tblpatient p
            WHERE CONCAT(p.fname, ' ', p.lname) LIKE '%$search%'
            LIMIT 10";

    // Ejecuta la consulta
    $result = mysqli_query($conn, $sql);

    // Verifica si se encontraron resultados
    if (mysqli_num_rows($result) > 0) {
        // Construye la lista de resultados
        $output .= '<ul class="list-group">';
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<li class="list-group-item patient-item" data-patient-id="' . $row['id'] . '">' . $row['full_name'] . '</li>';
        }
        $output .= '</ul>';
    } else {
        // Si no se encontraron resultados, muestra un mensaje indicando que no se encontraron pacientes
        $output .= '<p>No se encontraron pacientes.</p>';
    }
} else {
    // Si no se proporciona ninguna consulta, muestra un mensaje indicando que no se proporcionó una consulta
    $output .= '<p>No se proporcionó una consulta de búsqueda.</p>';
}

// Imprime la salida
echo $output;
?>
