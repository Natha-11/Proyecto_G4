<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, nombre, password FROM clientes WHERE email = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['nombre'];
                header("Location: index.php");
                exit();
            } else {
                header("Location: login.php?error=wrongpass");
                exit();
            }
        } else {
            header("Location: login.php?error=notfound");
            exit();
        }
        $stmt->close();
    } else {
        echo "Error: " . $conexion->error;
    }
    $conexion->close();
}
?>