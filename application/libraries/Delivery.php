<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Delivery  
{
	protected  $client_id;
	protected  $client_secret;
	protected  $token;
	protected  $url;
	
	function request($method,$params=[],$post=0,$headers=[],$url2='')
	{  
		$url=(strlen($url2)>0 ? $url2 : $this->url).$method;
		$ch = curl_init($url);
		 
		print_r([$post,$url,$params,$headers]);
		 
		if (count($params)>0)
		{
			if ($post==1)
			{
				$data = http_build_query($params);
				
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			elseif ($post==2)
			{
				
				$payload= ($params); 
				$headers[]='Content-Type: application/json';
				$headers[]='Content-Length: ' . strlen($payload) ;
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				 
			} 
		}
		
		if ($post) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		if (count($headers)>0) curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($ch, CURLOPT_HEADER, 1);

		$result = curl_exec($ch);
		 $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				 
		curl_close($ch);
		
		$header = substr($result, 0, $header_size);
		$status=substr($header, 9, 3);
		$body = substr($result, $header_size);
					
		print_r($result);print_r($header_size);
		
		 $json= json_decode($body);
		//системный дебаг
		if (count($json)<1) addlog(print_r([$method,$params ,$result],1));
		 
		return  $json;
	} 
}