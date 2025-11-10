<?php
require 'db.php';

// Ambil method HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Ambil path (misalnya /users atau /users/3)
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

// Routing sederhana
if ($request[0] !== 'users') {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint tidak ditemukan"]);
    exit;
}

$id = $request[1] ?? null; // Jika ada ID

switch ($method) {
    // ---------------- CREATE ----------------
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "User berhasil ditambahkan", "id" => $stmt->insert_id]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Gagal menambah user", "error" => $stmt->error]);
        }
        break;


    // ---------------- READ ----------------
    case 'GET':
        if ($id) {
            // GET by ID
            $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "User tidak ditemukan"]);
            }
        } else {
            // GET all
            $result = $conn->query("SELECT id, username, email FROM users");
            $users = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($users);
        }
        break;


    // ---------------- UPDATE ----------------
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID user dibutuhkan"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $password, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User berhasil diperbarui"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User tidak ditemukan atau tidak ada perubahan"]);
        }
        break;


    // ---------------- DELETE ----------------
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID user dibutuhkan"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["message" => "User berhasil dihapus"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User tidak ditemukan"]);
        }
        break;


    default:
        http_response_code(405);
        echo json_encode(["message" => "Method tidak diizinkan"]);
        break;
}
?>
