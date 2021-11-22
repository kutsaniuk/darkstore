<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	
	function get_base_data()
	{
		return array(
		'path'=>'/application/views/site/' 
		);
	}
	
	function error($text)
	{
		echo json_encode(['status'=>'error','error'=>$text]);
			die();
	}
	
	function auth()
	{
		$api_key=$_GET['api_key'];
		$row=$this->db->get_where('users',['api_key'=>$api_key])->row_array();
		if ($row['id']==0) $this->error('Invalid api_key'); 
		return (new Users($this,$row['id']));
	}
	
	function products($id=0)
	{
		$User = $this->auth(); 
		$post = json_decode(file_get_contents('php://input'));
		$method = $_SERVER['REQUEST_METHOD'];
		
		if (  $method=='DELETE'  )
		{ 
					$Products = new Products($this,$id);
					if ($id>0 && $User->id!=$Products->partner_id) $this->error('Invalid id'); 
					$Products->delete(); 
			 
			echo json_encode(['status'=>'OK' ]);
			die();
		}
		elseif (  $method=='POST' || $method=='PUT')
		{ 
					$Products = new Products($this,$id);
					if ($id>0 && $User->id!=$Products->partner_id) $this->error('Invalid id');
					$arr=[];
					$arr['price']=$item->price_value;
					$arr['name']=$item->name;
					$arr['text']=$item->description;
					$arr['custom_id']=$item->custom_id;
					$arr['img']=$item->image_url;
					$cur = $this->db->get_where('valut',['name'=>$item->price_currency])->row_array();
					if ($cur['id']==0) $cur['id']=1;
					$arr['cur']=$cur['id'];
					$arr['partner_id']=$User->id;
					$Products->update($arr); 
			 
			echo json_encode(['status'=>'OK','product'=>$Products->get_object()]);
			die();
		}
		elseif ($id==0)
		{
			$invs=[];
			foreach ((new Products($this))->get_all(999,0,'id','desc',['partner_id'=>$User->id]) as $row)
			{
				$Inv = new Products($this,$row['id']);
				$invs[]=$Inv->get_object();
			}
			echo json_encode(['status'=>'OK','products'=>$invs]);
			die();
		}
		else {
			$Inv = new Products($this,$id);
			if($Inv->partner_id!=$User->id) $this->error('Invalid id');
			$inv=$Inv->get_object();
			echo json_encode(['status'=>'OK','product'=>$inv]);
			die();
		}
		
	}
	
	function invoices($id=0)
	{
		$User = $this->auth(); 
		$post = json_decode(file_get_contents('php://input'));
		if ($id==0)
		{
			$ids=[];
			if (!isset($post->invoice->items)) $this->error('Items is empty'); 
			foreach ($post->invoice->items as $item)
			{
				
				if ($item->product_id>0) 
				{
					$Products = new Products($this,$item->product_id);
					if ($Products->partner_id!=$User->id) $this->error('Invalid product_id'); 
				} 
				else {
					$Products = new Products($this);
					$arr=[];
					$arr['price']=$item->price_value;
					$arr['name']=$item->name;
					$arr['img']=$item->image_url;
					$cur = $this->db->get_where('valut',['name'=>$item->price_currency])->row_array();
					if ($cur['id']==0) $cur['id']=1;
					$arr['cur']=$cur['id'];
					$arr['partner_id']=$User->id;
					$Products->update($arr); 
				}
				$Payer = new Users($this);
				$Payer->auth_soc($post->invoice->gateway_name,time(),$post->invoice->custom,$post->invoice->description,$User->id);
				if ($item->quantity==0) $item->quantity=1;
				for ($i=1;$i<$item->quantity;$i++)
				{
					$Ex = new Exchange($this);
					$Ex->payed($Products->id,$Payer->id,'api'); 
					$ids[]=get_object_vars($Ex);
				}
				
			}
			
			echo json_encode(['status'=>'OK','invoices'=>$ids]);
			die();
		}
		else {
			$Inv = new Exchange($this,$id);
			if($Inv->partner_id!=$User->id) $this->error('Invalid Incvoice id');
			$inv=$Inv->get_object();
			echo json_encode(['status'=>'OK','invoice'=>$inv]);
			die();
		}
		
	}
	
	function send($test=1) 
	{
		echo $test;
		 
		
	}
	
}
 
		 