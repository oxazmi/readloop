<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$user_id = (int) ($_POST["user_id"] ?? 0);
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "user_id wajib"]);
    exit;
}

if (!isset($_FILES["cover"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "File cover wajib (key: cover)"]);
    exit;
}

$file = $_FILES["cover"];
if ($file["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Upload error code: " . $file["error"]]);
    exit;
}

// Validasi size (maks 2MB)
$maxSize = 2 * 1024 * 1024;
if ($file["size"] > $maxSize) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Ukuran file terlalu besar (maks 2MB)"]);
    exit;
}

// Validasi ekstensi
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
$allowed = ["jpg", "jpeg", "png", "webp"];
if (!in_array($ext, $allowed, true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Format harus jpg/jpeg/png/webp"]);
    exit;
}

// Folder tujuan: /uploads/covers/{user_id}/
$baseDir = realpath(__DIR__ . "/.."); // menunjuk ke folder library_api
$targetDir = $baseDir . "/uploads/covers/" . $user_id;

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$newName = "cover_" . $user_id . "_" . time() . "." . $ext;
$targetPath = $targetDir . "/" . $newName;

if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Gagal menyimpan file"]);
    exit;
}

// Buat URL akses file
$scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$host = $_SERVER["HTTP_HOST"];
$apiBase = rtrim(dirname(dirname($_SERVER["SCRIPT_NAME"])), "/"); // .../library_api
$cover_url = $scheme . "://" . $host . $apiBase . "/uploads/covers/" . $user_id . "/" . $newName;

echo json_encode([
    "success" => true,
    "message" => "Upload cover berhasil",
    "cover_url" => $cover_url
]);
