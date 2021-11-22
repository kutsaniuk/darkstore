<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Users extends BaseRow
{
	private $laws='';
	public $Manager;
	
	public function get_table_cols($type='base')
	{
		if ($type=='confirmation') return array('name'=>'Имя','email'=>'E-mail'    ); 
		elseif ($type=='log') return array('time'=>'Дата','user'=>'Пользователь'    ); 
			
		return array('name'=>'Имя','email'=>'E-mail', 'user_type_id'=>'Тип пользователя'    ); 
	} 
	
	
	
	public function generate_form_rows($class='',$rows='',$placeholder='',$rows_select='' )
	{
		//простые поля  
		if (!is_array($rows)) $rows=array('name'=>'text' ,'email'=>'email','password'=>'text' );
		if (!is_array($placeholder)) $placeholder=array('name'=>'Имя','zone_id'=>'Зона','password'=>'Пароль','manager'=>'Менеджер','email'=>'E-mail', 'user_type_id'=>'Тип пользователя', 'balance'=>'Баланс');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками
		 
		if (!is_array($rows_select)) $rows_select=array( 'user_type_id'=>'user_type', 'zone_id'=>'zone' );
		foreach ($rows_select as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		 
		
		return $form;
	} 
	
	
	public function check_laws($page)
	{
		if ($this->id<1) return false;
		if ($this->user_type_id==6) return true;
		
		
		 
		if (!is_array($this->laws)) 
		{
			$this->laws=array();
			$res = $this->CI->db->get_where('admin_law',array('user_id'=>$this->id))->result_array();
			$res3 = $this->CI->db->get_where('admin_type_law',array('user_type_id'=>$this->user_type_id))->result_array();
			$res2 = $this->CI->db->get_where('admin_pages')->result_array();
			foreach ($res2 as $row) $admin_pages[$row['id']]=$row['url']; 
			foreach ($res as $row) $this->laws[]=$admin_pages[$row['admin_pages_id']];
			foreach ($res3 as $row) $this->laws[]=$admin_pages[$row['admin_pages_id']];
		}
		
		//print_r($this->laws);
		if (in_array($page,$this->laws)) return true;
		return false;
	}
	
	public function allow_edit_public_rows()
	{
		return  array('name','skype','suname'  ,'email','email2','tel2','tel' );
	}
	
	public function generate_public_form_rows($class='')
	{
		return  $this->generate_form_rows($class , 
		
		array('name'=>'text' ,'suname'=>'text' , 'tel'=>'text', 'tel2'=>'text','email'=>'email', 'email2'=>'text','skype'=>'text' ),
		
		array('name'=>l('Имя'),'suname'=>l('Фамилия'), 'tel'=>'Мобильный телефон', 'tel2'=>'Дополнительный телефон' , 'email2'=>'Дополнительный E-mail','skype'=>'Skype','email'=>l('E-mail'),'country_id'=>l('Страна'),'subscription_email'=>l('Подписка на E-mail'),'subscription_sms'=>l('Подписка на SMS'),'timezone'=>'Временная зона') ,array( ));
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'users',$id); 
		
		
		
		if ($id>0)
		{
			if (strlen($this->timezone)>0)
			{
				date_default_timezone_set($this->timezone);
			}
		}
		
		if ($this->manager>0) $this->Manager=new Users($this->CI,$this->manager);
	} 
	
	public function get_table_cols_template()
	{
		return array( 'user_type_id'=>'select_user_type','time'=>'time','user'=>'select_users','opt_graph_balance'=>'<a target="_blank" href="/admin55/graph/user_balance/[id]">Посмотреть график</a>','opt_logs'=>'<a target="_blank" href="/admin/logs/[id]/user_balance">Транзакции</a>'); 
	} 
	
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		
		if (isset($template[$key])) $template[$key]=str_replace('[id]',$row['id'],$template[$key]);
		
		if (isset($template[$key])) {
			if (strpos($template[$key],'select_')!==false) { 
				$table = row(substr($template[$key],7),$row[$key]);
				return $table['name'];
			}
			elseif ($template[$key]=='time') return date('d.m.Y H:i',$row[$key]);
			elseif (isset($row[$key])) return str_replace('[val]',$row[$key],$template[$key]);//возвращаем шаблон со значением
			else return $template[$key];//возвращаем шаблон без значения
		}
		return $row[$key];
	}
	
	public function check_cookie()
	{
		$row = $this->CI->db->get_where('users',array('id'=>get_cookie('user_id')))->row_array();
		if (md5($row['password'].$row['email'])==get_cookie('hash'))
		{
			$this->construct($this->CI,'users',$row['id']); 
			$this->set_session();
			return true;
		}
		return false;	
	}
	
	
	public function logout()
	{
		$this->CI->session->unset_userdata('user_id');
		$this->CI->input->set_cookie('user_id', '', 0, base_domain() , '/' );
		$this->CI->input->set_cookie('hash','', 0, base_domain() , '/' );
	}
	
	
	
	public function set_session($remember=0)
	{
		 
		$this->CI->session->set_userdata('user_id', $this->id);
		
		if ($remember)
		{ 
			$this->CI->input->set_cookie('user_id', $this->id, 30*24*3600, base_domain() , '/' );
			$this->CI->input->set_cookie('hash', md5($this->password.$this->email), 30*24*3600, base_domain() , '/' ); 
		}
	}
	
	public function login($login,$password,$remember=0)
	{ 
		$row =  $this->CI->db->get_where('users',array('email'=>$this->CI->db->escape_str($login),'password'=>md5($password)))->row_array();
		
		
		if ($row['id']>0)
		{
			$this->construct($this->CI,'users',$row['id']); 
			
			$this->set_session($remember);
			return true;
		}
		return false;
	}
	
	public function ulogin($params)
	{
		
		 
		 
		if (isset($params['email']) && strlen($params['email'])>0)
			$row = $this->CI->db->get_where('users',array('email'=>$params['email']))->row_array();
		
		
		
		if (!isset($row['id'])  )
			$row = $this->CI->db->get_where('users',array('network_id'=>$params['identity'],'network'=>$params['network']))->row_array();
		if ($row['id']>0)
		{
			$this->construct($this->CI,'users',$row['id']); 
			$this->set_session();
			return true;
		}
		$this->network_id=$params['identity'];
		$this->network=$params['network'];
		if (isset($params['email'])) $this->email=$params['email']; else $this->email=$params['network'].$params['identity'];
		$this->name=$params['first_name'].' '.$params['last_name'];
		$this->password=$params['network'].$params['identity']; 
		$this->save();
		$this->set_session();
		return true;
	}
	
	public function get_recovery_url($login)
	{ 
		$row = $this->CI->db->get_where('users',array('email'=>$this->CI->db->escape_str($login)))->row_array();
		if ($row['id']>0)
		{
			return array('status'=>true,'result'=>URI_PROTOCOL.base_domain().'/site/recovery/'.$row['id'].'/'.md5($row['password'].$row['email'])); 
		} 
		$this->CI->lang->load('system', $this->get_language()); 
		return array('status'=>false,'error'=> $this->CI->lang->line('email_not_valid'));	
	}
	
	public function recovery_password($user_id,$code)
	{ 
		$row = row('users', $user_id);
		if ($row['id']>0)
		{
			if (md5($row['password'].$row['email'])==$code)
			{
				$this->construct($this->CI,'users',$row['id']); 
				$password=rand(100000,99999999999);
				$this->password=md5($password);
				$this->save();
				return array('status'=>true,'password'=>$password,'email'=>$row['email']);
			} 
		} 
		$this->CI->lang->load('system', $this->get_language());  
		return array('status'=>false,'error'=>$this->CI->lang->line('code_not_valid'));
	}
	
	public function register($array)
	{ 
		$this->CI->load->helper('email');
		$this->CI->lang->load('system', $this->get_language()); 
		$err='';
		if (!isset($array['user_type_id'])) $array['user_type_id']=4;
		else {
			$user_type = row('user_type',$array['user_type_id']);
			if ($user_type['allow_register']!=1) $array['user_type_id']=4;
		} 
		unset($array['id']);
		unset($array['balance']);
		unset($array['g-recaptcha-response']);
		
		
		$row = $this->CI->db->get_where('users',array('email'=>$this->CI->db->escape_str($array['email']) ))->row_array();
		if ($row['id']>0) $err=$this->CI->lang->line('email_exists'); 
		 
		 
		
		if (strlen($array['password'])<6) $err=$this->CI->lang->line('password_short'); 
		if ($array['password']!=$array['password2'] && isset($array['password2'])) $err=$this->CI->lang->line('password2not_match');  
		if (strlen($array['email'])<3 || !valid_email($array['email']) ) $err=$this->CI->lang->line('email_not_valid');  
		
		unset($array['password2']);
		$array['password']=md5($array['password']);
		
		if (strlen($err)<1)
		{
			if ($array['timezone']>0) $array['timezone']='+'.(int)$array['timezone'];
			$array['timezone']='Etc/GMT'.$array['timezone'];
			$this->update($array);
			return array('status'=>true,'mes'=>$this->CI->lang->line('register_succes'));
		} 
		return array('status'=>false,'mes'=>$err);
	}
	
	public function update($array)
	{ 
		
		if (count($_FILES))
		{ 
			foreach ($_FILES as $k=>$v)
			{
				
				if (strlen($_FILES[$k]['name'] )>1)
				{
					$file_name = $this->img_upload($k,'./upload/post/');
					if (!is_array($file_name)) $array[$k]='post/'.$file_name; 
					else $result[]=$file_name['error'];
					 
					
				}
				
			} 
		}
		
		if ($this->password!=$array['password'] && strlen($array['password'])>0)
		{
			$this->password=md5($array['password']); 
		}
	 
		unset($array['password']);
		//обычное сохранение
		foreach ($array as $k=>$v) {
			if ($k=='balance') $this->set_balance($v);
			else $this->$k=$v;  
		}
		$this->save();
	}
	
	public function set_balance($val,$comment='')
	{ 
		$change=$val-$this->balance;
		if ($change!=0)$this->CI->db->insert('user_balance',array('user'=>$this->id,'time'=>time(),'balance'=>$val,'change_balance'=>$change,'comment'=>$comment));
		
		return $this->balance=$val;
	}
	
	public function get_language_id()
	{
		$language=$this->get_language();
		$row = $this->CI->db->get_where('language',array('name'=>$language))->row_array();
		return (int)$row['id'];
	}
	
	public function get_url_language($return_row=false)
	{
		$host=explode('.',$_SERVER['HTTP_HOST']);
		if (count($host)>=3) {
			$lang_uri=$host[0];
			$row = $this->CI->db->get_where('language',array('url'=>$lang_uri,'active'=>1))->row_array();
			if (isset($row['id'])) return $row['name'];
		}
		
		return DEFAULT_LANGUAGE; 
	}
	
	public function set_language($language)
	{ 
		$row = $this->CI->db->get_where('language',array('name'=>$language,'active'=>1))->row_array();
		if (isset($row['id'])) redirect('//'.$row['url'].base_domain().$_SERVER['REQUEST_URI']); 
	}
	
	public function get_language()
	{
		return $this->get_url_language(); 
		/*
		$domain_lang = $this->get_url_language(); 
		$language = $this->CI->session->userdata('language');
		if (strlen($language)) return $language;
		if (!isset($this->id)) $language = $domain_lang; 
		elseif (isset($this->properties['language']) && strlen($this->properties['language'])>0)  $language = $this->properties['language']; 
		elseif ($this->id>0)
		{ 
			$this->language = $domain_lang; 
			$this->save(); 
			$language = $domain_lang;
		} else $language = $domain_lang;
		
		$this->CI->session->set_userdata('language', $language);
		
		if ($domain_lang!=$language) {
			$row = $this->CI->db->get_where('language',array('name'=>$language))->row_array();
			redirect('//'.$row['url'].base_domain().$_SERVER['REQUEST_URI']);
		}
		
		return $language;
		*/
		
	}
	 
	public function get_graph_balance($date_start=0,$date_end=0)
	{
		if ($date_start==0) $date_start=time()-3600*24*30;
		if ($date_end==0) $date_end=time();
		
		$res =$this->CI->db->get_where('user_balance',array('user'=>$this->id,'time >'=>$date_start,'time <='=>$date_end ))->result_array();
		$list_date=$list_val=array(); 
		 
		foreach ($res as $row)
		{
			$date=date('Y-m-d',(int)($row['time']/3600/24)*3600*24);
			$list_date[$date]=$date;
			$list_val[$date]+=$row['balance'];
		}
		return array('Date'=>$list_date,'Balance'=>$list_val);
	
	}
	
	//подтверждение каперства
	public function confirmation_get()
	{
		return $this->get_all(5000,0,'id','desc',array('user_type_id'=>5));
	}
	
	public function confirmation_confirm()
	{
		$this->user_type_id=2;
		$this->save();
		return 'Одобрен';
	}
	
	public function confirmation_decline()
	{
		$this->user_type_id=4;
		$this->save();
		return 'Отклонен';
	}
	
	public function get_log($type='user_balance')
	{
		if ($type=='xxxx') {
			return 'xx';//тест
		}
		
		 
			if ($this->id>0) $this->CI->db->where('user',$this->id);
			$this->CI->db->order_by('time','desc');
			return $this->CI->db->get('user_balance')->result_array();
		 
		
		
	}
}