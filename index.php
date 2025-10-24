<?php
require_once 'config.php';

$recent_pastes = array();
$result = safe_query("SELECT id, poster, created_at, language FROM pastes WHERE is_private = 0 ORDER BY created_at DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch()) {
        $recent_pastes[] = $row;
    }
}

$remembered_name = isset($_COOKIE['pastebin_name']) ? $_COOKIE['pastebin_name'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Pastebin :: Main Page</title>
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

<div id="titlebar">php pastebin - collaborative irc debugging
</div>

<div id="menu">
<h1>Recent Posts</h1>
<ul>
<?php foreach ($recent_pastes as $paste): ?>
<li><a href="paste.php?id=<?php echo $paste['id']; ?>"><?php echo htmlspecialchars($paste['poster']); ?></a><br>
<?php 
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
<h1>New posting</h1>
<form name="editor" method="post" action="submit.php">
<input type="hidden" name="parent_pid" value="">
<b>Name</b><br>
<input type="text" class="text" maxlength="64" size="20" name="poster" value="<?php echo htmlspecialchars($remembered_name); ?>">
<input type="submit" class="submit" name="paste" value="Send">
<br>
<input type="checkbox" name="remember" value="1" <?php echo $remembered_name ? 'checked' : ''; ?>>Remember my name in a cookie
<br><br>

<b>Code:</b> To ensure legibility, keep your code lines under 80 characters long.<br>
Include comments to indicate what you need feedback on.<br>
<textarea class="codeedit" name="code" cols="80" rows="10"></textarea>

</form>
  
</div>
<center>
    &copy; BitByByte, 2025.<br>
    <img src="http://downgrade.hoho.ws/services/counter/index.php?id=12" alt="Downgrade Counter"> 
    <script src="//downgrade.hoho.ws/services/ring/ring.php"></script> 
</center>


</body>
</html>
