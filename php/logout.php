<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once 'config.php';

try {
    if (isset($_SESSION['user_id'])) {
        // Atualizar status para offline
        $stmt = $pdo->prepare('UPDATE users SET status = ?, last_seen = NOW() WHERE id = ?');
        $stmt->execute(['offline', $_SESSION['user_id']]);
    }
    // Destruir sessão
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout bem-sucedido']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>