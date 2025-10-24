<?php
$db_file = 'pastebin.db';

try {
    $connection = new PDO('sqlite:' . $db_file);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $tables = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pastes'")->fetchAll();
    
    if (empty($tables)) {
        $connection->exec("
            CREATE TABLE pastes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                poster VARCHAR(64) NOT NULL DEFAULT '',
                code TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                language VARCHAR(20) DEFAULT 'text',
                is_private INTEGER DEFAULT 0,
                view_count INTEGER DEFAULT 0
            )
        ");
        
        $connection->exec("CREATE INDEX idx_created ON pastes(created_at)");
        $connection->exec("CREATE INDEX idx_poster ON pastes(poster)");
        $connection->exec("CREATE INDEX idx_ip ON pastes(ip_address)");
        $connection->exec("CREATE INDEX idx_private ON pastes(is_private)");
        
        $connection->exec("
            CREATE TABLE stats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date DATE NOT NULL UNIQUE,
                pastes_count INTEGER DEFAULT 0,
                views_count INTEGER DEFAULT 0
            )
        ");
        
        $test_data = array(
            array('Anonymous', '<?php echo "Hello World!"; ?>', 'php'),
            array('TestUser', 'function test() { return "test"; }', 'javascript'),
            array('Dev', 'SELECT * FROM users WHERE active = 1;', 'sql'),
            array('Coder', '#!/bin/bash\necho "Hello from bash!"', 'bash'),
            array('WebDev', '<html><body><h1>Hello World!</h1></body></html>', 'html')
        );
        
        $stmt = $connection->prepare("INSERT INTO pastes (poster, code, language) VALUES (?, ?, ?)");
        foreach ($test_data as $data) {
            $stmt->execute($data);
        }
    }
    
} catch (PDOException $e) {
    die('Could not connect to database: ' . $e->getMessage());
}

function escape($data) {
    global $connection;
    return $connection->quote($data);
}

function safe_query($query) {
    global $connection;
    try {
        $result = $connection->query($query);
        return $result;
    } catch (PDOException $e) {
        error_log("SQLite Error: " . $e->getMessage() . " Query: " . $query);
        return false;
    }
}

function get_next_id() {
    $result = safe_query("SELECT MAX(id) as max_id FROM pastes");
    if (!$result) return 1;
    
    $row = $result->fetch();
    return ($row['max_id'] ? $row['max_id'] : 0) + 1;
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

function get_client_ip() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }
    return '0.0.0.0';
}

function time_ago($timestamp) {
    $time_ago = time() - strtotime($timestamp);
    
    if ($time_ago < 60) {
        return 'just now';
    } elseif ($time_ago < 3600) {
        $minutes = floor($time_ago / 60);
        return $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_ago < 86400) {
        $hours = floor($time_ago / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($time_ago / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}

register_shutdown_function(function() {
    global $connection;
    if ($connection) {
        $connection = null;
    }
});
?>
