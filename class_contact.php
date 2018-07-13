<?php

/*
 * Github URL: https://github.com/RahulSaini91/php_library/blob/master/class_contact.php
 */

class contact{
	private $_headers;
	private $_to;
	private $_from = array();
	private $_subject;
	private $_message;
	private $_error = array();
	private $_html=0;

	public function __construct($html=false){
		$this->_headers[] = "MIME-Version: 1.0";
		if($html==true){
			$this->_headers[] = "Content-type:text/html;charset=UTF-8";
			$this->_html=1;
		}
	}
	public function set_headers($headers){
		if(empty($headers)){
			return;
		}
		if(!is_array($headers) || !count($headers)){
			$this->_headers[] = $headers;
			return;
		}
		foreach($headers as $header){
			$this->_headers[] = $header;
		}
	}
	public function to($mail){
		if(!trim($mail)){
			return;
		}
		$mail = explode(',',$mail);
		foreach($mail as $to){
			$to = trim($to);
			if(!filter_var($to,FILTER_VALIDATE_EMAIL)){
				continue;
			}
			$this->_to[] = $to;
		}
	}
	public function from($name=false,$email=false){

		$name = trim($name);
		$email = trim($email);
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
			$email = 'admin@'.$_SERVER['HTTP_HOST'];
		}
		$this->_from['name'] = $name;
		$this->_from['email'] = $email;
		$this->_headers[] = 'From: '.$name.' <'.$email.'>';

	}
	public function subject($str,$fields=array()){
		$str = trim($str);
		$str = (!empty($str)?$str:'Contact Form');

		if(count($fields)>0){
			$str = $this->_replace($str,$fields);
		}
		$this->_subject = $str;
	}
	public function message($msg,$fields=array()){
		$msg = trim($msg);
		if(empty($msg)){
			$this->_error['message'] = 'You didn\'t enter the message';
			return;
		}
		if($this->_html==1){
			$msg = $this->_html(nl2br($msg));
		}
		if(count($fields)>0){
			$msg = $this->_replace($msg,$fields);
		}

		$this->_message = $msg;
	}
	public function send($switch=false,$response=false){
		if(!$this->_to){
			$this->_error['to'] = 'To is not defined';
		}
		if(!$this->_subject){
			$this->_error['subject'] = 'Subject is not defined';
		}
		if(!$this->_message){
			$this->_error['message'] = 'Message is blank';
		}
		if(count($this->_error)>0){
			return array('error'=>$this->_error);
		}

		//set header: From
		if(empty($this->_from)){
			$this->_from = $this->from();
		}

		//echo $this->_message;
		//echo $this->_subject;
		//print_r($this->_to);
		//print_r($this->_from);
		//print_r($this->_headers);

		$headers = join("\r\n",$this->_headers);

		if(empty($switch) || !filter_var($switch,FILTER_VALIDATE_EMAIL)){
			$switch = 'webmaster@'.$_SERVER['HTTP_HOST'];
		}
		$response = array();
		foreach($this->_to as $to){
			if(mail($to,$this->_subject,$this->_message,$headers,'-f'.$switch)){
				$response['success'][] = 'Email has been sent to: '.$to;
			}else{
				$response['error'][] = 'Failed to send email: '.$to;
			}
		}
		//flush the data
		$this->flush();

		if($response==true){
			return $response;
		}
		if(!count($response['error'])){
			return 1;
		}
	}
	private function _replace($string,$fields){
		if(!count($fields)){
			return;
		}
		$search = array();
		$repalce = array();
		foreach($fields as $key => $value){
			$search[] = '{'.$key.'}';
			$replace[] = $value;
		}
		return str_replace($search,$replace,$string);
	}
	private function _html($msg){
		return '<html><head><title>'.$this->_subject.'</title></head><body>'.$msg.'</body></html>';
	}
	private function flush(){
		unset($this->_headers);
		unset($this->_to);
		unset($this->_from);
		unset($this->_subject);
		unset($this->_message);
		unset($this->_error);
		unset($this->_html);
	}
}

$contact = new contact(true);
$contact->to('rahulsaini.rlm@gmail.com');
$contact->subject('Mail from {name}',array('name'=>'Rahul'));
$contact->message('Hi {name}, you got a mail from {sender}',array('rahul'=>'rahul'));
print_r($contact->send());
