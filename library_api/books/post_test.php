<?php
header("Content-Type: application/json; charset=UTF-8");
echo json_encode([
    "success" => true,
    "method" => $_SERVER["REQUEST_METHOD"],
    "msg" => "POST is allowed here"
]);
