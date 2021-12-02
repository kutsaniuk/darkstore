<?php
 
 
defined('BASEPATH') OR exit('No direct script access allowed');
//error_reporting( E_ALL );
class Cron extends CI_Controller {
	
	public function test()
	{ 
		 
		$Checkbox =  new Broniboy( );
		
		
		
		$res = $Checkbox->create_order(['zone'=>row('zone',1),'order'=>row('orders',3) ]);
		print_r($res); 
	}
	 
	 
	 
	public function cron_min1()
	{
		$Checkbox_main = (new Checkbox($this->config->item('checkbox_client_id'),$this->config->item('checkbox_secret')));
		 
		$orders=$this->db->where('status',0)->get('orders')->result_array(); 
		foreach ($orders as $order)
		{
			$partner = row('partners', $order['partner_id']);
			if ($partner['checkbox_client_id']) $Checkbox  = (new Checkbox($partner('checkbox_client_id'),$partner('checkbox_secret')));
			else $Checkbox  = $Checkbox_main;
			
			if ($order['lat']==0 || $order['lng']==0 )
			{
				 
				$address = "https://maps.google.com/maps/api/geocode/json?key=".$this->config->item('google_api_key')."&address=".urlencode($order['address']);
				$Res = json_decode(file_get_contents($address));
				$loc = $Res->results[0]->geometry->location;
				 
				if (isset($loc->lat))
				{
					$order['lat']=$loc->lat;
					$order['lng']=$loc->lng;
					
					$this->db->where('id',$order['id'])->update('orders',[ 'lat'=>$loc->lat, 'lng'=>$loc->lng ]);
				}
				
			}
			 
			$Res_id = $Checkbox->create_order(['zone'=>row('zone',$order['zone']),'order'=>$order ]);
			if ($Res_id)
			{
				$this->db->where('id',$order['id'])->update('orders',['status'=>1,'delivery_id'=>$Res_id]);
			}
			else {
				$this->db->where('id',$order['id'])->update('orders',['status'=>4]);
			}
			 
		}
		
		//трекинг
		$orders=$this->db->where('status >',0)->where('status <',3)->get('orders')->result_array(); 
		foreach ($orders as $order)
		{ 
			$partner = row('partners', $order['partner_id']);
			if ($partner['checkbox_client_id']) $Checkbox  = (new Checkbox($partner('checkbox_client_id'),$partner('checkbox_secret')));
			else $Checkbox  = $Checkbox_main;
			
			$update = $Checkbox->track_order($order['delivery_id']);
			if (count($update))
			{
				$this->db->where('id',$order['id'])->update('orders',$update);
				
				if ($update['status']!=$order['status'] && $update['status_text'])
				{
					$telegram = new Telegram($this->config->item('telegram_bot')); 
					$content = array('chat_id' => $partner['chat_id'],  'text' =>"Изменение статуса заказа {$order['id']} ".$update['status_text']  ); 
					$telegram->sendMessage($content);
					
					
				}
			} 
		}
		
		
	}
	
}
