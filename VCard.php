<?php
//defined('BASEPATH') OR exit('No direct script access allowed');

class VCard {
    private $_version;
    private $_ver;
    private $_vcard=array();
    public function __construct($version=false){
        if(!empty($version)){
        	$this->_version = $version;
        }
        
    }
	
	
	/* * * * * * * * * * * * * * * * * * 
	 * add single entry of vcard
	 * * * * * * * * * * * * * * * * * */
	public function add($vcard,$return=false){
	    if(!empty($vcard) && !count($vcard)){
	        return;
	    }
	    if(!$this->_version){
	    	return 'VCard Version not defined';
	    }

	    if(!in_array($this->_version,$this->version_list())){
	    	return 'VCard Version not supported';
	    }
	    $add[] = PHP_EOL;
	    $add[] = $this->begin();
	    $add[] = $this->_n(@$vcard['first_name'],@$vcard['last_name'],@$vcard['title']);
	    $add[] = $this->_fn(@$vcard['first_name'].' '.@$vcard['last_name']);
	    $add[] = $this->_org(@$vcard['organisation']);
	    $add[] = $this->_title(@$vcard['organisation_role']);
	    $add[] = $this->_photo(@$vcard['photo']);
	    $add[] = $this->_tel(@$vcard['telephone']);
	    $add[] = $this->_adr(@$vcard['address']);
	    $add[] = $this->_email(@$vcard['email']);
	    $add[] = $this->_nickname(@$vcard['nickname']);
		$add[] = $this->_url(@$vcard['website']);
		$add[] = $this->_geo(@$vcard['location']);
		$add[] = $this->_class(@$vcard['class']);
		$add[] = $this->_class(@$vcard['gender']);
		$add[] = $this->_class(@$vcard['anniversary']);
		$add[] = $this->_class(@$vcard['caladruri']);
		$add[] = $this->_class(@$vcard['caluri']);
		$add[] = $this->_class(@$vcard['class']);
	    $add[] = $this->end();
	    $add[] = PHP_EOL;
	    $add = join('',$add);
	    $this->_vcard[] = $add;
	    if($return==true){
	    	return $add;
	    }
	}
	private function _n($first=false,$last=false,$title=false){
		if(!trim($first) || !trim($last) || !trim($title)){
			return;
		}
		switch($this->_version){
			case '2.1':
				$format = 'N:{last_name};{first_name};;{title};';
				break;
			case '3.0':
				$format = 'N:{last_name};{first_name};;{title};';
				break;
			case '4.0':			
				$format = 'N:{last_name};{first_name};;{title};';
				break;

		}
		$search = array('{title}','{first_name}','{last_name}');
		$replace = array($title,$first,$last);
		return  str_replace($search,$replace,$format).PHP_EOL;
	}
	private function _fn($str){
		if(!trim($str)){
			return;
		}
		switch($this->_version){
			case '2.1':
				$format = 'FN:'.$str.';';
				break;
			case '3.0':
				$format = 'FN:'.$str.';';
				break;
			case '4.0':			
				$format = 'FN:'.$str.';';
				break;
		}
		return $format.PHP_EOL;
	}
	private function _org($str){
		if(!$str){
			return;
		}
		return 'ORG:'.$str.PHP_EOL;
	}
	private function _title($str){
		if(!$str){
			return;
		}
		return 'TITLE:'.$str.PHP_EOL;
	}


	private function _photo($str){
		if(!$str){
			return;
		}
		if(filter_var($str, FILTER_VALIDATE_URL)){
			//get mime type
			$type = strtoupper(pathinfo($str,PATHINFO_EXTENSION));
		}else{
			//encoding type is not available for now
			//$type = 'JPEG;ENCODING=BASE64:[base64-data]';
		}
		switch($this->_version){
			case '2.1':
				$format = 'PHOTO;'.$type.':'.$str;
				break;
			case '3.0':
				$format = 'PHOTO;TYPE='.$type.';VALUE=URI:'.$str;
				break;
			case '4.0':
				$format = 'PHOTO;MEDIATYPE=image/'.$type.':'.$str;
				break;
		}
		return $format.PHP_EOL;
	}
	private function _tel($tel){
		//accepted string for multiple
		// tel = 'work:1234567890,home:1234567890'

		if(!$tel){
			return;
		}


		switch($this->_version){
			case '2.1':
				$format = 'TEL;{type};VOICE:{number}';
				break;
			case '3.0':
				$format = 'TEL;TYPE={type},VOICE:{number}';
				break;
			case '4.0':
				$format = 'TEL;TYPE={type},voice;VALUE=uri:tel:{number}';
				break;
		}
		$search = array('{type}','{number}');
		$return = '';
		// explode number with ,
		$tel = explode(',',$tel);
		foreach($tel as $phone){
			$phone = explode(':',$phone);
			if(count($phone)>1){
				//first value is label and second is phoe=ne number
				$type = trim(strtoupper($phone[0]));
				$number = trim($phone[1]);
			}else{
				$type = 'PHONE';
				$number = $phone[0];
			}
			$return .= str_replace($search,array($type,$number),$format).PHP_EOL;
		}
		return $return;
	}
	private function _adr($adr){

		if(!$adr){
			return;
		}

		switch($this->_version){
			case '2.1':
				$format = 'ADR;TYPE={type}:;;{address};;;;';
				break;
			case '3.0':
				$format = 'ADR;TYPE={type}:;;{address};;;;';
				break;
			case '4.0':
				$format = 'ADR;TYPE={type}:;;{address};;;;';
				break;
		}
		$search = array('{type}','{address}');
		$return = '';
		// explode number with ,
		$adr = explode('|',$adr);
		foreach($adr as $address){
			$address = explode(':',$address);
			if(count($address)>1){
				//first value is label and second is phoe=ne number
				$type = trim(strtoupper($address[0]));
				$addr = trim($address[1]);
			}else{
				$type = 'HOME';
				$addr = $address[0];
			}
			$return .= str_replace($search,array($type,$addr),$format).PHP_EOL;
		}
		return $return;

	}
	private function _email($str){
		if(!$str){
			return;
		}
		$format = 'EMAIL:{email}';
		$str = explode(',',$str);
		$email = '';
		foreach($str as $value){
			if(filter_var($value,FILTER_VALIDATE_EMAIL)){
				$email .= str_replace('{email}',trim($value),$format).PHP_EOL;
			}
			
		}
		return $email;
	}
	private function _geo($location){
		if(!$location){
			return;
		}
		$format='';
		$location = explode(',',$location);
		$lat = $location[0];
		$long = $location[1];
		if(!$lat || !$long){
			return;
		}
		switch($this->_version){
			case '2.1':
				$format = 'GEO:'.$lat.';'.$long.PHP_EOL;
				break;
			case '3.0':
				$format = 'GEO:'.$lat.';'.$long.PHP_EOL;
				break;
			case '4.0':
				$format = 'GEO:geo:'.$lat.','.$long.PHP_EOL;
				break;
		}
		return $format;
	}
	private function _nickname($nickname){
		if(!trim($nickname)){
			return;
		}
		$format='';
		switch($this->_version){
			case '3.0':
				$format = 'NICKNAME:'.$nickname.PHP_EOL;
				break;
			case '4.0':
				$format = 'NICKNAME:'.$nickname.PHP_EOL;
				break;
		}
		return $format;
	}
	private function _url($str){
		return ($str && filter_var($str,FILTER_VALIDATE_URL))?'URL:'.$str.PHP_EOL:false;		
	}


	/* only Version 3.0 Supported */
	private function _class($str){
		if($this->_version=='3.0'){
			return ($str)?'CLASS:'.$str.PHP_EOL:false;
		}
	}

	/* Only Version 4.0 Supported */
	private function _gender($str){
		if($this->_version=='4.0'){
			return ($str)?'GENDER:'.$str.PHP_EOL:false;
		}
	}
	private function _anniversary($str){
		if($this->_version=='4.0'){
			return ($str)?'ANNIVERSARY:'.date("Ymd", strtotime($str)).PHP_EOL:false;
		}
	}
	private function _caladruri($str){
		if($this->_version=='4.0'){
			return ($str)?'CALADRURI:'.$str.PHP_EOL:false;	
		}
	}
	private function _caluri($str){
		if($this->_version=='4.0'){
			return ($str)?'CALURI:'.$str.PHP_EOL:false;	
		}
	}
	/* * * * * * * * * * * * * * * * * * 
	 * Generate Multiple VCard Data
	 * * * * * * * * * * * * * * * * * */
	public function generate($data){
	    
	}
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * create vCard file and return downloadable path
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function create($path){
	    
	}
	
	/* * * * * * * * * * * * * * * * * * 
	 * return vcard data in array
	 * * * * * * * * * * * * * * * * * */
    public function output(){
	$vcard = $this->_vcard();
        $this->_vcard = array();
	return $vcard;
    }
    
    
    private function begin(){
        $begin = 'BEGIN:VCARD'.PHP_EOL;
        $begin.= 'VERSION:'.$this->_version.PHP_EOL;
        return  $begin;
    }
    private function end(){
    	$end = 'REV:'.date('Y-m-d H:i:s').PHP_EOL;
    	$end.= 'END:VCARD'.PHP_EOL;
    	return $end;
    }
    
	/* * * * * * * * * * * * * * * * * * 
	 * Remove all data for next use
	 * * * * * * * * * * * * * * * * * */
    private function flush(){
        $this->_vcard = array();
    }
    
	/* * * * * * * * * * * * * * * * * * 
	 * Set Version for VCF File
	 * * * * * * * * * * * * * * * * * */
	public function set_version($ver='4.0'){
	    if(!$ver){
	        $ver = '4.0';
	    }
	    $this->_version = $ver;
	    if(!in_array($this->_version,$this->version_list())){
	    	echo 'VCard Version not supported';
	    }
	}
	private function version_list(){
		return array('2.1','3.0','4.0');
	}
    
}

$card['title'] = 'Mr.';
$card['first_name'] = 'Rahul';
$card['last_name'] = 'Saini';
$card['organisation'] = 'WeForIT';
$card['organisation_role'] = 'Owner';
$card['photo'] = 'http://domain.com/photo.jpg';
$card['telephone'] = 'mobile:+91981234567,office:+91789456123,other:+91789456123';
$card['address'] = 'home:782,jharsa,gurgaon,india|761,sohna road, gurgaon, india';
$card['email'] = 'rahul@example.com,saini@example.com';
$vcard = new VCard();
$vcard->set_version('2.1');
$vcard->add($card);
$vcard->output();

