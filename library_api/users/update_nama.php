<?php
// C:\xampp\htdocs\project\library_apmob\library_api\users\update_nama.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OK"]);
    exit;
}

// Pastikan hanya POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Hindari warning/notice merusak output JSON
error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . "/../config/db.php";

try {
    // Terima JSON dari Expo (Content-Type: application/json)
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // Fallback jika suatu saat kirim via form-data
    if (!is_array($data))
        $data = $_POST;

    $user_id = (int) ($data["user_id"] ?? 0);
    $name = trim($data["name"] ?? "");

    if ($user_id <= 0 || $name === "") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "user_id dan name wajib diisi"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $user_id]);

    if ($stmt->rowCount() === 0) {
        // bisa karena user_id tidak ada, atau namanya sama persis
        echo json_encode([
            "success" => false,
            "message" => "Tidak ada perubahan (user tidak ditemukan / nama sama)."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Nama berhasil diperbarui.",
        "data" => [
            "user_id" => $user_id,
            "name" => $name
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error",
        "error" => $e->getMessage()
    ]);
}
