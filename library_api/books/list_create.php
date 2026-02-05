<?php
require_once __DIR__ . "/../config/db.php";

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $user_id = (int) ($_GET["user_id"] ?? 0);
    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "user_id wajib"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM books WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $rows]);
    exit;
}

if ($method === "POST") {
    $body = json_decode(file_get_contents("php://input"), true);

    $user_id = (int) ($body["user_id"] ?? 0);
    $title = trim($body["title"] ?? "");
    $author = trim($body["author"] ?? "");
    $category = trim($body["category"] ?? "");
    $status = trim($body["status"] ?? "want");
    $rating = isset($body["rating"]) ? (int) $body["rating"] : null;
    $note = trim($body["note"] ?? "");
    $cover_url = trim($body["cover_url"] ?? "");
    $file_url = trim($body["file_url"] ?? "");

    if ($user_id <= 0 || $title === "") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "user_id dan title wajib"]);
        exit;
    }

    $allowedStatus = ["want", "reading", "finished"];
    if (!in_array($status, $allowedStatus, true))
        $status = "want";

    if ($rating !== null && ($rating < 1 || $rating > 5))
        $rating = null;

    // kosong -> NULL
    $author = $author === "" ? null : $author;
    $category = $category === "" ? null : $category;
    $note = $note === "" ? null : $note;
    $cover_url = $cover_url === "" ? null : $cover_url;
    $file_url = $file_url === "" ? null : $file_url;

    try {
        $stmt = $pdo->prepare("
      INSERT INTO books (user_id, title, author, category, status, rating, note, cover_url, file_url)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
        $stmt->execute([$user_id, $title, $author, $category, $status, $rating, $note, $cover_url, $file_url]);

        echo json_encode(["success" => true, "message" => "Berhasil tambah", "id" => (int) $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed"]);
