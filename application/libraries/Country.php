<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Country    extends BaseRow
{
	public function get_table_cols()
	{
		return array('alpha2'=>'Альфа2 код','name'=>'Название'); 
	} 
	
	public function get_table_cols_template()
	{
		return array('img'=>'<img src="/upload/[val]" width="50" >'); 
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
		$result=array();
		//тут добавим сохранение времени и файла
		if (count($_FILES))
		{ 
			foreach ($_FILES as $k=>$v)
			{
				$file_name = $this->img_upload($k,'./upload/country/');
				if (!is_array($file_name)) $array[$k]='country/'.$file_name;  
				else $result[]=$file_name['error'];
			}
                 
		}
		//обычное сохранение
		foreach ($array as $k=>$v) $this->$k=$v; 
		$this->save();
		return $result;
	}
	 
	public function generate_form_rows($class='')
	{
		//простые поля  
		$rows=array('name'=>'text','alpha2'=>'texr');
		$placeholder=array('name'=>'Название','alpha2'=>'Альфа2 код');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'country',$id); 
	} 
	
	
}