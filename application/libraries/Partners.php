<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Partners    extends BaseRow
{
	public function get_table_cols()
	{
		return array('id'=>'Номер','name'=>'Название' ); 
	} 
	
	public function get_table_cols_template()
	{
		return array('partner_id'=>'select_partners','img'=>'<img src="/upload/[val]" width="50" >'); 
	} 
	
 
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		if (isset($template[$key])) {
			if (strpos($template[$key],'select_')!==false) { 
				$table = row(substr($template[$key],7),$row[$key]);
				return $table['name'];
			}
			else return str_replace('[val]',$row[$key],$template[$key]);
		}
	
		
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
		$rows=array('name'=>'text','checkbox_client_id'=>'text', 'checkbox_secret'=>'text','chat_id'=>'text'  );
		$placeholder=array('name'=>'Партнер','checkbox_client_id'=>'Чекбокс client_id ', 
		'checkbox_secret'=>'Чекбокс секоеь','chat_id'=>'ИД чата телеграм'  );
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		} 
		
		/*
		$rows=array('partner_id'=>'partners' );
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		*/
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'partners',$id); 
	} 
	
	
}