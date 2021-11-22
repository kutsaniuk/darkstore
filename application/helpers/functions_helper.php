<?php
	function row($table,$id)
    {
		$CI =& get_instance();	
		return $CI->db->get_where($table,array('id'=>(int)$id))->row_array(); 
    }
	
	function number_format2($i,$o=0)
	{
		return ceil($i*100)/100;
	}
	
	function getHumanTimeDiff($strTime) {
		$dtNow = time();
		$dtTime = strtotime($strTime); 
		$diff = $dtNow - $dtTime;
	 
		if ($diff >= 0 && $diff < 15) { 
			return l('только что');
		} else if ($diff >= 15 && $diff < 60) {
			// разница меньше минуты => ...секунд назад
			return $diff . " " . l('сек. назад');
		} else if ($diff >= 60 && $diff < 3600) {
			// разница меньше часа => ...минут назад
			return floor($diff/60) . " " . l('мин. назад.');
		} else if ($diff >= 3600 && $diff < 86400) {
			// разница меньше суток => ...часов назад
			return floor($diff/3600) . " " . l('ч. назад');
		} else if ($diff >= 86400 && $diff < 2592000) {
			// разница меньше месяца => ...дней назад
			return floor($diff/86400) . " " . l('дн. назад');
		} else if ($diff >= 2592000) {
			// разница меньше года
			return l('более месяца назад');
		}
	 
		return '';
	}
	
	function time_dif($diff)    
	{
		if ( $diff / 3600/24>1)
			return sprintf('%2d days %02d:%02d:%02d', $diff / 3600/24, $diff / 3600, ($diff % 3600) / 60, $diff % 60);
		else return sprintf('%02d:%02d:%02d',  $diff / 3600, ($diff % 3600) / 60, $diff % 60);
	}
	
	function get_all_array($arr,$key_val='id',$name_val='name')
	{
		$list=array();
		foreach ($arr as $row) $list[$row[$key_val]]=$row[$name_val];
		return $list;
	}
	
	function generate_url($v)
	{
		$str = mb_strtolower(translit(str_replace(' ','-',str_replace(array('.','(',')','&','\\',"'" ),'',$v))),'utf-8');
		$str = str_replace('----','-',$str);
		$str = str_replace('---','-',$str);
		$str = str_replace('--','-',$str);
	
		$str = preg_replace("[^-a-zA-Z0-9]", "", $str); 
		return $str;
	}
	
	function l($word)//автоперевод
	{
		$key=translit($word);
		$CI =& get_instance();	
		if (isset($CI->CI)) $CI=$CI->CI;//значит вызвали из класса
		if (count($CI->lang->is_loaded)==0) { 
			$CI->lang->load('site_lang', (new Users($CI))->get_url_language());
		}
		$word2=$CI->lang->line($key);
		if (strlen($word2)>0) return $word2;
		//
		 
		if (count($CI->lang->is_loaded)>0)
		{
			foreach ($CI->lang->is_loaded as $file =>$lang)
				$basepath =  './application/language/'.$lang.'/'.$file;
			//значит создаем в файл новое слово
			$txt = '$LANG["'.$key.'"]="'.$word.'";';
			$myfile = file_put_contents($basepath, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);	
			
		}
		else {
			echo ('error use lang file');
			addlog($basepath.' '.$txt); 
		}
			
			
		 
		
		
		
		
		return $word;
	}
	
	function base_domain()
	{
		$url = str_replace('https:','',base_url());
		$url = str_replace('http:','',$url);
		$url = str_replace('/','',$url);
		return $url;
	}
	
	function vars($key)
	{
		$CI =& get_instance();	
		
		$option=$CI->db->get_where('options',array('key'=>$key))->row_array();
		
		return $option['value'];
	}
	
	function send_mail2($who,$theme,$text,$from='info')
	{
		$headers = 'Content-type: text; charset=UTF-8' . "\r\n";
		// Дополнительные заголовки
		$headers .= 'From: '.$from . "\r\n"; 
		mail($who, $theme,$text, $headers);					
	}
	
	function check()
    {
		$CI =& get_instance();	
		$user_id=$CI->session->userdata('user_id'); 
		if (isset($user_id)) 
		{
			$user =  new Users($CI,(int)$user_id);
			if ($user->id>0) {
				if ($CI->input->get('remember'))
				{ //постановка куки после авторизации в силу того что при авторизации чрез аякс на куки ставятся ограничения
					$CI->input->set_cookie('user_id', $user->id, 30*24*3600, base_domain() , '/' );
					$CI->input->set_cookie('hash', md5($user->password.$user->email), 30*24*3600, base_domain() , '/' );
				} 
				return $user;
			} 
		}
		$user = new Users($CI);
		$user->check_cookie();
		return $user; 
    }
	
	function mysql_check($params)
	{
		$CI =& get_instance();	
		
		foreach ($params as $k=>$v) $params[$k]=$CI->db->escape_str($v);
		return $params;
	}
	
    function redirect_js($url)
	{
		$_SESSION['last_page']= $_SERVER['REQUEST_URI'];
		echo '<script>window.location="'.$url.'";</script>';
	}	
	
	function redirect2($url)
	{
		$_SESSION['last_page']= $_SERVER['REQUEST_URI'];
		redirect($url);
	}	
	
    function translit($urlstr,$isk='_') 
	{
		 
			
			$tr = array(
				"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
				"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
				"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
				"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
				"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
				"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
				"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
				"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
				"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
				"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
				"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
				"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
				"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya", 
				" "=> "_",  "ё"=>"e" , "'"=>"",'ö'=>'o','/'=>'-','ü'=>'u'
			);
			$tr[$isk]=$isk;
			$urlstr = strtr($urlstr,$tr);
			
		
		return $urlstr;
		
	}
	
	function kol($kol,$drob=0)
    {
        
        if ($kol>10000000000000) { $str=(int)($kol/1000000000000); $str.='mm'; }
        elseif ($kol>10000000000) { $str=(int)($kol/1000000000); $str.='mk'; }
        elseif ($kol>10000000) { $str=(int)($kol/1000000); $str.='kk'; }
        elseif ($kol>10000) { $str=(int)($kol/1000); $str.='k'; } 
        elseif ($kol>1) $str=(int)$kol;
        elseif ($kol<-1) $str=(int)$kol;
        elseif ($kol>0.01 && $drob==1) $str=(int)($kol*100)/100;
        elseif ($drob==1)   $str=(int)($kol*10000)/10000;
        else $str=(int)$kol;
        return $str;
    }
	
	function check_recapcha($response)
	{
		$res = Send_Post('https://www.google.com/recaptcha/api/siteverify',  array('secret'=>GOOGLE_RECAPCHA_SECRET,'response'=>$response,'remoteip'=>get_client_ip()), base_url());
		$res=json_decode($res);
		if ($res->success) return true;
		return false;
	}
	
	function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	
	function vk_api($m,$a=array())
	{
		global $api_id,$vk_id, $api_secret, $DB; 
		require_once "class/VK_Api.php";
		$VK = new  VK_Api($api_id, $user_id, $api_secret);
			 
		return $resp = $VK->api($m, $a); 
	}
	 
	 
	function google_api($geocode,$additional_args='')
	{
		global $Google_API_KEY;
		
		$r=Send_Post('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$geocode.$additional_args.'&language=en&key='.$Google_API_KEY);
		
		if (strlen($r)<1) return false;
		$r2=json_decode($r);
		$res=$r2->results;
		 
		return $res;
	} 
	 
	 
	function yandex_api($geocode,$additional_args='')
	{
		global $Yandex_API_KEY;
		
		//die('https://geocode-maps.yandex.ru/1.x/?geocode='.$geocode.'&format=json&kind=street&lang=en_US&key='.$Yandex_API_KEY.$additional_args); 
		 $r=Send_Post('https://geocode-maps.yandex.ru/1.x/?geocode='.$geocode.'&format=json&kind=street&lang=en_US&key='.$Yandex_API_KEY.$additional_args);
		//file_get_contents('https://geocode-maps.yandex.ru/1.x/?geocode='.$geocode.'&format=json&kind=street&lang=en_US&key='.$Yandex_API_KEY.$additional_args);
		 
		if (strlen($r)<1) return false;
		$r2=json_decode($r);
		return $r2->response->GeoObjectCollection;
	}
	
	function Send_Post($post_url, $post_data=array(), $refer='') 
	{ 
	  $ch = curl_init(); 
	  curl_setopt($ch, CURLOPT_URL, $post_url); 
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	  if (strlen($refer)>0) curl_setopt($ch, CURLOPT_REFERER, $refer); 
	 
	  if (count($post_data)) {
		   curl_setopt($ch, CURLOPT_POST, 1); 
		   curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 
	  }
	  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); 
	  curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17'); 
	  
	  $data = curl_exec($ch); 
	  curl_close($ch); 
	  
	  return $data; 
	} 
	
	function SocialAuthInit($socials='vk,odnoklassniki,mailru,yandex,google,facebook',$redurect_uri='')
	{
		if (strlen($redurect_uri)<1) $redurect_uri=URI_PROTOCOL.base_domain().'/site/oauth'; 
		
		$adapterConfigs = array(
			'vk' => array(
				'client_id'     => '3774741',
				'client_secret' => '3nLWEs45iWeKypmVR2CU',
				'redirect_uri'  => $redurect_uri.'?provider=vk'
			),
			'odnoklassniki' => array(
				'client_id'     => '168635560',
				'client_secret' => 'C342554C028C0A76605C7C0F',
				'redirect_uri'  =>  $redurect_uri.'?provider=odnoklassniki',
				'public_key'    => 'CBADCBMKABABABABA'
			),
			'mailru' => array(
				'client_id'     => '770076',
				'client_secret' => '5b8f8906167229feccd2a7320dd6e140',
				'redirect_uri'  => $redurect_uri.'?provider=mailru'
			),
			'yandex' => array(
				'client_id'     => 'bfbff04a6cb60395ca05ef38be0a86cf',
				'client_secret' => '219ba8388d6e6af7abe4b4b119cbee48',
				'redirect_uri'  =>  $redurect_uri.'?provider=yandex'
			),
			'google' => array(
				'client_id'     => '670717055380-n8l7lhntov9nhcfnrsfp7tu62jeu6ivs.apps.googleusercontent.com',
				'client_secret' => 'ViMnyMImXYipCXE7fuBKmcuh',
				'redirect_uri'  =>  urlencode($redurect_uri.'?provider=google')
			),
			'facebook' => array(
				'client_id'     => '256885658074059',
				'client_secret' => '704398156e40bb96eacbced192336aeb',
				'redirect_uri'  => $redurect_uri.'?provider=facebook'
			)
		);
		
		$socials=explode(',',$socials); 
		$adapters = array();
		foreach ($adapterConfigs as $adapter => $settings) if (in_array($adapter,$socials)) {
			$class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
			$adapters[$adapter] = new $class($settings);
		}
		
		return $adapters;
	}
	
	function SocialAuth($socials='vk,odnoklassniki,mailru,yandex,google,facebook',$redurect_uri='')
	{
		$adapters=SocialAuthInit($socials,$redurect_uri);
		
		foreach ($adapters as $title => $adapter) { 
			echo '<p><a href="' . $adapter->getAuthUrl() . '">' . ucfirst($title) . '</a></p>';
		}
		

	}
	
	function table_color($num)
	{
		if ($num<0) return 'red';
		if ($num>0) return 'green';
		return '';
	}
	
	function table_val($num)
	{
		if ($num<0) return '';
		if ($num>0) return '+';
		return '';
	}
	
	function addlog($text,$file='log.txt')
    {
        
        $fp = fopen($file, 'a');
        fwrite($fp,"\r\n".date('d.m.Y H:i:s'). '  '.$text);
        
        fclose($fp);
    }
 