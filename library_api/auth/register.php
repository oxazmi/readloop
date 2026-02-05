<?php
require_once __DIR__ . "/../config/db.php";

$body = json_decode(file_get_contents("php://input"), true);
$name = trim($body["name"] ?? "");
$username = trim($body["username"] ?? "");
$password = $body["password"] ?? "";

if ($name === "" || $username === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "name/username/password wajib"]);
    exit;
}

try {
    // cek username unik
    $cek = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $cek->execute([$username]);
    if ($cek->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Username sudah terpakai"]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users(name, username, password_hash) VALUES(?,?,?)");
    $stmt->execute([$name, $username, $hash]);

    echo json_encode(["success" => true, "message" => "Register berhasil"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
}
