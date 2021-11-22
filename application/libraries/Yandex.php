<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Yandex  
{
	private $token;
	private $login;
	private $master_token;
	 
	function __construct($token='-xDjVM',$login= "")
	{
		if (strlen($login)<1) $login=vars('login_yandex');
		$this->token=$token;
		$this->login=$login;
		$this->master_token = '';
	}
	
	function get_finance_token( $operation_num,$login)
	{
		// die($this->master_token . $operation_num . 'AccountManagementCreateInvoice' . $this->normalize_login($login));
		return hash("sha256", $this->master_token . $operation_num . 'AccountManagementInvoicezakaz-direct');// . $this->normalize_login($login)); //$this->login
	}
	
	function normalize_login($login) 
	{
		
		$login = strtolower($login); 
		return $login = str_replace([ '.' ],'-',$login);
	}
	
	function Metrik_Counters( )
	{
		$operation_num = time();
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api-metrika.yandex.net/management/v1/counters?field=goals');
		  curl_setopt($ch, CURLOPT_POST, 0);
	  
			 $headers = [
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$this->token.'',
			 
			'Content-Type: application/x-yametrika+json',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		 
		  $r=  json_decode($server_output,true);
		 
		 return $r['counters'];
	}
	
	function Metrik_Goals($counter_id )
	{
		$operation_num = time();
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api-metrika.yandex.net/management/v1/counter/'.$counter_id.'/goals');
		  curl_setopt($ch, CURLOPT_POST, 0);
	  
			 $headers = [
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$this->token.'',
			 
			'Content-Type: application/x-yametrika+json',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  
		  $r=  json_decode($server_output,true);
		 return $r;
		 
	}
	
	function Metrik_Report($ids=[],$goals=[] ,$date1,$date2)
	{
		$goals_q='';
		if (count($goals))
		{ 
			$goals_q='&metrics=ym:s:users,';
			
			foreach($goals as $goal)
				$goals_q=',ym:s:goal'.$goal.'conversionRate'; 
		}
		
		$operation_num = time();
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api-metrika.yandex.net/stat/v1/data?date1='.$date1.'&date2='.$date2.'&ids='.implode(',',$ids).$goals_q);
		  curl_setopt($ch, CURLOPT_POST, 0);
	  
			 $headers = [
			'GET /management/v1/counters HTTP/1.1',
			'Host: api-metrika.yandex.net',
			'Authorization: OAuth '.$this->token.'',
			 
			'Content-Type: application/x-yametrika+json',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  
		  $r=  json_decode($server_output,true);
		 return $r;
		 
	}
	
	function AccountManagement_Get($row )
	{
		$operation_num = time();
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.ru/live/v4/json/');
		  curl_setopt($ch, CURLOPT_POST, 1);
		  curl_setopt($ch, CURLOPT_POSTFIELDS,'
		  {
			   "method": "AccountManagement",
			   "token": "'.$this->token.'",
			   "param": { 
				  "Action": "Get",
				  "SelectionCriteria": { 
					 "Logins": [
						"'.$row['login'].'"
					 ] 
				  }
			   }
			}
       ');  //Post Fields
	   
	    
	   
	   
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  $headers = [
			'POST /json/v5/ads/ HTTP/1.1',
			'Host: api.direct.yandex.com',
			'Authorization: Bearer '.$this->token.'',
			'Accept-Language: ru', 
			'Content-Type: application/json; charset=utf-8',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  $r=  json_decode($server_output,true);
		  return $r['data']['Accounts'][0]['AccountID'];
	}
	
	function create_bill($row,$sum)
	{
		 
		$operation_num = time();
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.ru/live/v4/json/');
		  curl_setopt($ch, CURLOPT_POST, 1);
		  curl_setopt($ch, CURLOPT_POSTFIELDS,'
		  {
			   "method": "AccountManagement",
			   "finance_token": "'.$this->get_finance_token( $operation_num,$this->login).'",
			   "operation_num":  '.$operation_num.' , 
			   "token": "'.$this->token.'",
			   "param": { 
				  "Action": "Invoice",
				  "Payments": [
					 {   
						"AccountID": '.$this->AccountManagement_Get($row).',
						"Amount": '.($sum).',
						"Currency": "RUB"
					 } 
				  ]
			   }
			} ');  //Post Fields
	   
	   
	 
	   
	   
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  $headers = [
			'POST /json/v5/ads/ HTTP/1.1',
			'Host: api.direct.yandex.com',
			'Authorization: Bearer '.$this->token.'',
			'Accept-Language: ru', 
			'Content-Type: application/json; charset=utf-8',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  $r=  json_decode($server_output,true);
		  print_r($r);die();
		  return $r['data']['ActionsResult'][0]['URL'];
	}
	
	function get_accounts()
	{
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.com/json/v5/agencyclients');
		  curl_setopt($ch, CURLOPT_POST, 1);
		  curl_setopt($ch, CURLOPT_POSTFIELDS,'
		  {
		  "method": "get",  
		  "params": { 
			"SelectionCriteria": {   
			  "Logins": [ ],
			  "Archived": "NO"
			},   
			"FieldNames": [ "AccountQuality" , "Archived" , "ClientId" , "ClientInfo" , "CountryId" , "CreatedAt" , "Currency" , "Grants" , "Login" , "Notification" , "OverdraftSumAvailable" , "Phone" , "Representatives" , "Restrictions" , "Settings" , "Type" , "VatRate" ],  
			"Page": {   
			  "Limit": 500,
			  "Offset": 0
			}
		  }
		}
       ');  //Post Fields
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  $headers = [
			'POST /json/v5/ads/ HTTP/1.1',
			'Host: api.direct.yandex.com',
			'Authorization: Bearer '.$this->token.'',
			'Accept-Language: ru', 
			'Content-Type: application/json; charset=utf-8',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  return $balance_direct_miramall = json_decode($server_output,true);
		   
	}
	
	function get_balance($logins=[])
	{
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.ru/live/v4/json/');
		  curl_setopt($ch, CURLOPT_POST, 1);
		  curl_setopt($ch, CURLOPT_POSTFIELDS,'{
			 "method": "AccountManagement",
			 "token": "'.$this->token.'",
			 "param": {"SelectionCriteria": {"Logins": '.json_encode($logins).'}, "Action": "Get"}}');  //Post Fields
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  $headers = [
			'POST /json/v5/ads/ HTTP/1.1',
			'Host: api.direct.yandex.com',
			'Authorization: Bearer '.$this->token.'',
			'Accept-Language: ru',
			'Client-Login: '.$this->login,
			'Content-Type: application/json; charset=utf-8',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  /*
		  echo PHP_EOL.'{
			 "method": "AccountManagement",
			 "token": "'.$this->token.'",
			 "param": {"SelectionCriteria": {"Logins": '.json_encode($logins).'}, "Action": "Get"}}';
			 echo PHP_EOL;
		print_r($headers);
		echo PHP_EOL;  
		print_r($server_output);  
		 die();  
		 */ 
		  
		  
		  
		  return $balance_direct_miramall = json_decode($server_output,true);
		  
		  
		   
	}
	
	function get_compaigns($login)
	{
		$ch = curl_init();
		  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.com/json/v5/campaigns');
		  curl_setopt($ch, CURLOPT_POST, 1);
		  curl_setopt($ch, CURLOPT_POSTFIELDS,'
		  {
  "method": "get",
  "params": {  
    "SelectionCriteria": {   
        "States": [ "ON" ]  
    },  
    "FieldNames": ["Id", "DailyBudget", "EndDate", "Funds", "ClientInfo", "Statistics","Name" ],   
    "Page": {   
      "Limit": 10000,
      "Offset": 0
    }
  }
}
       ');  //Post Fields
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  $headers = [
			'POST /json/v5/ads/ HTTP/1.1',
			'Host: api.direct.yandex.com',
			'Authorization: Bearer '.$this->token.'',
			'Accept-Language: ru', 
			'Client-Login: '.$login,
			'Content-Type: application/json; charset=utf-8',
		  ];
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		  $server_output = curl_exec ($ch);
		  curl_close ($ch);
		  return $balance_direct_miramall = json_decode($server_output,true);
		   
	}
	
	
	function get_keywords($login,$date1=-1,$date2=-1,$keywords=[],$compaigns=[] )
	{
		$name=date('Y-m-d H:i:s',time());
		$status=201;
		
		if ($date1==-1) $date1=time()-30*24*3600;
		if ($date2==-1) $date2=time();
		
		foreach($compaigns as &$c) $c='"'.$c.'"';
		foreach($keywords as &$c) $c='"'.$c.'"';
		 
		while ($status==201 || $status==202)
		{
			$ch = curl_init();
					  curl_setopt($ch, CURLOPT_URL,'https://api.direct.yandex.com/json/v5/reports');
					  curl_setopt($ch, CURLOPT_POST, 1);
					  curl_setopt($ch, CURLOPT_HEADER, 1);
					  $json='
					  {
			  "params" : { 
				"SelectionCriteria": {  
				  "DateFrom": "'.date('Y-m-d',$date1).'",
				  "DateTo": "'.date('Y-m-d',$date2).'"
				 
				  ,"Filter": [
					'.( count($compaigns)>0 ? '
						{ 
						"Field": "CampaignId",  
						"Operator": "IN", 
						"Values": ['.implode(',',$compaigns).']  
					  },' : '' ).'
					  {"Field": "Keyword", 
						"Operator": "IN", 
					  "Values": ['.implode(',',$keywords).']  }
					  ]
					  
				},  
				 
				"FieldNames": [  "Date", "TargetingLocationName", "TargetingLocationId" , "AvgImpressionPosition" , "AvgClickPosition" , "Impressions" ], 
				"Page": {  
				  "Limit": 10000
				},
				 
				"ReportName": "'.$name.'", 
				"ReportType":  "CUSTOM_REPORT"  ,  
				"DateRangeType":   "CUSTOM_DATE"  ,  
				"Format":   "TSV"  ,  
				"IncludeVAT":  "YES"  ,   
				"IncludeDiscount":  "YES"  
			  }
			}
				   ';
				   
					  curl_setopt($ch, CURLOPT_POSTFIELDS,$json);  //Post Fields  CAMPAIGN_PERFORMANCE_REPORT 
				    
					  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					  $headers = [
						'POST /json/v5/ads/ HTTP/1.1',
						'Host: api.direct.yandex.com',
						'Authorization: Bearer '.$this->token.'',
						'Accept-Language: ru', 
						'Client-Login: '.$login,
						'Content-Type: application/json; charset=utf-8',
						//'processingMode: online',
					  ];
					  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					  $server_output = curl_exec ($ch);
					  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
					  curl_close ($ch);
					   
					 
					$header = substr($server_output, 0, $header_size);
					$status=substr($header, 9, 3);
					
					
					 
					$dt=explode('retryIn: ',$header);
					$wait = (int)$dt[1]; 
					
					 
					
					if (count($dt)>1 || $status==202  || $status==201)
					{
						 
						sleep(($wait>0 ? $wait : 10));
					}
					else
						$body = substr($server_output, $header_size);
		}
		
		 
		if ($status!=200 && $status!=100)
		 	return [$server_output];
			 
		 
			
		 
		$dt=explode(PHP_EOL,$body);
		unset($dt[0]);
		unset($dt[1]);
		foreach ($dt as $k=>&$vv)
		{
			$vv = explode("	",$vv); 
			if (count($vv)<4) unset($dt[$k]);
		}
		return $dt;
		 
		 
	}
	
}