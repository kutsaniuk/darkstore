<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Bot    
{
	public $Bot_User, $chat_id, $lang_id;
	 
	 
	
	public function insert_bot_user($user_id,$network)
	{
		$this->CI->db->query("INSERT IGNORE INTO bot_user SET network='$network', user_id='{$user_id}', bot_id='{$this->id}', time='".time()."' ");
	}
	
	public function get_user_id($network)
	{
		$Payer = new Users($this->CI);
		if ($network=='telegram')
		{
			$telegram = new Telegram( $this->telegram_api);
			$result = $telegram->getData(); 
			if (isset($result['callback_query'])) {
					$result['message']=$result['callback_query']['message'];
					$result['message']['from']=$result['callback_query']['from'];
			}
			$Payer->auth_soc($network,$result['message']['from']['id'],$result['message']['from']['first_name'],$result['message']['from']['username'] );
		
		}
		elseif ($network=='viber')
		{ 
			$bot = new Viber\Bot(['token' => $this->viber_api]);
			$bot
				->onText('|.*|s', function ($event) use ($bot, $botSender ) {
					$V_U =  $event->getSender()->toArray(); 
					$Payer->auth_soc('viber',$V_U['id'],$V_U['name'],$V_U['name'] );
				})->run();		
		}	
		$this->Bot_User=$Payer;
		return $Payer->id;
	}
	
	public function get_var($var,$user_id)
	{
		$row = $this->CI->db->get_where('bot_vars',['bot_id'=>$this->id,'user_id'=>$user_id,'name'=>$var])->row_array();
		return $row['val'];
	}
	
	public function set_var($var,$user_id,$val)
	{
		$this->CI->db->query("INSERT IGNORE INTO  bot_vars SET user_id='{$user_id}', name='{$var}', bot_id='{$this->id}' ");
		$this->CI->db->where('bot_id',$this->id)->where('user_id',$user_id)->where('name',$var)
			->update('bot_vars',['val'=>$val]); 
	}
	
	public function send($network,$answer,$buttons=[],$media='',$chat_id=0)
	{
		
		if ($network=='telegram')
		{
			$telegram = new Telegram( $this->telegram_api);
			if ($chat_id==0)
			{
				$result = $telegram->getData(); 
				if (isset($result['callback_query'])) {
						$result['message']=$result['callback_query']['message'];
						$result['message']['from']=$result['callback_query']['from'];
				}
				$chat_id = $this->chat_id = $result['message']['chat']['id'];
			}
			
			
			if (strlen($media)>1) $telegram->sendPhoto(['chat_id' => $chat_id ,'photo' => $media]);
			else {
				$content = array('chat_id' => $chat_id,  'text' => $answer); 
				$option=[];
				if (count($buttons))
				{
					foreach ($buttons	as $v)			
						$option[]=array($telegram->buildInlineKeyBoardButton($v['text'],$v['href'],$v['code']  ) );
					$keyb = $telegram->buildInlineKeyBoard($option ); 
					$content['reply_markup']=$keyb;
					//$content['text']=json_encode($option); 
				}  
				$telegram->sendMessage($content);
			}
			
			
		}
		elseif ($network=='viber')
		{
			$botSender = new Viber\Api\Sender([
				'name' => $this->name,
				'avatar' => 'https://developers.viber.com/img/favicon.ico',
			]);
			$bot = new Viber\Bot(['token' => $this->viber_api]);
			
			if ($chat_id>0)
			{
				$bot->getClient()->sendMessage(
						(new \Viber\Api\Message\Text())
						->setSender($botSender)
						->setReceiver($chat_id)
						->setText($answer)
					);
			}
			else 
				$bot
				->onText('|.*|s', function ($event) use ($bot, $botSender ) {
					$option=[];
					foreach ($buttons	as $v)			
							if (strlen($v['href'])>1) $option[]=(new \Viber\Api\Keyboard\Button())->setActionType('open-url')
												->setActionBody($v['href'])->setText($v['text']);  
							else $option[]=(new \Viber\Api\Keyboard\Button())->setActionType('reply')
												->setActionBody($v['code'])->setText($v['text']);

					$this->chat_id = $event->getSender()->getId();
					
					if (strlen($media)>0)
					{
						$bot->getClient()->sendMessage(
							(new \Viber\Api\Message\Picture())
							->setSender($botSender)
							->setReceiver($this->chat_id)
							->setText($answer)
							->setMedia($media)
						);
					}							
					elseif (count($option))
						$bot->getClient()->sendMessage(
							(new \Viber\Api\Message\Text())
							->setSender($botSender)
							->setReceiver($this->chat_id)
							->setText($answer)->setKeyboard(
								(new \Viber\Api\Keyboard())
								->setButtons($option)
							) 
						);
					else
						$bot->getClient()->sendMessage(
						(new \Viber\Api\Message\Text())
						->setSender($botSender)
						->setReceiver($this->chat_id)
						->setText($answer)
					);
				})->run();
		}
		
		$this->CI->db->insert('chat',['time'=>time(),'sender_bot'=>1,'media'=>$media,'buttons'=>json_encode($buttons),'text'=>$answer,'bot_id'=>$this->id,'user_id'=>$this->Bot_User->id,'chat_id'=>$this->chat_id]);
		
	}
	
	public function mass_send($mes,$period=0)
	{
		$this->CI->db->insert('chat',['time'=>time(),'sender_bot'=>1,'period'=>$period ,'text'=>$mes ,'bot_id'=>$this->id ]);
		
		foreach ($this->CI->db->query("SELECT * FROM chat WHERE bot_id='{$this->id}' AND user_id>0 GROUP BY user_id ")->result_array()  as $chat)
		{
			 
				$Us = new Users($this,$chat['user_id']);
				$this->Bot_User=$Us;
				$this->chat_id=$chat['chat_id'];
				$this->send($Us->network,$mes, [], '',$chat['chat_id']);
		}
			
	}
	
	public function select_lang_send($network,$screen)
	{
		   
		$text=l('Select you language');
		$buttons=[];
		foreach (explode(',',$this->langs) as $l)
		{
			$lang = row('language',$l);
			$buttons[]=['text'=>$lang['title'],'code'=>'setlang_'.$l];
		}
		
		$this->send($network,$text,$buttons); 
	}
	
	public function check_lang_block($screen)
	{
		
		$res = $this->CI->db->get_where('bot_block',['screen'=>$screen['id'] ,'lang'=>$this->lang_id])->result_array();
		
		if (count($res)<1)
		{
			$res = $this->CI->db->get_where('bot_block',['screen'=>$screen['id']] )->row_array();
			 
			if ($res['id']>0)
			{  
				$this->CI->db->query("
				INSERT IGNORE INTO bot_block (screen,bot,type,pos,p1,p2,p3,p4,lang,osn_id)
				SELECT screen,bot,type,pos,p1,p2,p3,p4,'{$this->lang_id}',id FROM bot_block WHERE lang='{$res['lang']}'
				");
				
			}
		
			
		}
	}
	
	public function form_and_send($network,$screen)
	{
		 
		
		$this->check_lang_block($screen);
		//статичные переменные 
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$screen['id'],'type'=>'var','lang'=>$this->lang_id])->result_array() as $row)
		{ 
			$this->set_var($row['p1'],$this->Bot_User->id,$row['p2']);
		}
		//стартовый запрос к апи 
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$screen['id'],'type'=>'json_api','lang'=>$this->lang_id])->result_array() as $row)
		{  
			if ($row['p2']=='get')
			{
				$request = $this->add_vars($row['p1']).'?'.$this->add_vars($row['p3']);
				//addlog($request);
				file_get_contents($request);
			}
			else {
				$post_url = $this->add_vars($row['p1']);
				//addlog($row['p3']);
				$post_data = parse_str($this->add_vars($row['p3'])); 
				//addlog($this->add_vars($row['p3']));
				//addlog(json_encode($post_data));
				Send_Post($post_url, $post_data );
			}
			
		}
		
		
		
		$blocks=[];
		
		foreach ($this->CI->db->query("SELECT * FROM bot_block WHERE bot='{$this->id}' AND lang='{$this->lang_id}' AND screen='{$screen['id']}' AND `type`   IN ('text','media','button') ORDER BY pos ASC ")->result_array() as $row)
		{
			$blocks[]=$row;
		}
		$blocks[]=['type'=>'end','id'=>999999];
		$text=[];
		$buttons=[];
		//addlog( json_encode($blocks));
		foreach ($blocks as $k=>$v)
		{
			 
			$prev=$blocks[$k-1];
			if ($v['type']=='text') $text[]=$v['p1'];
			if ($v['type']=='button') {
				if ($v['p2']=='link') {
					$href=$this->add_vars($v['p3']);
					if (strlen($href)<1 || strpos($href,'ttp')==false) $href='http://kratospay.com';
					$buttons[]=['text'=>$this->add_vars($v['p1']),'href'=>$href];
				}
				elseif ($v['p2']=='goto_screen') $buttons[]=['text'=>$this->add_vars($v['p1']),'code'=>'gotoscreen_'.$v['p3']];
				elseif ($v['p2']=='item') {
					$buttons[]=['text'=>$this->add_vars($v['p1']),'code'=>'Yes '.$v['p3']];
				} 
				else $buttons[]=['text'=>$this->add_vars($v['p1']),'code'=>'button_'.$v['id']];
			}
			
			if ($prev['id']>0 && (($v['type']=='end' || $v['type']=='media' ) || ($v['type']=='text' && $prev['type']!='text' && $prev['type']!='media'  )))
			{ 
				$answer=implode(PHP_EOL,$text);
				if ($v['type']=='end' && strlen($answer)==0 && count($buttons)>0) $answer='empty';
				if (strlen($answer)>0)
				{
					$this->send($network,$this->add_vars($answer),$buttons);
					$text=[];$buttons=[];
				} 
			}
			if ($v['type']=='media')
			{ 
				$this->send($network,l('media'),[],$v['p1']);
			}
		}	 
		
		//$answer=implode(PHP_EOL,$text);
		//$this->send($network,$this->add_vars($answer) );
		$this->set_var('last_screen',$this->Bot_User->id,$screen['id']);
		
		 
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$screen['id'],'type'=>'gotonow','lang'=>$this->lang_id])->result_array() as $row)
		{  
			$screen = row('bot_screen',$row['p3']); 
			return $this->form_and_send($network,$screen); 
		}
		
	}
	
	function add_vars($text)
	{
		$arr1=$arr2=[];
		foreach ($this->CI->db->get_where('bot_vars',['bot_id'=>$this->id,'user_id'=>$this->Bot_User->id])->result_array() as $r)
		{
			$arr1[]='{'.$r['name'].'}';
			$arr2[]=$r['val'];
		} 
		$arr1[]='{bot_id}';
		$arr2[]=$this->id;
		return str_replace($arr1,$arr2,$text); 
	}
	
	public function check_lang($network)
	{
		$answer = $this->get_answer($network);
		if (strpos($answer,'setlang')!==false)
		{ 
			$this->lang_id =  str_replace('setlang_','',$answer);
			$this->set_var('lang_id',$this->Bot_User->id,$this->lang_id);
		}
		
		$langs = explode(',',$this->langs);
		if (count($langs)<=1) {
			$this->lang_id=(int)$langs[0];
			$this->set_var('lang_id',$this->Bot_User->id,$this->lang_id);
		}
		elseif (count($langs)>1 && $this->lang_id==0)
		{
			$this->select_lang_send($network,$screen); 
			die();
		}
	}
	
	public function send_start_screen($network)
	{
		 
		$this->check_lang($network);
		
		$screen = $this->CI->db->query("SELECT * FROM bot_screen WHERE bot='{$this->id}' ORDER BY pos ASC ")->row_array();
		 
		$this->form_and_send($network,$screen); 
	} 
	
	public function get_answer($network)
	{
		if ($network=='telegram')
		{
			$telegram = new Telegram( $this->telegram_api);
			$result = $telegram->getData(); 
			
			
			if (isset($result['callback_query'])) 
			{
				$result['message']=$result['callback_query']['message'];
				$this->chat_id = $result['message']['chat']['id'];
				return $result['callback_query']['data']; 
			}	 
			else {
				$this->chat_id = $result['message']['chat']['id'];
				return $result['message']['text']; 
			}
		}
		elseif ($network=='viber')
		{
			global $text_viber;
			$bot = new Viber\Bot(['token' => $this->viber_api]);
			$bot
				->onText('|.*|s', function ($event) use ($bot, $botSender ) {
					global $text_viber;
					$this->chat_id  = $event->getSender()->getId();
					$text_viber= $event->getMessage()->getText();
				})->run();	
			return $text_viber;
		}	
	}
	
	public function answer($network,$last_screen_id)
	{
		
		$answer = $this->get_answer($network);
		$this->CI->db->insert('chat',['time'=>time(),'sender_bot'=>0,'media'=>'','buttons'=>'','text'=>$answer,'bot_id'=>$this->id,'user_id'=>$this->Bot_User->id,'chat_id'=>$this->chat_id]);
		
		//устанавливаем введенные переменные
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$last_screen_id,'type'=>'input','lang'=>$this->lang_id])->result_array() as $row)
		{ 
			$this->set_var($row['p1'],$this->Bot_User->id,$answer);
		}
		
		//проферяем условия
		addlog(json_encode(['screen'=>$last_screen_id,'type'=>'ifgoto','lang'=>$this->lang_id]));
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$last_screen_id,'type'=>'ifgoto','lang'=>$this->lang_id])->result_array() as $row)
		{ 
			$goto=0;
			$var = $this->get_var($row['p2'],$this->Bot_User->id);
			//if (strtotime($var)>0) $var_time=strtotime($var);
			$val=$row['p4'];
			
			if ($row['p1']=='<' && $var<$val) $goto=$row['p3'];
			if ($row['p1']=='>' && $var>$val) $goto=$row['p3'];
			if ($row['p1']=='=' && $var==$val) $goto=$row['p3'];
			if ($row['p1']=='>=' && $var>=$val) $goto=$row['p3'];
			if ($row['p1']=='<=' && $var<=$val) $goto=$row['p3'];
			addlog($row['p2'].$var.$row['p1'].$val.' '.$row['p3']);
			//проверка даты
			if (strtotime($var)>0) {
				$day=date("w", strtotime($var));
				if ($day==0) $day=7;
				 
				if ($row['p1']=='NOT_IN' && !in_array($day,explode(',',$val))) $goto=$row['p3'];
			}	
			elseif ($row['p1']=='NOT_IN' && !in_array($var,explode(',',$val))) $goto=$row['p3'];
			
			if ($goto>0)
			{
				$screen = row('bot_screen',$goto); 
				return $this->form_and_send($network,$screen); 
			}
		}
		
		if (strpos($answer,'gotoscreen_')!==false)
		{
			$id=str_replace('gotoscreen_','',$answer);
			$screen = row('bot_screen',$id);
			 
			return $this->form_and_send($network,$screen); 
		}
		elseif (strpos($answer,'button')!==false)
		{
			$button = row('bot_block',str_replace('button_','',$answer));
			if ($button['p2']=='api_request')
			{
				$request = $this->add_vars($button['p3']);
				file_get_contents($request);
			}
			elseif ($button['p2']=='set_var')
			{
				$this->set_var($button['p3'],$this->Bot_User->id,$button['p4']);
			}
		}
		elseif (strpos($answer,'setlang')!==false)
		{
			
			$this->lang_id =  str_replace('setlang_','',$answer);
			$this->set_var('lang_id',$this->Bot_User->id,$this->lang_id);
		}
		
		 
		foreach ($this->CI->db->get_where('bot_block',['screen'=>$last_screen_id,'type'=>'goto','lang'=>$this->lang_id])->result_array() as $row)
		{  
			$screen = row('bot_screen',$row['p3']); 
			return $this->form_and_send($network,$screen); 
		}
		 
		//если ничего не нашли
		return $this->send_start_screen($network); 
	}
	
	public function get_current_screen($user_id)
	{ 
		$this->lang_id=(int)$this->get_var('lang_id',$user_id);
		return $this->get_var('last_screen',$user_id);
	}
	
	public function users_count($date=0)
	{
		$row = $this->CI->db->query("SELECT COUNT(DISTINCT(user_id)) count FROM  bot_user WHERE bot_id='{$this->id}' AND time>'{$date}' ")->row_array();
		return $row['count'];
	}
	 
	
}