<?php
namespace App\Helper;

class NotificationHelper
{
    public static function notification($json_data,$type)
    {
        $key = "";
        if($type == "seller"){
            $key = env('SELLER_SERVER_KEY');
        }else if($type == "buyer"){
            $key = env('BUYER_SERVER_KEY');
        }else if($type == "broker"){
            $key = env('BROKER_SERVER_KEY');
        }

        $data = json_encode($json_data);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = $key;
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        // if ($result === FALSE) {
			
            // $response['status'] = 404;
			// $response['message'] = 'Success';
            // $response['data'] = (object)array();
            // return response($response, 200);
            // die('Oops! FCM Send Error: ' . curl_error($ch));
        // }
        curl_close($ch);

    }

    public static function send_otp($mobile_no,$message){

        $auth_key = env('AUTH_KEY');
        $route_id = env('ROUTE_ID');
        $sender_id = env('SENDER_ID');
        $api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=".$auth_key."&routeId=".$route_id."&senderId=".$sender_id."&mobileNos='".$mobile_no."'&message=" . urlencode($message);
		$sms = file_get_contents($api);
        return true;
    }
}
