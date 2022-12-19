<?php
require_once(__DIR__.'/get_settings.php');
class etrade{
	private $conn;

	public function __construct($conn){
		$this->conn = $conn;
	}


	public function auth_token($code){
		global $memcache;
		$host = "https://etws.etrade.com/oauth/request_token";

		$headers = array('OAuth realm:',
						 'oauth_callback:oob',
						 'oauth_signature:FjoSQaFDKEDK1FJazlY3xArNflk%3D',
						 'oauth_nonce:LTg2ODUzOTQ5MTEzMTY3MzQwMzE%3D',
						 'oauth_signature_method:HMAC-SHA1',
						 'oauth_consumer_key:282683cc9e4b8fc81dea6bc687d46758',
						 'oauth_timestamp:1273254425');
		$post_fields = array(
							'grant_type'=>'authorization_code',
							'refresh_token'=>'',
							'access_type'=>'offline',
							'code'=>$code,
							'client_id'=>TDA_AUTH_CLIENT_ID,
							'redirect_uri'=>TDA_AUTH_CALLBACK_URI);
		$response = json_decode(cURL($host,'POST',$headers,$post_fields),true); # Response is in json
		if(!$response){
			return false;
		}
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['access_token']."' WHERE variable='tda_access_token' LIMIT 1");
		$this->conn->query("UPDATE `twitter_sniper`.`settings` SET `value` = '".$response['refresh_token']."' WHERE variable='tda_refresh_token' LIMIT 1");
		$memcache->delete('optionsniper_settings');
		return true;
	}
}
?>