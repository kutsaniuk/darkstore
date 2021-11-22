<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Bot_Chat extends BaseRow 
{
	public function get_table_cols()
	{
		return array('id'=>'ID','quest'=>'Варианты вопроса через запятую', 'answer'=>'Ответ', 'quest_before'=>'Предшествующий вопрос', 'buttons'=>'Кнопки через запятую'  ); 
	}   
	
	function show_list($root=0,$parent_id=0)
	{
		foreach ($this->get_all(50,0,'id','asc',['quest_before'=>$parent_id]) as $row):?>
                <tr  class="parent<?=$parent_id?>" <?if ($parent_id>0):?>style="display: none;"<?endif;?> >
					<?/*
					<?for ($i=0;$i<$root;$i++):?>
					<td ></td>
					<?endfor;?> 
					*/?>
					<td><?=$root?> <br><?if ($row['id']>0):?><a OnClick="$('.parent<?=$row['id']?>').toggle();" href="javascript:">+ Развернуть</a><?endif;?></td>
					<?foreach ($this->get_table_cols() as $key => $val):?>
					<td title="<?=$val?>"><?=$this->get_table_row($key,$row)?></td>
					<?endforeach;?> 
					<td><a href="/admin55/edit/bot_chat/<?=$row['id']?>">Редактировать</a></td>
					<td><a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;" href="/admin55/edit/bot_chat/<?=$row['id']?>/delete">Удалить</a></td>
                </tr>
				<?if ($row['id']!=$parent_id) $this->show_list($root+1,$row['id']);?>
		<?endforeach;
	}
	
	function get_edit_list()
	{
		return 'edit_list_botchat.php';
	}
	
	public function get_table_cols_template()
	{
		return array('quest_before'=>'select_alisa_quests'); 
	} 
	
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		if (isset($template[$key])) {
			if (strpos($template[$key],'select_')!==false) { 
				$table = row(substr($template[$key],7),$row[$key]);
				return $table['answer'];
			}
			else return str_replace('[val]',$row[$key],$template[$key]);
		}
	
		
		return $row[$key];
	}
	
	public function update($array)
	{ 
		if (isset($array['quest'])) $array['quest']=mb_strtolower($array['quest']);
		//обычное сохранение 
		foreach ($array as $k=>$v) $this->$k=$v; 
		$this->save();
	}
	 
	public function generate_form_rows($class='')
	{
		//простые поля  
		$rows= array('quest'=>'text',  'answer'=>'text_noedit',  'buttons'=>'text'  ); 
		$placeholder= array('quest'=>'Варианты вопроса через запятую','system'=>'Тип сообщения', 'once_send_time'=>'Отправлять в заданое время один раз, если не надо, оставьте пустым' , 'answer'=>'Ответ', 'quest_before'=>'Предшествующий вопрос', 'buttons'=>'Кнопки через запятую'  ); 
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками  
		 $quests=[];
		 foreach ($this->get_all(999,0,'id','asc') as $r) $quests[$r['id']]=$r['answer'];
		 
		$rows=array('quest_before'=>$quests,'system'=>[0=>'Обычный',1=>'Требует ответа']);
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		return $form;
	} 
	
	public function __construct($CI,$id=0,$url='',$key='url') 
	{  
		$this->construct($CI,'alisa_quests',$id,$url,$key); 
		 
	}  
	 
	
	//удаление элемента
	public function delete()
	{
		$this->CI->db->query("UPDATE alisa_quests SET quest_before=0 WHERE quest_before='{$this->id}'");
		
		parent::delete();
	}
}