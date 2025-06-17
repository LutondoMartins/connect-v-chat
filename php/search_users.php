<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id']) || !isset($_GET['query'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $query = '%' . trim($_GET['query']) . '%';

    $stmt = $pdo->prepare('
        SELECT id, username AS name, profile_pic
        FROM users
        WHERE id != ? AND username LIKE ?
        LIMIT 10
    ');
    $stmt->execute([$user_id, $query]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adicionar prefixo 'Uploads/' ao avatar, se existir
    $users = array_map(function($user) {
        $user['avatar'] = $user['profile_pic'] ? 'Uploads/' . $user['profile_pic'] : null;
        unset($user['profile_pic']); // Remover campo desnecessário
        return $user;
    }, $users);

    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>