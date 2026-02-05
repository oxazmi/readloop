<?php
require_once __DIR__ . "/../config/db.php";

$id = (int) ($_GET["id"] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "id wajib"]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Data tidak ditemukan"]);
        exit;
    }

    echo json_encode(["success" => true, "data" => $row]);
    exit;
}

if ($method === "PUT") {
    $body = json_decode(file_get_contents("php://input"), true);

    $title = trim($body["title"] ?? "");
    $author = trim($body["author"] ?? "");
    $category = trim($body["category"] ?? "");
    $status = trim($body["status"] ?? "want");
    $rating = isset($body["rating"]) ? (int) $body["rating"] : null;
    $note = trim($body["note"] ?? "");
    $cover_url = trim($body["cover_url"] ?? "");
    $file_url = trim($body["file_url"] ?? "");

    if ($title === "") {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "title wajib"]);
        exit;
    }

    $allowedStatus = ["want", "reading", "finished"];
    if (!in_array($status, $allowedStatus, true))
        $status = "want";

    if ($rating !== null && ($rating < 1 || $rating > 5))
        $rating = null;

    $author = $author === "" ? null : $author;
    $category = $category === "" ? null : $category;
    $note = $note === "" ? null : $note;
    $cover_url = $cover_url === "" ? null : $cover_url;
    $file_url = $file_url === "" ? null : $file_url;

    try {
        $stmt = $pdo->prepare("
      UPDATE books
      SET title=?, author=?, category=?, status=?, rating=?, note=?, cover_url=?, file_url=?
      WHERE id=?
    ");
        $stmt->execute([$title, $author, $category, $status, $rating, $note, $cover_url, $file_url, $id]);

        echo json_encode(["success" => true, "message" => "Berhasil edit"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
    }
    exit;
}

if ($method === "DELETE") {
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id=?");
        $stmt->execute([$id]);

        echo json_encode(["success" => true, "message" => "Berhasil hapus"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed"]);
