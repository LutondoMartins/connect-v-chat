<?php
// Start the session
session_start();

// Include database configuration
require_once 'config.php';

// Set headers for JSON response and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Credentials: true');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário já está logado';
    echo json_encode($response);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de requisição inválido';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

// Get and sanitize input data
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
$confirmPassword = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
$profilePic = isset($_FILES['profile_pic']) ? $_FILES['profile_pic'] : null;

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    $response['message'] = 'Todos os campos obrigatórios devem ser preenchidos';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email inválido';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

if ($password !== $confirmPassword) {
    $response['message'] = 'As senhas não coincidem';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

if (strlen($password) < 8) {
    $response['message'] = 'A senha deve ter no mínimo 8 caracteres';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

if (strlen($username) > 50) {
    $response['message'] = 'O nome de usuário deve ter no máximo 50 caracteres';
    $_SESSION['register_error'] = $response['message'];
    echo json_encode($response);
    exit;
}

// Validate profile picture if provided
$profilePicPath = null;
if ($profilePic && $profilePic['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($profilePic['type'], $allowedTypes)) {
        $response['message'] = 'A foto deve ser um arquivo JPEG ou PNG';
        $_SESSION['register_error'] = $response['message'];
        echo json_encode($response);
        exit;
    }

    if ($profilePic['size'] > $maxFileSize) {
        $response['message'] = 'A foto não pode exceder 2MB';
        $_SESSION['register_error'] = $response['message'];
        echo json_encode($response);
        exit;
    }

    // Generate unique filename and save the file
    $uploadDir = '../uploads/profile_pics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileExtension = pathinfo($profilePic['name'], PATHINFO_EXTENSION);
    $uniqueFileName = uniqid('profile_') . '.' . $fileExtension;
    $profilePicPath = $uploadDir . $uniqueFileName;

    if (!move_uploaded_file($profilePic['tmp_name'], $profilePicPath)) {
        $response['message'] = 'Erro ao fazer upload da foto';
        $_SESSION['register_error'] = $response['message'];
        echo json_encode($response);
        exit;
    }
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $response['message'] = 'Nome de usuário já registrado';
        $_SESSION['register_error'] = $response['message'];
        // Remove uploaded file if exists
        if ($profilePicPath && file_exists($profilePicPath)) {
            unlink($profilePicPath);
        }
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $response['message'] = 'Email já registrado';
        $_SESSION['register_error'] = $response['message'];
        // Remove uploaded file if exists
        if ($profilePicPath && file_exists($profilePicPath)) {
            unlink($profilePicPath);
        }
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, profile_pic, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$username, $email, $hashedPassword, $profilePicPath, 'offline']);

    $response['success'] = true;
    $response['message'] = 'Registro bem-sucedido! Faça login para continuar.';
} catch (PDOException $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
    $_SESSION['register_error'] = $response['message'];
    // Remove uploaded file if exists
    if ($profilePicPath && file_exists($profilePicPath)) {
        unlink($profilePicPath);
    }
} catch (Exception $e) {
    $response['message'] = 'Erro inesperado: ' . $e->getMessage();
    $_SESSION['register_error'] = $response['message'];
    // Remove uploaded file if exists
    if ($profilePicPath && file_exists($profilePicPath)) {
        unlink($profilePicPath);
    }
}

// Output the JSON response
echo json_encode($response);
exit;
?>