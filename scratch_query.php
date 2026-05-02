<?php
include 'conexion.php';
$result = $conexion->query('SHOW FULL COLUMNS FROM clientes');
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
