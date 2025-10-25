<?php
require_once 'config.php';

$paste_id = 0;
if (isset($_GET['id'])) {
    $paste_id = intval($_GET['id']);
} else {
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = trim($path, '/');
    if (is_numeric($path)) {
        $paste_id = intval($path);
    }
}

if ($paste_id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $connection->prepare("SELECT * FROM pastes WHERE id = ?");
$stmt->execute(array($paste_id));
$paste = $stmt->fetch();

if (!$paste) {
    header('Location: error.php?error=notfound');
    exit;
}

if (isset($_GET['raw']) && $_GET['raw'] == 1) {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="paste_' . $paste_id . '.txt"');
    echo $paste['code'];
    exit;
}

safe_query("UPDATE pastes SET view_count = view_count + 1 WHERE id = $paste_id");

$today = date('Y-m-d');
safe_query("INSERT OR REPLACE INTO stats (date, views_count) VALUES ('$today', COALESCE((SELECT views_count FROM stats WHERE date = '$today'), 0) + 1)");

$recent_pastes = array();
$recent_result = safe_query("SELECT id, poster, created_at, language FROM pastes WHERE is_private = 0 ORDER BY created_at DESC LIMIT 10");
if ($recent_result) {
    while ($row = $recent_result->fetch()) {
        $recent_pastes[] = $row;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Pastebin :: Pate #<?php echo $paste['id']; ?></title>
<link rel="stylesheet" type="text/css" media="screen" href="style.css">
<!--[if IE 5]>
<link rel="stylesheet" type="text/css" href="ie5.css">
<link rel="stylesheet" type="text/css" href="ie5_hacks.css">
<![endif]-->
</head>

<body>
<p style="display: none;">This site is developed to XHTML and CSS2 W3C standards.  
If you see this paragraph, your browser does not support those standards and you 
need to upgrade.  Visit <a href="http://www.webstandards.org/upgrade/" target="_blank">WaSP</a>
for a variety of options.</p>

<div id="titlebar">php pastebin - collaborative irc debugging <a href="https://github.com/tankwars92/Pastebin/" class="alt">view php source</a>
</div>

<div id="menu">
<h1>Recent Posts</h1>
<ul>
<?php foreach ($recent_pastes as $recent_paste): ?>
<li><a href="paste.php?id=<?php echo $recent_paste['id']; ?>"<?php echo ($recent_paste['id'] == $paste_id) ? ' class="highlight"' : ''; ?>><?php echo htmlspecialchars($recent_paste['poster']); ?></a><br>
<?php 
$time_ago = time() - strtotime($recent_paste['created_at']);

if ($time_ago < 0) {
    echo 'just now';
} elseif ($time_ago < 60) {
    echo 'just now';
} elseif ($time_ago < 3600) {
    $minutes = floor($time_ago / 60);
    echo $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' ago';
} elseif ($time_ago < 86400) {
    $hours = floor($time_ago / 3600);
    echo $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
} else {
    $days = floor($time_ago / 86400);
    echo $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}
?>
</li>
<?php endforeach; ?>

<li><a href="index.php">Make new post</a></li>
</ul>


<p>
    <a href="http://validator.w3.org/check/referer"><img src="valid-xhtml10.png" alt="Valid XHTML 1.0!" height="31" width="88" border="0"></a>
</p>

</div>

<div id="content">
<h1>Paste #<?php echo $paste['id']; ?></h1>

<div class="paste-meta">
<strong>Posted by:</strong> <?php echo htmlspecialchars($paste['poster']); ?><br>
<strong>Posted on:</strong> <?php echo date('Y-m-d H:i:s', strtotime($paste['created_at'])); ?><br>
<strong>Age:</strong> <?php 
$time_ago = time() - strtotime($paste['created_at']);
if ($time_ago < 0) {
    echo 'just now';
} elseif ($time_ago < 60) {
    echo 'just now';
} elseif ($time_ago < 3600) {
    $minutes = floor($time_ago / 60);
    echo $minutes . ' min' . ($minutes > 1 ? 's' : '') . ' ago';
} elseif ($time_ago < 86400) {
    $hours = floor($time_ago / 3600);
    echo $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
} else {
    $days = floor($time_ago / 86400);
    echo $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}
?><br>
<strong>Views:</strong> <?php echo $paste['view_count']; ?><br>
</div>

<pre><?php echo htmlspecialchars($paste['code']); ?></pre>



<p>
    <a href="paste.php?id=<?php echo $paste['id']; ?>&raw=1">Download raw</a> |
    <a href="index.php">Create new paste</a>
</p>
  
</div>
<center>
    &copy; BitByByte, 2025.<br>
    <img src="http://downgrade.hoho.ws/services/counter/index.php?id=12" alt="Downgrade Counter"> 
    <script src="//downgrade.hoho.ws/services/ring/ring.php"></script> 
</center>
</body>
</html>
