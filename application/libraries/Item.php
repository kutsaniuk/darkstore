<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Item    extends BaseRow
{
	public function get_table_cols()
	{
		return array('item_id'=>'Товар', 'zone_id'=>'Зона','rack'=>'Стеллаж','shelf'=>'Полка' ,'count'=>'Количество' ); 
	} 
	
	public function get_table_cols_template()
	{
		return array('item_id'=>'select_item_type','zone_id'=>'select_zone','img'=>'<img src="/upload/[val]" width="50" >'); 
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
		$rows=array( 'rack'=>'text','shelf'=>'text' ,'count'=>'text');
		$placeholder=array('item_id'=>'Товар', 'zone_id'=>'Зона','rack'=>'Стеллаж','shelf'=>'Полка' ,'count'=>'Количество' );
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		} 
		
		//со справочниками
		$rows=array('item_id'=>'item_type', 'zone_id'=>'zone' );
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'item',$id); 
	} 
	
	
}