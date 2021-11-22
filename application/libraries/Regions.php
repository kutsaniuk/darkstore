<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Regions    extends BaseRow
{
	public function get_table_cols()
	{
		return array('name'=>'Название','status'=>'1 - Выгружается в отчет' ); 
	} 
	
	public function get_table_cols_template()
	{
		return array('img'=>'<img src="/upload/[val]" width="50" >'); 
	} 
	
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		if ($key=='time_activate') return ($row[$key] ? date('d.m.Y H:i',$row[$key]) : 'Не активирован');
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
		 
		$rows=array('name'=>'text_disabled' );
		$placeholder=array('name'=>'Название','status'=>'Выгружать в отчет?' );
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		 
		//со справочниками
		 
		//$categories[0]='Случайный'; 
		//foreach ($this->CI->db->get('prize')->result_array()  as $row ) $categories[$row['id']]=$row['name'];
		
		$rows=array('status'=>[0=>'Не выгружать',1=>'Выгружать в отчет'] );
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		 
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'regions',$id); 
	} 
	
	
}