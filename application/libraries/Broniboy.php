<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Broniboy  extends Delivery
{
	
	
	function __construct($token='j2e3ueqjCIowqiSuzEKVnE47bJJX2dWG') //тестовый токен
	{
		
		// $this->url='https://api.broniboy.ru/v1.0b2b';
		$this->url='https://dev.broniboy.ru/v1.0b2b';
		
		$this->token=$token;  
	}
	 
	function create_order($data=[])
	{
		
		$express_dropoff_upper_time=time()+($data['zone']['time'] ? $data['zone']['time'] : 120)*60;
		
		$Request = (object) array(
			   'pickup' => 
			  (object) array( 
				 'address' => 
				(object) array(
				   'longitude' => (real)$data['zone']['longitude'],
				   'latitude' => (real)$data['zone']['latitude'],
				   'address' => $data['zone']['address'] 
				), 
			  ),
			   'payment_type' => 'b2b_account',
			   'drop_off' => 
			  array (
				0 => 
				(object) array(
				   'address' => 
				  (object) array(
					 'longitude' => (real)$data['order']['lng'],
					 'latitude' => (real)$data['order']['lat'],
					 'address' => $data['order']['address'] 
				  ),
				   'client_phone' => $data['order']['number']  
				),
			  ),
			); 
		
		
 
		 $Request=json_encode($Request);
		$Res = $this->request('/orders/create',$Request,2,['X-App-Token: '.$this->token]);
		
		if (!isset($Res->id))
		{
			if (strlen($Res->error->message) && $data['order']['id'] )
			{
				$this->db->where('id',$data['order']['id'])->update('orders',['status'=>4,'status_text'=>$Res->error->message]);
			}
			return false;
			addlog('Ошибка создания заказа! '.print_r($Res,1));
		}
		return $Res->id;
	}
	
	function cancel_order($id)
	{
		return false;
	}
 
	function track_order($id)
	{
		$Res = $this->request('/orders/order_id='.$id ,'',0,['X-App-Token: '.$this->token]);
		return ['status'=>1,'status_text'=>$Res[0]->state];
		
		 
	} 
}