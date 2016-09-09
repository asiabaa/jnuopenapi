<?php

namespace dareninfo\jnu\openapi;

use Yii;
use linslin\yii2\curl;

class TokenHelper{

	public static function getToken($app_id,$app_secret,$api_url){

		$token = Yii::$app->params['openapi_token'];
		if($token){
			if(time() - $token['time'] < 2 * 60 * 55 * 1000){
				return $token;
			}
		}

		$now = time();
		$curl = new Curl();
		$token = array();
		$resp = $curl->get($api_url . '?appid=$app_id&$appsecret=$app_secret');
		if($resp['code'] == 200){
			$json = json_decode($resp['body']);
			if($json){
				if($json['result'] == 200){
					$token = array(
						'token' => $json['data'],
						'time' => $now,
					);
					Yii::$app->params['openapi_token'] = $token;
					return $token;
				}else{
					$token['error'] = 'app_secret认证错误';
				}
			}
		}else{
			$token['error'] = '网络错误';
		}

		return $token;
	}

}