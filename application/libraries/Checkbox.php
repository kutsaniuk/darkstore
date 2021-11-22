<?php 

defined('BASEPATH') OR exit('No direct script access allowed');
 
class Checkbox  extends Delivery
{
	
	
	function __construct($client_id,$client_secret)
	{
		
		$this->url='https://api.partner.checkbox.ru/api/v1';
		//$this->url='https://sandbox.partner.checkbox.ru/api/v1';
		
		$this->client_id=$client_id;
		$this->client_secret=$client_secret;
		
		$this->token = $this->token();
	}
	
	function token()
	{
		$CI =& get_instance();
		 
		$token = $CI->db->get_where('delivery_service',['name'=>'checkbox'])->row_array();
		if ($token['expire']>time()) return $token['token'];
		 
		$Res = $this->request('/oauth2/token',['grant_type'=>'client_credentials','audience'=>$this->url,'scope'=>'api:full','client_id'=>$this->client_id,'client_secret'=>$this->client_secret],1,['content-type: application/x-www-form-urlencoded'],'https://auth.partner.checkbox.ru');
		
		
		if ($Res->access_token)
		{
			$CI->db->where('name','checkbox')->update('delivery_service',['expire'=>time()+$Res->expires_in,'token'=>$Res->access_token]);
			 
			return $Res->access_token;
			
		}
	}
	
	function create_order($data=[])
	{
		
		$express_dropoff_upper_time=time()+($data['zone']['time'] ? $data['zone']['time'] : 120)*60;
		
		$Request = (object)['order'=> (object)[
			'city_id'=>'MSK',
			'express'=>true,
			'express_dropoff_upper_time'=>date('Y-m-d',$express_dropoff_upper_time).'T'.date('H:i:s',$express_dropoff_upper_time).'Z',
			 
			'pickup_waypoint'=>(object)[
				'address_base'=> $data['zone']['address'],
				'address_addition'=>"",
				'coordinates'=>(object)[
					'latitude'=>(real)$data['zone']['latitude'],
					'longitude'=>(real)$data['zone']['longitude']
				],
				'contact_phone'=>$data['zone']['contact_phone']
			],
			'dropoff_waypoint'=>(object)[
				'address_base'=> $data['order']['address'],
				'address_addition'=>"",
				'coordinates'=>(object)[
					'latitude'=>(real)$data['order']['lat'],
					'longitude'=>(real)$data['order']['lng']
				],
				'contact_phone'=>$data['order']['number']
			],
			'comment_for_courier'=>''
			
		]];
		
		
	 
		 $Request=json_encode($Request);
		$Res = $this->request('/orders',$Request,2,['Authorization: Bearer '.$this->token]);
	 
		if (!isset($Res->order->id))
		{
			if (strlen($Res->message) )
			{
				$this->db->where('id',$data['order']['id'])->update('orders',['status'=>4,'status_text'=>$Res->message]);
			}
			
			addlog('Ошибка создания заказа! '.print_r($Res,1));
			
			return false;
		}
		return $Res->order->id;
	}
	
	function cancel_order($id)
	{
		$Res = $this->request('/orders/'.$id.':cancel',json_encode((object)['reason'=>'OTHER']),2,['Authorization: Bearer '.$this->token]);
	}
	
	function track_order($id)
	{
		$Res = $this->request('/orders/'.$id.':track','',0,['Authorization: Bearer '.$this->token]);
		
		
		if ($Res->order_track->delivery_status=='UNSPECIFIED')
		{
			return ['status_text'=>' '];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_ASSIGNED')
		{
			return ['status'=>1,'status_text'=>'Курьер назначен<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_ON_WAY_TO_PICKUP')
		{
			return ['status'=>1,'status_text'=>'курьер движется на точку приема<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_ARRIVED_AT_PICKUP')
		{
			return ['status'=>1,'status_text'=>'курьер прибыл на точку приема<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_PICKED_UP')
		{
			return ['status'=>2,'status_text'=>'курьер посетил точку приема и получил заказ от отправителя<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_ON_WAY_TO_DROPOFF')
		{
			return ['status'=>2,'status_text'=>'курьер движется на точку выдачи<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_ARRIVED_AT_DROPOFF')
		{
			return ['status'=>2,'status_text'=>'курьер прибыл на точку выдачи<br>'.$Res->order_track->courier->name.' '.$Res->order_track->courier->phone];
		}
		elseif ($Res->order_track->delivery_status=='COURIER_DROPPED_OFF')
		{
			return ['status'=>3,'status_text'=>''];
		}
		elseif ($Res->order_track->delivery_status=='CANCELLED')
		{
			return ['status'=>4,'status_text'=>'Доставка отменена<br>'.$Res->delivery_status_details->cancel->reason];
		}
		addlog('Ошибка трекинга заказа! '.$id.'___'.print_r($Res,1));
		return []; 
	} 
}