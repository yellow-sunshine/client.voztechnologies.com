<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
 
<html>
<head>
        <title>Purge Varnish cache</title>
</head>
 
<style type="text/css">
  body {
        font-size: 10px;
  }
  h1 {
        font-weight: bold;
        color: #000000;
        border-bottom: 1px solid #C6EC8C;
        margin-bottom: 2em;
  }
  label {
        font-size: 160%;
        float: left;
        text-align: right;
        margin-right: 0.5em;
        display: block
  }
  input[type="text"] {
        width: 500px;
  }
  .submit input {
        margin-left: 0em;
        margin-bottom: 1em;
  }
</style>
 
<body>
 
  <h1>Makes Varnish purge the supplied URL from its cache</h1>
 
  <form action="" method="post">
        <p><label>URL</label> <input type="text" name="url"></p>
        <p><label>HOST</label> <input type="text" name="host"></p>
        <p class="submit"><input value="Submit" type="submit"></p>
  </form>
<?php
if($isset($_POST['submit'])){
	# Try with Curl first
	$curl = curl_init($_POST["url"]);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE");
	$response = curl_exec($curl);
	
	$curl = curl_init($_POST["url"]);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE");
	$response = curl_exec($curl);


	# Then try with php socks
	$url = $_POST["url"];
	$host = $_POST["host"];

	$ip = "127.0.0.1";
	$port = "6082";
	
	$timeout = 1;
	$verbose = 1;
	
	# inits
	$sock = fsockopen ($ip,$port,$errno, $errstr,$timeout);
	if (!$sock) { echo "connections failed $errno $errstr"; exit; }
	
	if ( !($url || $host) ) { echo "No params"; exit; }
	
	stream_set_timeout($sock,$timeout);
	
	$pcommand = "purge";
	# Send command
	$pcommand .= ".hash $url#$host#";
	
	put ($pcommand);
	put ("quit");
	
	fclose ($sock);
	
	function readit() {
		global $sock,$verbose;
		if (!$verbose) { return; }
			while ($sockstr = fgets($sock,1024)) {
				$str .= "rcv: " . $sockstr . "<br>";
			}
		if ($verbose) { echo "$str\n"; }
	
	}
	
	function put($str) {
		global $sock,$verbose;
		fwrite ($sock, $str . "\r\n");
		if ($verbose) { echo "send: $str <br>\n"; }
		readit();	
	}
}
?> 
</body>
</html>