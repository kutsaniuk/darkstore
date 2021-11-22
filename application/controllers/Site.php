<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Controller {
	
	function test()
	{
		send_mail2(vars('email'),'Новая запись'   , 'Фамилия: '.$row_an1['text'].PHP_EOL.'Имя: '.$row_an2['text'].PHP_EOL.'Телефон: '.$row_an3['text'].PHP_EOL  );
					
	}

	function get_base_data()
	{
		$user = check(); 
		if ($user->id>0) date_default_timezone_set($user->timezone);
		$this->lang->load('site', $user->get_language()); 
		
		if (strlen($_SERVER['HTTP_REFERER'])>0 && strpos($_SERVER['HTTP_REFERER'],'btcbit')===false)
		{
			$_SESSION['HTTP_REFERER']=$_SERVER['HTTP_REFERER'];
		}
		if ($user->id>0)
		{
			$user->save_log();
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
	
	function pay()
	{
		$test = 'JzmPbQ1pVYtvLC5C';
		$secret_key=$this->config->item('interkassa_test');
		
		$err[0] = 'Ошибка - Проверка контрольной подписи данных о платеже провалена!';
		$err[1] = 'Ошибка - Неверная сумма платежа!';
		$err[2] = 'Ошибка - Shop ID!';
		$err[3] = 'FAIL';
		
		$post_shop_id			= trim(stripslashes($_POST['ik_co_id']));          //Номер сайта продавца (eshopId);
		$ik_payment_amount		= trim(stripslashes($_POST['ik_am']));   //Сумма платежа (recipientAmount);
		$ik_payment_id			= trim(stripslashes($_POST['ik_pm_no']));       //Идентификатор платежа
		$ik_paysystem_alias		= trim(stripslashes($_POST['ik_paysystem_alias']));  //Способ оплаты
		$ik_baggage_fields		= trim(stripslashes($_POST['ik_baggage_fields']));   //пользовательское поле
		$ik_payment_state		= trim(stripslashes($_POST['ik_inv_st']));    //Статус платежа (paymentStatus);
		$ik_trans_id			= trim(stripslashes($_POST['ik_trans_id']));         //внутренний номер платежа
		$ik_currency_exch		= trim(stripslashes($_POST['ik_currency_exch']));    //Валюта платежа (recipientCurrency);
		$ik_fees_payer			= trim(stripslashes($_POST['ik_fees_payer']));       //плательщик комиссии
		$ik_sign_hash			= trim(stripslashes($_POST['ik_sign']));        //Контрольная подпись
		$dataSet=$_POST;
		unset($dataSet['ik_sign']); //удаляем из данных строку подписи
		ksort($dataSet, SORT_STRING); // сортируем по ключам в алфавитном порядке элементы массива
		array_push($dataSet, $secret_key); // добавляем в конец массива "секретный ключ"
		$date_now = $signString = implode(':', $dataSet); // конкатенируем значения через символ ":"
		$sing_hash = base64_encode(hash('sha256', $signString, true)); //base64_encode(md5($signString, true)); 
		
		if($ik_sign_hash === $sing_hash)
		{ 
				if($post_shop_id  == $this->config->item('interkassa_id'))
				{
					if($ik_payment_state == 'success')
					{ 
						addlog(   'success!!!!:'.$date_now.':'.$log_data);
							$order = row('pay',$ik_payment_id); 
							if ($order['status']==0 && $order['price']<=$ik_payment_amount)
							{
								
								 addlog(  'Заказ номер: '.$order['id'] .PHP_EOL. $order['do'].' '.$order['social'].': '.$order['count'].'шт.'.PHP_EOL.
								'Цена: '.$order['price']);
								
								$this->db->where('id',$order['id'] )->update('pay',['status'=>1]);
								
								$this->send_admin('Заказ номер: '.$order['id'] .PHP_EOL. $order['do'].' '.$order['social'].': '.$order['count'].'шт.'.PHP_EOL.
								'Цена: '.$order['price']);
								 
								
							}
							
							addlog(   'OK!!!!:'.$date_now.':'.$log_data);
							
							Header('Status: 200 OK');
						  exit("ok");

						 
					}
					else
					{
						addlog(  $err[3].':'.$date_now.':'.$log_data);
					}
				}
				else
				{
					addlog(  $err[2].':'.$date_now.':'.$log_data);
				}
			 
		} else addlog(  $err[1].':'.$date_now.',,'.$secret_key.'='.$ik_sign_hash .' === '. $sing_hash);
		
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
		//$text='Расскажи о инструментах работы';
		//$chat_id=$user_id=44117488;
		 
		$textToCheck=strtolower(trim($text) );
		   
		
		
			
		$this->db->insert('chat_history',['user_id'=>$user_id,'text'=>$textToCheck,'time'=>time(),'bot'=>$type]);	 
		
		
		
		
		$bot_users = row('bot_users',$user_id); 
		if (!$bot_users['id'])
		{
			$answer_text='
			Привет!
Это чат бот компании DANGER, тут ты можешь вводить коды из банок и получать за это гарантированные призы!
-NEW_MES-В каждой банке Malaysian X есть свой уникальный код, а главная фишка в том, что за каждый код ты получишь гарантированный приз!
Никаких накопительных систем, ты нам код - мы тебе приз, сразу же
-NEW_MES-Под несколькими сотнями кодов мы прячем эксклюзивные наборы мерча и даже дорогую технику, например, iPhone 12 или AirPods Pro!
-NEW_MES-А под какими-то кодами скрывается виртуальный подарок в виде уникального контента специально для тебя и его никогда не увидит широкая публика
-NEW_MES-Каждый месяц мы обновляем призы под кодами, чтобы первым узнать, что мы разыгрываем в текущем месяце, пожалуйста, не блокируй уведомления от этого бота.
Обещаем, спамить не будем!
-NEW_MES-А чтобы начать пользоваться нашей щедростью нужно зарегистрироваться, это очень просто и не займет больше пары минут

Учти, что данные, которые ты будешь вводить далее мы будем вписывать в качестве получателя для транспортной компании, так что указывай реальные данные и проверяй на ошибки дважды, иначе ТК может просто не отдать твой приз.
-NEW_MES-Также мы обязаны тебя уведомить, что нам требуется твое согласие на обработку персональных данных, полное соглашение доступно по ссылке:

dngr.ru/privacy-policy/ ';
			$this->db->query("INSERT IGNORE INTO bot_users SET id='$user_id', bot='$type', nick='$nick'  ");
			$answer_buttons=['Я согласен'];
		}
		elseif (!$bot_users['accept'] || $text=='Я согласен')
		{
			$answer_text='Введите свое ФИО и отправь сообщение';
			$this->db->where('id',$user_id)->update('bot_users',['accept'=>1]);
		}
		elseif (!$bot_users['name'])
		{
			$this->db->where('id',$user_id)->update('bot_users',['name'=>$text]);
			$answer_text='Твой номер телефона';
		} 
		elseif (!$bot_users['tel'])
		{
			$this->db->where('id',$user_id)->update('bot_users',['tel'=>$text]);
			$answer_text='Твоя электронная почта';
		}  
		elseif (!$bot_users['email'] )
		{
			$this->db->where('id',$user_id)->update('bot_users',['email'=>$text]);
			$answer_text='Эти данные верны?'.PHP_EOL.$bot_users['name'].PHP_EOL.$bot_users['tel'].PHP_EOL.$text.PHP_EOL.PHP_EOL
				.'Изменить свои данные потом ты не сможешь. Внимательно перепроверь их ещё раз';
			$answer_buttons=['Есть ошибка, хочу исправить','Всё правильно'];
		}  
		elseif (!$bot_users['finigsh_register'])
		{
			if ($text=='Всё правильно')
			{
				$this->db->where('id',$user_id)->update('bot_users',['finigsh_register'=>1]);
				$answer_text='Поздравляем с успешной регистрацией и приветствуем тебя в рядах Малазийцев!
				-NEW_MES-Чтобы начать получать призы, нужно нажать на кнопку "Ввести код" и ввести код, который ты нашел внутри пачки
				';
				$answer_buttons=['Ввести код'];
			}
			else {
				$this->db->where('id',$user_id)->update('bot_users',['email'=>'','tel'=>'','name'=>'']);
				$answer_text='Введите свое ФИО и отправь сообщение';
			}
			
		}  
		
		
		
		try 
		{			
			 
				
		
				$user_quest = row('user_quest',$user_id); 
				
				if (strlen($answer_text)==0)
				{
					$answer_buttons=[];
					$answer_before=row('alisa_quests',$user_quest['last_quest']);
					if ($textToCheck=='Назад'  )
					{ 
						$answer_before=row('alisa_quests',$answer_before['quest_before']); 
						$user_quest['last_quest']=$answer_before['quest_before'];
					}
						
					if ($textToCheck=='Главная' )
						$answer_before=row('alisa_quests',0);
					 
						
					//запрограммированные ответы 
					if ( $answer_before['id']>0 )
					{
						if ($textToCheck!='Назад' && $textToCheck!='Главная')
							$this->db->query("
								INSERT INTO last_answers SET user_id='{$user_id}', quest_id='{$user_quest['last_quest']}', text='$textToCheck'
								ON DUPLICATE KEY UPDATE text='{$textToCheck}' ");
						 
						$answer = $this->db->like('quest',$textToCheck)->where('quest_before',$user_quest['last_quest'])->get('alisa_quests')->row_array();
						if (!isset($answer['id']) && strlen($textToCheck)>2) $answer = $this->db->like('quest',$textToCheck)->get('alisa_quests')->row_array();
						if (!isset($answer['id'])) $answer = $this->db->where('quest_before',$user_quest['last_quest'])->get('alisa_quests')->row_array();
						
						if ($user_quest['last_quest']==14) //ввели код 
						{
							$code = $this->db->where('code',$textToCheck)->order_by('time_activate','asc')-> get('code')->row_array();
							
							
							if ($bot_users['try_time']<time()-24*3600) $bot_users['try']=0;
							if ($bot_users['try']<10) $this->db->where('id',$user_id)->update('bot_users',['try'=>$bot_users['try']+1,'try_time'=>time()]);
							
							if ($bot_users['try']>=9) 
							{
								$answer=row('alisa_quests',23);
							}
							elseif ($code['id']==0  ) $answer=row('alisa_quests',15);
							elseif (  $code['time_activate']>0) $answer=row('alisa_quests',22);
							else {
								//код верный 
								if ($code['prize']) $prize=row('prize',$code['prize']);
								
								//определяем какой приз 
								if (!isset($prize['id']) || $prize['count']<=0)
								{
									$prize = (new Prize($this))->random_prize();
								}
								$this->db->where('id',$prize['id'])->update('prize',['activated'=>$prize['activated']+1 , 'count'=>$prize['count']-1 ]); 
								$this->db->where('id',$code['id'])->update('code',['time_activate'=>time() ]); 
								
								$prize_name = $prize['name'];
								// физический ли приз?
								if ($prize['virtual']==0)
								{
									$answer=row('alisa_quests',19); 
								}
								else {
									$answer=row('alisa_quests',16);
									 
									
									$answer['answer']=str_replace('[prize_link]',$prize['url'],$answer['answer']);
									
									$this->send_admin($prize['name'].PHP_EOL. 'ФИО: '.$bot_users['name'].PHP_EOL.'Телефон: '.$bot_users['tel'].PHP_EOL.'E-mail: '.$bot_users['email']);
									send_mail2(vars('email'),'Новый подарок'   , $prize_name.PHP_EOL.'ФИО: '.$bot_users['name'].PHP_EOL.'Телефон: '.$bot_users['tel'].PHP_EOL.'E-mail: '.$bot_users['email'].PHP_EOL.'Адрес: '.$bot_users['address'] );

								}
								
								$answer['answer']=str_replace('[prize_name]',$prize_name,$answer['answer']);
								
								$this->db->where('id',$user_id)->update('bot_users',['text'=>$bot_users['text'].'<br>'.$prize_name]);
								
							}
							 
							
						}
						elseif ($user_quest['last_quest']==19) // адрес введен 
						{
							$this->db->where('id',$user_id)->update('bot_users',['address'=>$textToCheck]);
							
							$answer['answer']=str_replace('[sdack_code]',$textToCheck,$answer['answer']);
							 
							
						}
						elseif ($answer['id']==20)
						{
							 
							$prizes = explode('<br>',$bot_users['text']);
							$prize_name = $prizes[count($prizes)-1];
							$this->send_admin( $prize_name.PHP_EOL.'ФИО: '.$bot_users['name'].PHP_EOL.'Телефон: '.$bot_users['tel'].PHP_EOL.'E-mail: '.$bot_users['email'].PHP_EOL.'Адрес: '.$bot_users['address']);
							send_mail2(vars('email'),'Новый подарок'   , $prize_name.PHP_EOL.'ФИО: '.$bot_users['name'].PHP_EOL.'Телефон: '.$bot_users['tel'].PHP_EOL.'E-mail: '.$bot_users['email'].PHP_EOL.'Адрес: '.$bot_users['address'] );

						}						
					}
					 
					
					if (!$answer['answer']  ) $answer=row('alisa_quests',1);
					
					//addlog($answer['buttons']); 
					
					 
					$this->db->query("
					INSERT INTO user_quest SET id='{$user_id}', last_quest='{$answer['id']}'
					ON DUPLICATE KEY UPDATE last_quest='{$answer['id']}' ");
					
					//$this->db->query("
					//INSERT INTO user_quest_history SET user_id='{$user_id}', quest_id='{$answer['id']}', time='".time()."' ");
					
					
					
					$answer_text = strip_tags($answer['answer']); 
					if (strlen($answer['buttons'])>0) $answer_buttons =  explode(',',$answer['buttons']);
				} 
		  
			
			
			
			$this->db->insert('chat_history',['user_id'=>$user_id,'text'=>$answer_text,'time'=>time(),'bot'=>$type,'sender_bot'=>1]);	
			
			addlog(date('d.m.Y H:i:s ').'!--ПРЕДВАРИТЕЛЬНЫЙ ОТВЕТ: '.$answer_text);
			
			 
					
			$option = [ ];
			$opt_k=0;
			foreach ($answer_buttons as $btn) 
			if (strlen(trim($btn))>0)
			{
				 $btn = trim($btn );
				 $bt=explode('::',$btn);
				 $btn=$bt[0];
				 if (count($option[$opt_k])>=2) $opt_k++;
				$option[$opt_k][]=$telegram->buildInlineKeyBoardButton( ($btn) ,(isset($bt[1]) ?  $url=$bt[1] : ''  ),(isset($bt[1]) ?   '' : mb_substr($answer['id'].'_'.$btn,0,28) )  );
			} 
			
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
			 
					
				if (count($option)>0 && count($answer_texts)-1==$k) 
				{
					$keyb = $telegram->buildInlineKeyBoard($option );  
					$content['reply_markup']=$keyb;
				}
				
			 
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
