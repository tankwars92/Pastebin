<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['paste'])) {
    $poster = clean_input(isset($_POST['poster']) ? $_POST['poster'] : 'Anonymous');
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $remember = isset($_POST['remember']) ? true : false;
    $language = isset($_POST['language']) ? clean_input($_POST['language']) : 'text';
    $is_private = isset($_POST['private']) ? 1 : 0;
    
    if (empty($code)) {
        header('Location: error.php?error=empty');
        exit;
    }
    
    if (strlen($code) > 65535) {
        header('Location: error.php?error=toolong');
        exit;
    }
    
    if (strlen($poster) > 64) {
        $poster = substr($poster, 0, 64);
    }
    
    $ip_address = get_client_ip();
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
    
    $stmt = $connection->prepare("INSERT INTO pastes (poster, code, ip_address, user_agent, language, is_private) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute(array($poster, $code, $ip_address, $user_agent, $language, $is_private));
    
    if ($result) {
        $paste_id = $connection->lastInsertId();
        
        $today = date('Y-m-d');
        safe_query("INSERT OR REPLACE INTO stats (date, pastes_count) VALUES ('$today', COALESCE((SELECT pastes_count FROM stats WHERE date = '$today'), 0) + 1)");
        
        if ($remember && !empty($poster)) {
            setcookie('pastebin_name', $poster, time() + (365 * 24 * 60 * 60), '/', '', false, true);
        }
        
        header("Location: paste.php?id=$paste_id");
        exit;
    } else {
        header('Location: error.php?error=db');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
