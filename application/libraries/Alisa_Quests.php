<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Alisa_Quests extends BaseRow 
{
	public function get_table_cols()
	{
		return array('quest'=>'Варианты вопроса через запятую', 'answer'=>'Ответ', 'quest_before'=>'Предшествующий вопрос', 'buttons'=>'Кнопки через запятую'  ); 
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
		//обычное сохранение 
		foreach ($array as $k=>$v) $this->$k=$v; 
		$this->save();
	}
	 
	public function generate_form_rows($class='')
	{
		//простые поля  
		$rows= array('quest'=>'text', 'answer'=>'text',  'buttons'=>'text'  ); 
		$placeholder= array('quest'=>'Варианты вопроса через запятую', 'answer'=>'Ответ', 'quest_before'=>'Предшествующий вопрос', 'buttons'=>'Кнопки через запятую'  ); 
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками  
		$rows=array('quest_before'=>'alisa_quests');
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
	 
}