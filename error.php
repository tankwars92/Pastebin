<?php
$error = isset($_GET['error']) ? $_GET['error'] : 'unknown';

$error_messages = array(
    'empty' => 'Code cannot be empty',
    'db' => 'Database error occurred',
    'notfound' => 'Paste not found',
    'toolong' => 'Code is too long (maximum 65535 characters)',
    'invalid' => 'Invalid request'
);

$error_message = isset($error_messages[$error]) ? $error_messages[$error] : 'Unknown error occurred';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Pastebin :: Error!</title>
<link rel="stylesheet" type="text/css" media="screen" href="style.css">
<!--[if IE 5]>
<link rel="stylesheet" type="text/css" href="ie5.css">
<link rel="stylesheet" type="text/css" href="ie5_hacks.css">
<![endif]-->
</head>

<body>
<div id="titlebar">php pastebin - collaborative irc debugging <a href="https://github.com/tankwars92/Pastebin/" class="alt">view php source</a>
</div>

<div id="content">
<h1>Error</h1>
<p><?php echo htmlspecialchars($error_message); ?></p>
<p><a href="index.php">Back to pastebin</a></p>
</div>
<center>
    &copy; BitByByte, 2025.<br>
    <img src="http://downgrade.hoho.ws/services/counter/index.php?id=12" alt="Downgrade Counter"> 
    <script src="//downgrade.hoho.ws/services/ring/ring.php"></script> 
</center>
</body>
</html>
