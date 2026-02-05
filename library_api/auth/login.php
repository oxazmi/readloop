<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if ($username === "" || $password === "") {
    echo json_encode(["success" => false, "message" => "Username dan password wajib diisi."]);
    exit;
}

// cari user berdasarkan username
$stmt = $pdo->prepare("SELECT id, name, username, password_hash FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["success" => false, "message" => "Username belum terdaftar"]);
    exit;
}

// cek password
if (!password_verify($password, $user["password_hash"])) {
    echo json_encode(["success" => false, "message" => "Username atau password salah"]);
    exit;
}

// sukses
echo json_encode([
    "success" => true,
    "message" => "Login berhasil",
    "user" => [
        "id" => (int) $user["id"],
        "name" => $user["name"],
        "username" => $user["username"],
    ]
]);
