<?php

namespace dareninfo\jnu\openapi;

use Yii;
use linslin\yii2\curl\Curl;

/**
 * Access Token 帮助类
 */
class TokenHelper{

	/**
	 * AccessToken 请求接口
	 * @var string
	 */
	private $api_url = '/service/app/security/getAccessToken.html';
	/**
	 * 校园卡请求接口
	 * @var string
	 */
	private $school_card_url = '/service/other/account/getAccountInfo.html';
	/**
	 * cas 认证接口
	 * @var string
	 */
	private $cas_url = '/service/cas/security/login.html';
	/**
	 * 财务信息接口
	 * @var string
	 */
	private $student_tuition_url = '/service/other/finance/getStudentTuition.html';
	/**
	 * 学生宿舍信息接口
	 * @var string
	 */
	private $dorm_url = '/service/other/studentdorm/search.html';
	/**
	 * APP ID
	 * @var string
	 */
	private $app_id = '';
	/**
	 * APP secret
	 * @var string
	 */
	private $app_secret = '';
	/**
	 * API host
	 * @var string
	 */
	private $host = '';
	/**
	 * Curl 对象
	 * @var null
	 */
	private $curl = null;

	/**
	 * 构造函数
	 * @param [type] $appid     [description]
	 * @param [type] $appsecret [description]
	 * @param [type] $host      [description]
	 */
	function __construct($appid,$appsecret,$host){
		$this->app_id = $appid;
		$this->app_secret = $appsecret;
		$this->host = $host;
		$this->curl = new Curl();
	}

	/**
	 * 查询学生缴费信息
	 * @param  [type] $studentno [description]
	 * @return [type]            [description]
	 */
	public function getStudentTuition($studentno){
		return array('error' => '未实现');
	}

	/**
	 * 查询学生宿舍信息
	 * @param  [type] $studentno [description]
	 * @return [type]            [description]
	 */
	public function getDorm($studentno){
		$accessToken = $this->getToken();
		$result = array();
		if(isset($accessToken)){
			//设置 User-Token
			$this->curl->setOption(CURLOPT_HTTPHEADER,
				array(
					'User-Token:' . $accessToken['token'],
					));
			// 设置 Form Data
			$this->curl->setOption(CURLOPT_POSTFIELDS, http_build_query(array('studentno' => $studentno)));
			// 伪造浏览器
			$this->curl->setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
			$resp = json_decode( $this->curl->post($this->host . $this->cas_url) );
			if($resp && $resp->result == 200){
				return $resp->data;
			}else{
				$result['error'] = '登录失败';
			}
		}else{
			$result['error'] = 'API授权失败';
		}
		return $result;
	}

	/**
	 * 验证CAS登录
	 * Sample : {"result":200,"message":"Success!","data":{"id":"245327","type":"1","name":"申报人测试","user_id":"20140612194197","userName":"2013003","teacherid":"2013003","studentid":"","user_sex":"0","cardid":"2013003","unit_id":"080","unit_name":"人力资源开发与管理处","officephone":"","mobile":""}}
	 * @param  [string] $username [description]
	 * @param  [string] $password [description]
	 * @return [std Object]           [data 转换的 json 对象]
	 */
	public function getCas($username,$password){
		$accessToken = $this->getToken();
		$result = array();
		if(isset($accessToken['token'])){
			// 设置 User-Token
			$this->curl->setOption(CURLOPT_HTTPHEADER,
				array(
					'User-Token:' . $accessToken['token'],
					));
			// 设置 Form Data
			$this->curl->setOption(CURLOPT_POSTFIELDS, http_build_query(array('username' => $username, 'password' => $password)));
			// 伪造浏览器
			$this->curl->setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
			$resp = json_decode( $this->curl->post($this->host . $this->cas_url) );
			if($resp && $resp->result == 200){
				return $resp->data;
			}else{
				$result['error'] = '登录失败';
			}
		}else{
			$result['error'] = 'API认证错误';
		}
		return $result;
	}

	/**
	 * 读取校园卡信息
	 * Sample Result : {"result":200,"message":"Success!","data":{"id":"1620071005","name":"王兴棠","carduuid":"000000370892","logo":"http://openapi.jnu.edu.cn/img/logo/2016/9/9/d92739afcd5c74dd857acc8c881cd10b.jpg","cardid":"237606"}}
	 * @param  [int] $pid_class [1 学生 2 老师 3 外派人员]
	 * @param  [string] $psnno     [帐号]
	 * @return [std Obj]            [data 转换的 json 对象]
	 */
	public function getCard($pid_class, $psnno){
		$accessToken = $this->getToken();
		$result = array();
		if(isset($accessToken['token'])){
			// 设置 User-Token
			$option = array('User-Token:'. $accessToken['token']);
			$this->curl->setOption(CURLOPT_HTTPHEADER,$option);
			// 设置 Post Data
			$this->curl->setOption(
				CURLOPT_POSTFIELDS,
				http_build_query(array('pidclass' => $pid_class,'psnno' => $psnno))
				);
			// 伪造浏览器
			$this->curl->setOption(
				CURLOPT_USERAGENT,
				'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
				);
			$resp = json_decode( $this->curl->post($this->host . $this->school_card_url) );
			if($resp && $resp->result == 200){
				return $resp->data;
			}else{
				$result['error'] = '获取校园卡信息失败';
			}
		}else{
			$result['error'] = '获取AccessToken失败';
		}
		return $result;
	}

	/**
	 * 获取通讯 Token
	 * API Result Sample：{"result":200,"message":"Success","data":{"tokenid":"8eab00cc5c51417eb4456814f3cd956b"}}
	 * @param  string $app_id     [appid]
	 * @param  string $app_secret [appsecret]
	 * @param  string $api_url    [api url for token]
	 * @return array              array('token'=>token,'time'=>time,'error'=>error_string)
	 */
	public function getToken(){

		if(Yii::$app->cache && Yii::$app->cache->get('openapi_token') != null)
		{
			$token = Yii::$app->cache->get('openapi_token');
			if($token){
				if(time() - $token['time'] < 2 * 60 * 55){
					return $token;
				}
			}
		}

		$now = time();
		$curl = new Curl();
		$token = array();
		$resp = $curl->get($this->host . $this->api_url . "?appid=$this->app_id&appsecret=$this->app_secret");
		if(isset($resp)){
			$json = json_decode($resp);
			if($json){
				if(isset($json->result) && $json->result == 200){
					$token = array(
						'token' => $json->data->tokenid,
						'time' => $now,
					);
					Yii::$app->cache->set('openapi_token', $token);
					return $token;
				}else{
					$token['error'] = 'API认证错误';
				}
			}
		}else{
			$token['error'] = '网络错误';
		}

		return $token;
	}

}