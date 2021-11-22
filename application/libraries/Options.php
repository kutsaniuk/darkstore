<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Options    extends BaseRow
{
	public function get_table_cols()
	{
		return array('name'=>'Название','value'=>'Значение' ); 
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
		$rows=array('value'=>'text');
		$placeholder=array('value'=>'Значение' );
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		} 
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'options',$id); 
	} 
	
	
}