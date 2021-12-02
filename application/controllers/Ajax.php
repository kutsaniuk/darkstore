<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

	function get_base_data()
	{
		return array(
		'path'=>'/application/views/site/' 
		);
	}
	 
	public function get_base_admin_ajax()
	{
		$user = check(); 
		if ($user->user_type_id!=1 && $user->user_type_id!=6) { $user->logout; redirect2('/login'); } 
	}
	
	public function get_order_info( )
	{
		$dt=[];
		foreach ((new Orders($this))->get_all() as $row)
		{
			?>
			<script>$('#td<?=$row['id']?>_status_info').html('<?=(new Orders($this,$row['id']))->status_info()?>');</script>
			<?
		}
			 
	}
	
	public function get_compaigns()
	{
		?>
		<select   class="form-control" OnChange="ajax('get_adgroups','id='+this.value,'#add_form_group');$('#f_compaign_id').val(this.value);" name="compaign_id">
			<option value="0" >-- Любая кампания --</option>
		<?foreach ((new BaseRow( $this,'ya_campaigns'))->get_all(999,0,'name','asc',['account_id'=>(int)$_POST['id']]) as $client):?> 
			<option  <?=($_GET['compaign_id']==$client['id']?'selected':'' ) ?>  value="<?=$client['id']?>" ><?=$client['name']?> #<?=$client['id']?></option>
		<?endforeach;?>
		</select>
		<?
	}
	 
	
	public function recovery()
	{  
		$user = new Users($this); 
		//if (isset($_POST['g-recaptcha-response'])) 
		//{
			if (!check_recapcha($this->input->post('g-recaptcha-response'))) {
				$this->lang->load('system', $user->get_language()); 
				echo $this->lang->line('recaptcha_invalid');	
				die();
			}
		//}
		$res = $user->get_recovery_url($this->input->post('email'));
		
		if ($res['status'])
		{ 
			$this->lang->load('system', $user->get_language()); 
			send_mail2($this->input->post('email'),$this->lang->line('password_recovery_request'),
			str_replace('[you_link]',$res['result'],$this->lang->line('password_recovery_request_email_text'))
			,vars('email'));
			echo $this->lang->line('recovery_email_send');	
		} 
		else  $res['error'];	
		 
	}
	
	public function login()
	{ 
		
		
		$user = new Users($this); 
		/*
		if (isset($_POST['g-recaptcha-response'])) 
		{
			if (!check_recapcha($this->input->post('g-recaptcha-response'))) {
				$this->lang->load('system', $user->get_language()); 
				echo $this->lang->line('recaptcha_invalid');	
				die();
			}
		}
		*/
		
		$res = $user->login($this->input->post('email'),$this->input->post('password') );
		if ($res)
		{ 
			if ($this->input->post('remember')) $remember='?remember=1';
			else $remember='';
			 redirect_js('/'.$remember); 
		} 
		else {
			$this->lang->load('system', $user->get_language()); 
		
			echo $this->lang->line('login_not_match').' <script>grecaptcha.reset();</script>';	
		}
		
		
	}
	 
	
	public function register()
	{ 
		$user = new Users($this);
		if (isset($_POST['g-recaptcha-response'])) 
		{
			if (!check_recapcha($this->input->post('g-recaptcha-response'))) {
				$this->lang->load('system', $user->get_language()); 
				echo $this->lang->line('recaptcha_invalid');	
				die();
			}
		}
		 
		$res = $user->register($this->input->post());
		if ($res['status'])
		{
			$user->set_session();
			redirect_js('/');
		} 
		else echo $res['mes'].' <script>grecaptcha.reset();</script>';
	}
	
	  
	
	public function save($model_name,$id=0)
	{
		
		$user=check();
		if ($user->id<1)  die(l('Вы не авторизованы'));
		
		if (in_array($model_name,array('Delivery')) && 	$user->user_type_id!=2) die(l('Вы не каппер'));
		
		$model = new $model_name($this,$id);
		
		if (in_array($model_name,array('Delivery')) && 	$model->user_id!=$user->id && $id>0) die(l('Это не ваша рассылка'));
		elseif (in_array($model_name,array('Delivery'))) $model->user_id=$user->id;
		
		$update=array();
		foreach ($this->input->post() as $k=>$v)
		{
			if (strlen(trim(strip_tags($v)))<1) die(l('Вы заполнили не все поля'));
			
			if (in_array($k,$model->allow_edit_public_rows() ) && $model->$k!=$v) {
				
				$update[$k]=strip_tags($v);
			}
			
		}
			
		
		
		if (count($update))
		{
			$res = $model->update($update);
			if (strlen($res)>0) die( $res);
			$model->save();
			
			echo l('Данные успешно сохранены'); 
			if ($model_name=='Delivery') redirect_js('/my-services');
		}
		else echo l('Не было введено изменений'); 
	}
	
	
	
	public function save_profile()
	{ 
		
		
		$user=check();
		if ($user->id<1)  die(l('Вы не авторизованы'));
		$update=array();
		foreach ($this->input->post() as $k=>$v)
			if (in_array($k,$user->allow_edit_public_rows() ) && $user->$k!=$v) $update[$k]=$v;
			
		if (isset($update['email']))
		{
			$email_user=$this->db->get_where('users',array('email'=>$update['email']))->row_array();
			if ($email_user['id']!=$user->id && $email_user['id']>0) die(l('Пользователь с таким E-mail уже существует.'));
		}
		
		
		if (count($update))
		{
			$user->update($update);
			$user->save();
			
			echo l('Данные успешно сохранены'); 
		}
		else echo l('Не было введено изменений'); 
	}
	 
	public function save_password()
	{ 
		
		
		$user=check();
		if ($user->id<1)  die(l('Вы не авторизованы'));
		
		$this->lang->load('system', $user->get_language()); 
		
		if ($user->password!=md5($this->input->post('old_password'))) die(l('Старый пароль введен не верно'));
		if ($this->input->post('password')!=$this->input->post('password2')) die(l('Пароли не совпадают'));
		if (strlen($this->input->post('password'))<6) die(l('Пароль не должен быть меньше 6 символов'));
		
		$user->password=md5($this->input->post('password'));
		$user->save();
		
		echo l('Пароль успешно изменен'); 
	}
	
	public function admin_confirm($model_name,$id,$type)
	{
		$data = $this->get_base_admin_ajax();
		
		$data['model_name'] = mb_convert_case($model_name, MB_CASE_TITLE, "UTF-8");
		$data['model'] = new $data['model_name']($this,$id);
		if ($type=='accept') $data['model']->confirmation_confirm();
		else $data['model']->confirmation_decline();
		echo ' ';
	}
}
