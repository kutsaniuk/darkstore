<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
require_once(APPPATH.'libraries/RestController.php');
require_once(APPPATH.'libraries/Format.php');
 
use chriskacerguis\RestServer\RestController;	 

class Api_Client extends  RestController {
	
	function auth()
	{
		//testing
		 
		//$data = $this->input->request_headers();
		 $api_key = $_GET['api_key'];
		if (  strlen($api_key)<1  )
			return $this->response(array('error' => 401, 'message'=>'Отсутствует api_key'), 401);
		
		$row = $this->db->get_where('partners',['api_key'=>$api_key])->row_array();
		
		 
		if ($row['id']<1   )
			return $this->response(array('error' => 401, 'message'=>'Не правильный api_key'), 401);
		
		return $row;
	}
	 
	
	function zones_get()
	{
		$row = $this->auth();
		
		
		$zones  = [];
		 
		foreach ($this->db->get('zone')->result_array() as $row)
		{
			$Z = new stdClass();
			$Z->id	= $row['id'];
			$Z->name = $row['name'];
			$Z->time = $row['time'];
			$Z->coordinates=[];
			
			$coordinates_longitude = explode(',',$row['coordinates_longitude']);
			$coordinates_latitude = explode(',',$row['coordinates_latitude']);
			
			foreach ($coordinates_longitude as $k=>$v)
			{
				$Z->coordinates[]=(object)['longitude'=>$v,'latitude'=>$coordinates_latitude[$k]];
			}
			
			$zones[]=$Z;
		}
		 
		   
		$this->response($zones);
	}	
	
	function nomenclature_post()
	{
		$user = $this->auth();
		$api_results = [];
		
		$postData = file_get_contents('php://input');
		 
		$data = json_decode($postData, true);
		 
		if (json_last_error() != JSON_ERROR_NONE) 
			return $this->response(array('error' => 'Ошибка в вашем JSON'), 404);
		
		foreach ($data as $k=>$row)
		{
			if (!empty($row['barcode'])) $row['code']=$row['barcode'];
			if (empty($row['code'])) 
				return $this->response(array('error' => 'Отсутствует code в строке '.$k), 404);
			if (empty($row['name'])) 
				return $this->response(array('error' => 'Отсутствует name в строке '.$k), 404);
			if (empty($row['id'])) 
				return $this->response(array('error' => 'Отсутствует id в строке '.$k), 404);
			
			$this->db->query("
			INSERT INTO item_type SET name= ? , code= ? , partner_id= ? , partner_item_id = ?
				ON DUPLICATE KEY UPDATE name= ? ;
			",[$row['name'],$row['code'],$user['id'],$row['id'],$row['name']]);
		}
		
		$this->response(['status'=>'ok']);
	}	
	
	function availability_get()
	{
		$user = $this->auth();
		$api_results = [];
		
		foreach ($this->db->get('zone')->result_array() as $zone)
		{
			$Z = (object)(['id'=>$zone['id'],'nomenclarure'=>[]]);
			
			foreach ($this->db->get_where('item_type',['partner_id'=>$user['id']])->result_array() as $item_type)
			{
				$amount = $this->db->query("SELECT SUM(count) sum FROM item WHERE item_id = '{$item_type['id']}' AND zone_id = '{$zone['id']}' ")->row_array();
				$Z->nomenclarure[]=(object)['id'=>$item_type['partner_item_id'] , 'count'=>(int)$amount['sum']];
			} 
			$api_results[]=$Z;
			
		}
			
		$this->response($api_results);
		  
		
	}	
	
	function order_get($order_id)
	{
		$user = $this->auth();
		$api_results = [];
		
		$Orders = new Orders($this,$order_id);
		
		if ($Orders->partner_id!=$user['id'])
			return $this->response(array('error' => 'Не верный ID заказа'), 404);
		
		$api_results['id']=$Orders->id;
		$api_results['status']=$Orders->status;
		
			
		$api_results['text']=$Orders->status_info();
			
		$this->response($api_results);
		  
		
	}
	
	function order_post()
	{
		$user = $this->auth();
		$api_results = [];
		
		$postData = file_get_contents('php://input');
		 
		$data = json_decode($postData, true);
		 
		if (json_last_error() != JSON_ERROR_NONE) 
			return $this->response(array('error' => 'Ошибка в вашем JSON'), 404);
		
		foreach (['number','address','zip','zone' ] as  $row)
		{ 
			if (empty($data[$row])) 
				return $this->response(array('error' => 'Отсутствует параметр '.$row ), 404); 
		}
		 
		
		foreach ($data['nomeclature'] as $nomeclature)
		{
			$item_type = $this->db->get_where('item_type',['partner_item_id'=>(int)$nomeclature['id'],'partner_id'=>$user['id']])->row_array();
			if ($item_type['id']<1)
				return $this->response(array('error' => 'Не верный ID товара '.$nomeclature['id'] ), 404); 
			
			if ($nomeclature['count']<1)
				return $this->response(array('error' => 'Число товаров не может быть меньше 1' ), 404); 
			
			//проверяем сумму товаров 
			$amount = $this->db->query("SELECT SUM(count) sum FROM item WHERE item_id = '{$item_type['id']}' AND zone_id = ? ",[(int)$data['zone']])->row_array();
			if ($amount['sum']<$nomeclature['count'])
				return $this->response(array('error' => 'Такого количества товаров нет в указанной вами зоне' ), 404); 
		}
		
		
		
		$Orders = new Orders($this);
		$Orders->number = $data['number'];
		$Orders->address = $data['address'];
		$Orders->partner_id = $user['id'];
		$Orders->zip = $data['zip'];
		$Orders->zone = $data['zone'];
		$Orders->nomeclature_json = json_encode($data['nomeclature']);
		
		if ($data['rush']) $Orders->status=0; else $Orders->status=-1;
		
		$text=[];
		
		foreach ($data['nomeclature'] as $nomeclature)
		{
			$item_type = $this->db->get_where('item_type',['partner_item_id'=>(int)$nomeclature['id'],'partner_id'=>$user['id']])->row_array();
			
			$count=$nomeclature['count'];
			
			foreach ($this->db->query("SELECT * FROM item WHERE count>0 AND item_id = '{$item_type['id']}' AND zone_id = ? ",[(int)$data['zone']])->result_array() as $item)
			{
				if ($count>0)
				{
					$del_count=$count;
					if ($del_count>$item['count']) $del_count=$item['count'];
					$count-=$del_count;
					$this->db->query("UPDATE item SET count=count-'$del_count' WHERE id='{$item['id']}' ");
					
					$text[]="{$item_type['name']} ({$del_count} шт) стеллаж {$item['rack']} полка {$item['shelf']} ";
				}
			}
		 
		}
		
		$Orders->nomeclature= implode(PHP_EOL.'<br>',$text);
		$Orders->save();
		
		
		if ($Orders->id>0) 
			$this->response(['status'=>'ok','id'=>$Orders->id]);
		else 
		{
			addlog(json_encode($data).'____'.json_encode(debug_backtrace()));
			return $this->response(array('error' => 'Произошла непредвиденная ошибка! Сообщите нашему администратору указав json' ), 404); 
		}
			
		
	}	

	 
}
 
		 