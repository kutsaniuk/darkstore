<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class User_Type    extends BaseRow
{
	public function get_table_cols()
	{
		return array('name'=>'Название' ); 
	} 
	
	public function get_table_cols_template()
	{
		return array(); 
	} 
	
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		if (isset($template[$key])) return str_replace('[val]',$row[$key],$template[$key]);
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
		$rows=array('name'=>'text','limit_year'=>'text','limit_week'=>'text','limit_day'=>'text');
		$placeholder=array('name'=>'Название','admin'=>'Админ?','limit_year'=>'Лимит в год','limit_week'=>'Лимит в неделю','limit_day'=>'Лимит в день','allow_register'=>'Разрешено выбрать при регистрации');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками
		$rows=array('allow_register'=>'select','admin'=>'select');
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array('0'=>'Да','1'=>'Нет'),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'user_type',$id); 
	} 
	
	
}