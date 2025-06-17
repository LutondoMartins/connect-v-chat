<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

$response = ['logged_in' => false];

if (isset($_SESSION['user_id'])) {
    require_once 'config.php';
    try {
        $stmt = $pdo->prepare('SELECT username, profile_pic FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $response = [
                'logged_in' => true,
                'username' => $user['username'],
                'avatar' => $user['profile_pic'] ? 'uploads/' . $user['profile_pic'] : null
            ];
        }
    } catch (PDOException $e) {
        $response['error'] = 'Erro no servidor';
    }
}

echo json_encode($response);
exit;
?>