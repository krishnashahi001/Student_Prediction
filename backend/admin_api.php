<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
session_start();
// Require admin session for API access
if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'forbidden']);
    exit;
}

$action = $_GET['action'] ?? '';
$db = get_db();

if ($action === 'list_users') {
    $sql = "SELECT rollno AS id, fullname AS name, email, contactno AS contact, stream, created_at FROM studentdata ORDER BY rollno DESC";
    $res = $db->query($sql);
    if (!$res) {
        echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $db->error]);
        exit;
    }
    $users = [];
    while ($row = $res->fetch_assoc()) $users[] = $row;
    echo json_encode(['status' => 'ok', 'users' => $users]);
    exit;
}

if ($action === 'user_details') {
    $id = $_GET['id'] ?? '';
    $stmt = $db->prepare("SELECT rollno AS id, fullname AS name, email, contactno, stream FROM studentdata WHERE rollno = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    echo json_encode(['status' => 'ok', 'user' => $user]);
    exit;
}

if ($action === 'user_predictions') {
    $id = $_GET['id'] ?? '';
    // Query predictions table if it exists, otherwise return empty
    $stmt = $db->prepare("SELECT id, input_data, result, created_at FROM predictions WHERE student_id = ? OR rollno = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->bind_param('ss', $id, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $preds = [];
    while ($row = $res->fetch_assoc()) $preds[] = $row;
    echo json_encode(['status' => 'ok', 'predictions' => $preds]);
    exit;
}

if ($action === 'get_streams') {
    $sql = "SELECT DISTINCT stream FROM studentdata WHERE stream IS NOT NULL AND stream != '' ORDER BY stream";
    $res = $db->query($sql);
    if (!$res) {
        echo json_encode(['status' => 'error', 'message' => 'Query failed']);
        exit;
    }
    $streams = [];
    while ($row = $res->fetch_assoc()) $streams[] = $row['stream'];
    echo json_encode(['status' => 'ok', 'streams' => $streams]);
    exit;
}

if ($action === 'students_by_stream') {
    $stream = $_GET['stream'] ?? '';
    if (empty($stream)) {
        echo json_encode(['status' => 'error', 'message' => 'stream parameter required']);
        exit;
    }
    $stmt = $db->prepare("SELECT rollno AS id, fullname AS name, email, contactno AS contact, stream FROM studentdata WHERE stream = ? ORDER BY fullname");
    $stmt->bind_param('s', $stream);
    $stmt->execute();
    $res = $stmt->get_result();
    $students = [];
    while ($row = $res->fetch_assoc()) $students[] = $row;
    echo json_encode(['status' => 'ok', 'students' => $students, 'stream' => $stream]);
    exit;
}

// allow editing a student's basic info
if ($action === 'update_user') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'invalid method']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        echo json_encode(['status' => 'error', 'message' => 'invalid payload']);
        exit;
    }
    $id = $input['id'] ?? '';
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $contact = $input['contact'] ?? null;
    $streamVal = $input['stream'] ?? null;
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'id is required']);
        exit;
    }
    $stmt = $db->prepare("UPDATE studentdata SET fullname = ?, email = ?, contactno = ?, stream = ? WHERE rollno = ?");
    $stmt->bind_param('sssss', $name, $email, $contact, $streamVal, $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'update failed: ' . $stmt->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'invalid action']);
