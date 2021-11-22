<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Ulogin  
{
	
	public function print_social($redurect_uri='',$socials='')
	{
		if (strlen($socials)<1) $socials='google,facebook,twitter;';//hidden=other
		if (strlen($redurect_uri)<1) $redurect_uri=URI_PROTOCOL.base_domain().'/site/ulogin'; 
		echo '
		<script src="//ulogin.ru/js/ulogin.js"></script>
		<div id="uLogin" data-ulogin="display=panel;theme=classic;lang=en;fields=first_name,last_name;providers='.$socials.';redirect_uri='.urlencode($redurect_uri).';mobilebuttons=0;"></div>';
	}
	
	public function request($token)
	{
		$s = file_get_contents('http://ulogin.ru/token.php?token=' . $token . '&host=' . $_SERVER['HTTP_HOST']);
        $user = json_decode($s, true);
        return $user;
					//$user['network'] - соц. сеть, через которую авторизовался пользователь
                    //$user['identity'] - уникальная строка определяющая конкретного пользователя соц. сети
                    //$user['first_name'] - имя пользователя
                    //$user['last_name'] - фамилия пользователя
                
	}
}