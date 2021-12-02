<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller {
 

	function get_base_data()
	{
		$user = check(); 
		if ($user->id>0) date_default_timezone_set($user->timezone);
		$this->lang->load('site', $user->get_language()); 
		
		if (strlen($_SERVER['HTTP_REFERER'])>0 && strpos($_SERVER['HTTP_REFERER'],'btcbit')===false)
		{
			$_SESSION['HTTP_REFERER']=$_SERVER['HTTP_REFERER'];
		}
		 
		
		//реферальность
		 
		if ($_GET['referal']>0) $this->session->set_userdata('referal', (int)$_GET['referal']);
		 
		 
		return array(
		'path'=>'/application/views/site/' 
		,'system_js'=>'<script src="/js/system_js.js"></script>
		<script src="https://www.google.com/recaptcha/api.js"></script>
		'
		,'user'=>$user
		  
		,'need_login'=>array('profile','referals','partner','enter_code','identify','enter_phone','add_balance','dashboard')
		,'need_capper'=>array('enter_code','add_balance', 'buy_bitcoin' )
		,'title'=>vars('title'),'description'=>vars('description'),'keywords'=>vars('keywords')
		,'not_login'=>array('login','register','recovery')
		,'langs'=>get_all_array((new Language($this))->get_all(50,0,'url','asc',array('active'=>1)),'name','url')
		);
	}
	
	public function logout(){
		//print_r($_SESSION);
		$user = check(); 
		$user->logout();
		//print_r($_SESSION);die();
		redirect2('/');
	}
	  
	public function index()
	{
		 
		redirect2('/admin55');
	}
	
	
	public function confirm_email($user_id,$code)
	{
		$Us = new Users($this,$user_id);
		if ($Us->code==$code) {
			$Us->confirm_email=1;
			$Us->save();
		}
		
		redirect('/');
	}
	
	public function recovery($id,$code)
	{
		$data=$this->get_base_data();
		$user = new Users($this);
		$result = $user->recovery_password($id,$code);
		if ($result['status'])
		{
			$this->lang->load('system', $user->get_language());   
			send_mail2($result['email'],$this->lang->line('password_recovery_success'),
			str_replace('[new_password]',$result['password'],$this->lang->line('password_recovery_email_text'))
			,vars('email'));
			
			$data['text']=$this->lang->line('password_recovery_success');
			$this->load->view('site/info.php',$data); 
		}
		else {
			//код не верен
			$data['text']=$result['error'];
			$this->load->view('site/info.php',$data); 
		}
	}
	   
	
	public function defaultt($page,$page2='')
	{
		 
		$page=urldecode($page);
		$data=$this->get_base_data(); 
		$user= check();
		//Турнир?
		 
		
		//Статья
	
		$post = $this->db->get_where('post',array('url'=>mb_strtolower($page,'utf-8'),'language'=>$user->get_language_id()))->row_array();
		if ($post['id']<1) $post= $this->db->get_where('post',array('url'=>mb_strtolower($page,'utf-8') ))->row_array();
		if ($post['id']>0)
		{
			$pagin_start= isset($_GET['st']) ? $_GET['st'] : 0;
			$data['pagination_count']=vars('pagination_count');
			$data['pagin_start']=$pagin_start;
			$data['post']=new Post($this,$post['id']);
			$data['postes']=$data['post']->get_all($data['pagination_count'],(int)$pagin_start,'time','desc',array('parent'=>$data['post']->id,'language'=>$data['user']->get_language_id()));
			$data['count_post']=$data['post']->get_count_posts();
			$data['title']=$data['post']->title;
			$data['description']=$data['post']->description;
			$data['keywords']=$data['post']->keywords;
			
			$this->load->view('site/post.php',$data);
			return;
			
		}
		elseif ($page!='404') redirect2('/404');
	}
	  
	     
	public function set_hook()
	{
		
		 $telegram_bot=$this->config->item('telegram_bot');
		$res=file_get_contents('https://api.telegram.org/bot'.$telegram_bot.'/setWebhook?url=https://'.$_SERVER['HTTP_HOST'].'/bot/telegram');
		 //die('https://api.telegram.org/bot'.$telegram_bot.'/setWebhook?url=https://'.$_SERVER['HTTP_HOST'].'/bot/telegram');
		die($res);
		
	}
	
	public function get_excel()
	{
		//$spreadsheet_url="https://docs.google.com/spreadsheets/d/e/2PACX-1vRNhQ9AHAOnyJGNG6swrUlr42e4qps4N-U5Sv6ELGT7FD70QuX8J_cdwqgzNBIxYcvjajFBMSCBxQk5/pub?output=csv";
		$spreadsheet_url="ftp://elevele_read:elevele_read@ftp.elevele.ru/chatbot/NewStaff.csv";

		if(!ini_set('default_socket_timeout', 15)) echo "<!-- unable to change socket timeout -->";

		if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				$spreadsheet_data[] = $data;
			}
			fclose($handle);
		}
		
		return $spreadsheet_data;
	}
	
	function finish()
	{
		redirect('https://t.me/likeitNow_bot');
	}
	 
	function pay_url($id)
	{
		$pay = row('pay',(int)$id);
		
 
		redirect('https://sci.interkassa.com/?ik_co_id='.$this->config->item('interkassa_id').'&ik_pm_no='.$id.'&ik_am='.$pay['price'].'&ik_cur=RUB&ik_desc=PAY');
	 
	}
	
	public function bot($type='alisa')
	{ 
		header('Content-Type: application/json');
		
	 
		
	 
		$telegram = new Telegram($this->config->item('telegram_bot')); 
		$result = $telegram->getData(); 
		 
		
		if (isset($result['callback_query'])) {
			$result['message']=$result['callback_query']['message'];
			$result['message']['from']=$result['callback_query']['from'];
			$result['message']['text']=$result['callback_query']['data']; 
			$t = explode('_',$result['message']['text']);
			$result['message']['text']=$t[1];
			$last_quest=$t[0];
			
		}
		$text = $result['message']['text'];
		
		$chat_id = $result['message'] ['chat']['id'];
		$user_id=(int)$result['message']['from']['id']; 
		
		$name=  $result['message']['from']['first_name'].' '. $result['message']['from']['username'];
		$nick=$result['message']['from']['username'];
		//отладка тест 
	 
		 
		$textToCheck=strtolower(trim($text) );
		   
		 
		
		
		try 
		{			
			 
			$answer_text = "ИД ЧАТА ".$chat_id;
		 
			
			$answer_texts=explode('-NEW_MES-',$answer_text);
			$photos=[];
			foreach ($answer_texts as $k=>$answer_text)
			{
				
				$answer_text = str_replace('&nbsp;', ' ',  ($answer_text));
			//	$answer_text = html_entity_decode($answer_text);
			
			
				preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $answer_text, $match);
				if (count($match[0])>0)
				{
					foreach ($match[0] as $photo)
					{
						$photo=strtolower($photo);
						if ( strpos($photo,'.jpg') || strpos($photo,'.png') || strpos($photo,'.gif') || strpos($photo,'.jpeg'))
						{
							$photos[]=$photo;
							
							$answer_text=str_replace($photo,'',$answer_text);
						}
					}
					
				}
				
				
				$content = array('chat_id' => $chat_id,  'text' =>$answer_text  ); 
			 
					 
				
			 
				$telegram->sendMessage($content);
				
				foreach ($photos as $photo)
				{
					$content = array('chat_id' => $chat_id,  'photo' =>$photo  ); 
					$telegram->sendPhoto($content);
				}
				 
				
				addlog(date('d.m.Y H:i:s ').'!--СДЕЛАННЫЙ ОТВЕТ: '.$answer_text);
			}
			 
			 
		} catch(\Exception $e){
			echo '["Error occured"]';
		}
 
	}
	
	private function links($str)
	{
		$reg = '/(?:^|[^a-z@])((?:https?:\/\/)?(?:www.)?\w{3,20}\.\w{2,13}(?:\w*\.?\w*\/?)*(?:\?.+)?)/i';
			$matches = [];
			$count = preg_match_all($reg, $str, $matches);
			if ($count > 0) {
				return $matches[1];
			}
		return [];
	}
	
	private function send_admin($text)
	{
		$telegram = new Telegram($this->config->item('telegram_bot')); 
		$content = array('chat_id' => vars('chat_id'),  'text' =>  $text);
		$telegram->sendMessage($content);
	}
	
	private function notify_wrong_question($text)
	{
		if (trim($text)=='ping') return false;
		return;
			
		
		$telegram = new Telegram($this->config->item('telegram_bot')); 
		$content = array('chat_id' => vars('chat_id'),  'text' => 'Бот получил непонятный вопрос: '.PHP_EOL.$text);
		$telegram->sendMessage($content);
		
		send_mail2(vars('email'),'Алиса получила непонятный вопрос'   , 'Алиса получила непонятный вопрос: '.PHP_EOL.$text );

	}
	
}
