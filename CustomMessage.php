<?php
namespace dareninfo\jnu\openapi;

/**
 * 自定义消息 邮件/短信
 */
class CustomMessage{
	/**
	 * 通道
	 */
	const TUNNEL_JINAN_ALARM = 'jinan_alarm';
	const	TUNNEL_JINAN_MESSAGE = 'jinan_message';
	const	TUNNEL_SIHAI_ALARM = 'sihai_alarm';
	const	TUNNEL_SIHAI_MESSAGE = 'sihai_message';
	const	TUNNEL_SHOUYISMS = 'shouyisms';
	/**
	 * 消息类型
	 */
	const MESSAGE_EMAIL = 'email';
	const MESSAGE_MOBILE = 'mobile';
	/**
	 * 发送者ID
	 * @var string
	 */
	public $senderid = '1';
	/**
	 * 消息类型
	 * @var string
	 */
	public $type = '';
	/**
	 * 消息通道
	 * @var string
	 */
	public $tunnel = '';
	/**
	 * 接收者编号 target有值是可为空
	 * @var string
	 */
	public $receiverid = '';
	/**
	 * 接收者名称 target有值是可为空
	 * @var string
	 */
	public $receivername = '';
	/**
	 * 接收者（邮箱或者手机号码） 和 receiverid 不能同时为空
	 * @var string
	 */
	public $target = '';
	/**
	 * 邮件主题 type=email 时，必填；type=mobile时，可为空
	 * @var string
	 */
	public $subject = '';
	/**
	 * 消息主体 短信或者邮件正文（不支持 html）
	 * @var string
	 */
	public $message = '';

	public function __construct(){

	}

	/**
	 * 初始化一个邮件信息
	 * @param  [type] $target  [description]
	 * @param  [type] $subject [description]
	 * @param  [type] $message [description]
	 * @return [type]          [description]
	 */
	public function email($target,$subject,$message,$tunnel = CustomMessage::TUNNEL_JINAN_MESSAGE){
		$this->type = CustomMessage::MESSAGE_EMAIL;
		$this->target = $target;
		$this->subject = $subject;
		$this->message = $message;
		$this->tunnel = $tunnel;
	}

	/**
	 * 初始化一个短信信息
	 * @param  [type] $target  [description]
	 * @param  [type] $message [description]
	 * @return [type]          [description]
	 */
	public function sms($target,$message,$tunnel = CustomMessage::TUNNEL_SHOUYISMS){
		$this->type = CustomMessage::MESSAGE_MOBILE;
		$this->target = $target;
		$this->message = $message;
		$this->tunnel = $tunnel;
	}
}