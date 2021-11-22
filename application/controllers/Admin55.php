<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin55 extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	function get_base_data($page='')
	{
		
		$user = check(); 
		if ($user->user_type_id!=1 && $user->user_type_id!=6) { $user->logout; redirect2('/login'); } 
		if (strlen($page)>0) if (!$user->check_laws($page)) {  redirect2('/admin55/edit/reports'); } 
		$editors=[
		 
		'users'=>'Пользователи',
		'zone'=>'Зоны доставки',
		//  'options'=>'Настройки'
			 
			];
		//$logs=array('vaucher'=>'Транзакции' );
		//$graphs=array('vaucher'=>'Частота транзакций' );
		if ($user->user_type_id!=6) { unset($editors['users']); unset($editors['admin_pages']);  unset($editors['admin_law']);}
		
		return array(
		'path'=>'/application/views/admin/',
		'editors'=>$editors
		,'user'=>$user
		//,'logs'=>$logs
		//,'graphs'=>$graphs 
		);
	}
	
	public function index()
	{
		 redirect('/admin55/edit/orders');
		 
		 die();
		$data=$this->get_base_data();
		
	 
	 
		$this->load->view('admin/index.php',$data); 
		
	}
	
	public function import_xls()
	{
		
		if (count($_FILES))
		{
			 
			$config['allowed_types']        = '*';
			$config['max_size']             = 1024000; 
			$config['upload_path']          = './upload';
			
			$this->load->library('upload', $config);
			$this->upload->do_upload('file');
			$dt = $this->upload->data();
			
			
			
			
			
			if (!isset($dt['error']) )
			{
				$text=file_get_contents($dt['full_path']);
				 
				foreach (explode(PHP_EOL,$text) as $code)
				if (strlen($code)>0)
				{
					$this->db->insert('code',['code'=>$code,'time_add'=>time()]);
				}				
			 
 
			}
		}
		
		redirect('/admin55/edit/code');
	}
	  
	
	public function login()
	{
		$data['path']='/application/views/admin/';
		$this->load->view('admin/login.php',$data);
	}
	
	public function page($page )
	{
		$data=$this->get_base_data(); 
		
		if ($page=='scaner')
		{
			if (strlen(trim($_POST['code']))>0)
			{
				$item_type = $this->db->get_where('item_type',['code'=>$_POST['code']])->row_array();
				if ($item_type['id'] ) 
				{
					$this->db->insert('item',['zone_id'=>$data['user']->zone_id,'item_id'=>$item_type['id'],'shelf'=>$_POST['shelf'],'rack'=>$_POST['rack'],'count'=>1 ]);
				}
				else 
				{
					$data['result'][]='Товар по этому коду не найден в номенклатуре!';
				}
				 
			}
		} 
		elseif ($page=='new_order')
		{
			if (strlen(trim($_POST['address']))>0 && strlen(trim($_POST['phone']))>0)
			{
				$partner = row('partners',(int)$_POST['partner_id']);
				$Broniboy = new Broniboy($partner['api_broneboy']);
				
				$address = "https://maps.google.com/maps/api/geocode/json?key=".$this->config->item('google_api_key')."&address=".urlencode($_POST['address']);
				$Res = json_decode(file_get_contents($address));
				$loc = $Res->results[0]->geometry->location;
				
				$order['address']=$_POST['address'];
				$order['number']=$_POST['phone'];
				$order['lat']=$loc->lat;
				$order['lng']=$loc->lng;
			 
				$res = $Broniboy->create_order(['zone'=>$partner,'order'=>$order ]);
				
				if (!$res)
				{
					$Checkbox = new Checkbox($partner['api_checkbox_id'],$partner['api_checkbox_secret']);
					$res = $Checkbox->create_order(['zone'=>$partner,'order'=>$order ]);
					
					if (!$res)
						$data['result'][]='Ошибка создания заказа, посмотрите логи';
				}
				else $data['id']=$res;
				 
				 
			}
		} 
		 
		$this->load->view('admin/'.$page.'.php',$data);
		  
	}
	
	public function edit($model_name='',$id=0,$do='')
	{
		 
		$data=$this->get_base_data('edit');
		
		
		if (strlen($model_name)<1) redirect2('/admin55/edit/post'); 
		$data['model_name'] = mb_convert_case($model_name, MB_CASE_TITLE, "UTF-8");
		$data['model'] = new $data['model_name']($this,$id);
		$data['do']=$do; 
		 
		if ($do=='delete')
		{ 
			$data['model']->delete();
			redirect2('/admin55/edit/'.$model_name); 
		}
		
		if ($_GET['send_status'])
		{
			$this->db->where('id',(int)$_GET['send_status'])->where('status',-1)->update('orders',['status'=>0]);
		}
		
		if ($id>0 || $do=='add' || $do=='save')
		{ 
			if ($do=='save')
			{
				$data['result'] = $data['model']->update($_POST); 
			} 
			
			$this->load->view('admin/edit.php',$data);
		}
		else
		{ 
			if ($do=='del_all')
			{
				$this->db->where('id >',0)->delete(strtolower($model_name));
			}
			$this->load->view('admin/'.$data['model']->get_edit_list(),$data);
		}
		 
		
	}
}
