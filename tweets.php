<?php

$redirect = "https://" . $_SERVER['HTTP_HOST'] . "/optionSniper/tweets.php";
header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $redirect);
exit();

?>