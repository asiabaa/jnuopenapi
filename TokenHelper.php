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
	 * 信息接口
	 * @var string
	 */
	private $message_url = '/service/webservice/message/sendDefineMessage.html';
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
	 * [sendMessage description]
	 * Sample Data:{"result":200,"message":"success.","data":{"id":3275,"sendtime":"2016-09-12 13:35:02"}}
	 * @param  [CustomMessage] $customMessage [信息实体]
	 * @return [type]                [description]
	 */
	public function sendMessage($customMessage){
		if(!$customMessage ||
			gettype($customMessage) != 'object' ||
			get_class($customMessage) != 'dareninfo\jnu\openapi\CustomMessage'){
			return json_decode( json_encode(array('error' => '参数错误：必须是CustomMessage实体对象')) );
		}
		$data = array(
			'senderid' => $customMessage->senderid,
			'type' => $customMessage->type,
			'tunnel' => $customMessage->tunnel,
			'receiverid' => $customMessage->receiverid,
			'receivername' => $customMessage->receivername,
			'target' => $customMessage->target,
			'subject' => $customMessage->subject,
			'message' => $customMessage->message,
			);
		return $this->request($this->message_url, $data);
	}

	/**
	 * 查询学生缴费信息
	 * Sample Data:{"result":200,"message":"Success!","data":{"T_TUI_LEARN":"11532.000000","S_TUI_INSURANCE":"232.000000","S_TUI_DORM":"1300.000000","S_TUI_LEARN":"10000.000000","S_TUISUM":"1625071001","S_TUI_CARD":"0.000000","STUDENT_NO":"1625071001"}}
	 * @param  [string] $studentno [学生学号]
	 * @return [stdObject]            [description]
	 */
	public function getStudentTuition($studentno){
		$data = array('studentno'=>$studentno);
		return $this->request($this->dorm_url, $data);
	}

	/**
	 * 查询学生宿舍信息
	 * Sample Result : {"result":200,"message":"Success!","data":{"REGION":"广州本部","BUILDING_ADDRESS":"学11栋","ROOM_NO":"135","FEE":1000,"STUDENT_NO":"1623101001","STUDENT_NAME":"蔡智辉","SEX":"1","COLLEGE":"生命科学技术学院","MAJOR":"遗传学","SCHOOLING_LENGTH":"3","STU_POOL_TYPE":"内招生"}}
	 * @param  [type] $studentno [description]
	 * @return [stdObject]            [data to Json Object]
	 */
	public function getDorm($studentno){
		$data = array('studentno' => $studentno);
		return $this->request($this->dorm_url, $data);
	}

	/**
	 * 验证CAS登录
	 * Sample : {"result":200,"message":"Success!","data":{"id":"245327","type":"1","name":"申报人测试","user_id":"20140612194197","userName":"2013003","teacherid":"2013003","studentid":"","user_sex":"0","cardid":"2013003","unit_id":"080","unit_name":"人力资源开发与管理处","officephone":"","mobile":""}}
	 * @param  [string] $username [description]
	 * @param  [string] $password [description]
	 * @return [std Object]           [data 转换的 json 对象]
	 */
	public function getCas($username,$password){
		$data = array('username' => $username, 'password' => $password);
		return $this->request($this->cas_url, $data);
	}

	/**
	 * 读取校园卡信息
	 * Sample Result : {"result":200,"message":"Success!","data":{"id":"1620071005","name":"王兴棠","carduuid":"000000370892","logo":"http://openapi.jnu.edu.cn/img/logo/2016/9/9/d92739afcd5c74dd857acc8c881cd10b.jpg","cardid":"237606"}}
	 * @param  [int] $pid_class [1 学生 2 老师 3 外派人员]
	 * @param  [string] $psnno     [帐号]
	 * @return [std Obj]            [data 转换的 json 对象]
	 */
	public function getCard($pid_class, $psnno){
		$data = array('pidclass' => $pid_class,'psnno' => $psnno);
		return $this->request($this->school_card_url, $data);
	}

	/**
	 * 接口请求
	 * @param  [string] $method [接口地址]
	 * @param  [array] $data   [post数据]
	 * @return [stdObject]         []
	 */
	private function request($method, $data){
		$accessToken = $this->getToken();
		$result = array();
		if(!array_key_exists('error',$accessToken)){
			//设置 User-Token
			$this->curl->setOption(
				CURLOPT_HTTPHEADER,
				array(
					'User-Token:' . $accessToken['token']
					));
			// 设置 Form Data
			$this->curl->setOption(
				CURLOPT_POSTFIELDS,
				http_build_query($data)
				);
			// 伪造浏览器
			$this->curl->setOption(
				CURLOPT_USERAGENT,
				'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
				);
			$resp = $this->curl->post($this->host . $method);
			if($this->curl->responseCode == 200 && $resp){
				return json_decode($resp);
			}else{
				$result['error'] = '请求接口没有响应';
			}
		}else{
			$result['error'] = 'API授权失败';
		}
		return json_decode(json_encode($result));
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
		// 如果 cache 中有可用 AccessToken，直接取用
		if(Yii::$app->cache && Yii::$app->cache->get('openapi_token') != null)
		{
			$token = Yii::$app->cache->get('openapi_token');
			if($token){
				if(time() - $token['time'] < 2 * 60 * 55){
					return $token;
				}
			}
		}
		// 从 cache 中读取失败，通过 API 获取
		$now = time();
		$curl = new Curl();
		$token = array();
		$resp = $curl->get($this->host . $this->api_url . "?appid=$this->app_id&appsecret=$this->app_secret");
		if($curl->responseCode == 200 && isset($resp)){
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