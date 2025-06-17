<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
    exit;
}

require_once 'config.php';

$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id) {
    echo json_encode(['success' => false, 'message' => 'ID do chat não fornecido']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT u.id, u.username AS name, u.profile_pic AS avatar
        FROM chat_members cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.chat_id = ?
    ');
    $stmt->execute([$chat_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Adicionar prefixo 'Uploads/' ao avatar
    $members = array_map(function($member) {
        if ($member['avatar']) {
            $member['avatar'] = 'Uploads/' . $member['avatar'];
        }
        return $member;
    }, $members);

    echo json_encode(['success' => true, 'members' => $members]);
} catch (PDOException $e) {
    error_log("Erro em get_group_members: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
}

exit;
?>