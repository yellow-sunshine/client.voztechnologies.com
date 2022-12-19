<?php
require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;
function send_sms($phone, $message){
    # Add the + to the phone number we are sending to if it does not exist
    if($phone[0]!='+'){
        $phone = '+'.$phone;
    }

    # Make sure the Twilio account phone number has a + on it
    if(TWILIO_PHONE[0]!='+'){
        $twilio_number = '+'.TWILIO_PHONE;
    }else{
        $twilio_number = TWILIO_PHONE;
    }

    $client = new Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);

    $client->messages->create(
        $phone,
        array(
            'from' => $twilio_number,
            'body' => $message
        )
    );
}
?>