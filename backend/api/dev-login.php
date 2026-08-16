<?php
// backend/api/dev-login.php
session_start();

// Simulamos que el usuario inició sesión con el ID que le pasemos por la URL
/*

URL para iniciar sesion como administrador: http://localhost:8000/api/dev-login.php?usuario_id=1

Codigo de consola para saltarse la autenticacion en el frontend (para desarrollo):

localStorage.setItem("eventia_authenticated", "1");
localStorage.setItem("eventia_user", JSON.stringify({id: 1, nombre: "Organizador Demo"}));
location.reload();

*/
/*

URL para iniciar sesion como estudiante: http://localhost:8000/api/dev-login.php?usuario_id=2

Codigo de consola para saltarse la autenticacion en el frontend (para desarrollo):

localStorage.setItem("eventia_user", JSON.stringify({id: 2, nombre: "Estudiante Demo"}));
location.reload();

*/

$_SESSION['usuario_id'] = $_GET['usuario_id'] ?? 1;

header('Content-Type: application/json');
echo json_encode([
    "success" => true, 
    "message" => "Sesion de desarrollo iniciada como ID " . $_SESSION['usuario_id']
]);