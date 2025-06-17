<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once 'config.php';

try {
    $user_id = $_SESSION['user_id'];
    $query = 'SELECT id, username AS name, profile_pic AS avatar FROM users WHERE id != ?';
    $params = [$user_id];

    // Excluir membros de um grupo específico, se fornecido
    if (isset($_GET['exclude_group'])) {
        $group_id = filter_var($_GET['exclude_group'], FILTER_VALIDATE_INT);
        $query .= ' AND id NOT IN (SELECT user_id FROM chat_members WHERE chat_id = ?)';
        $params[] = $group_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adicionar prefixo 'Uploads/' ao avatar e fallback
    $users = array_map(function($user) {
        $user['avatar'] = $user['avatar'] ? 'Uploads/' . $user['avatar'] : 'https://via.placeholder.com/48';
        return $user;
    }, $users);

    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    error_log("Erro em get_users: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>