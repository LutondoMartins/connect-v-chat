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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de requisição inválido';
    echo json_encode($response);
    exit;
}

// Get input data
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

// Validate input
if (empty($email) || empty($password)) {
    $response['message'] = 'Email e senha são obrigatórios';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email inválido';
    echo json_encode($response);
    exit;
}

try {
    // Query to find user by email
    $stmt = $pdo->prepare('SELECT id, username, password, status FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password'])) {
        // Update user status to online and last_seen
        $stmt = $pdo->prepare('UPDATE users SET status = ?, last_seen = NOW() WHERE id = ?');
        $stmt->execute(['online', $user['id']]);

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $email;

        $response['success'] = true;
        $response['message'] = 'Login bem-sucedido';
    } else {
        $response['message'] = 'Email ou senha incorretos';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Erro inesperado: ' . $e->getMessage();
}

// Output the JSON response
echo json_encode($response);
exit;
?>