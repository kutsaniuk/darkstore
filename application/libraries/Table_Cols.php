<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Table_Cols extends BaseRowCols
{
	 public function __construct($CI,$id=0,$url='',$key='url') 
	{  
		$this->construct($CI,'table_cols',$id,$url,$key); 
	}  
	
	public function get_edit_list()
	{
		return 'edit_list.php';
	} 
	 
	public function get_edit()
	{
		return 'edit_table_cols.php';
	} 
	
	public function get_all_to_edit($limit)
	{
		$filter=[];
		if (isset($_GET['table_name'])) $filter['table_name']=$_GET['table_name'];
		return $this->get_all($limit,$st=0,'id','desc',$filter);
	}
	
	public function json_to_text($json)
	{
		$select=$this->json_to_select($json);
		return $text=implode(PHP_EOL,$select);
	}
	
	public function text_to_json($text)
	{
		return json_encode(explode(PHP_EOL,$text)); 
	}
	
	public function update($array)
	{  
	
		//обычное сохранение
		foreach ($array as $k=>$v) {
			
			if (is_array($v)) {
				if (isset($v['date']) && isset($v['time'])) $this->$k=$this->get_timestamp($v['date'],$v['time']);
			}
			elseif ($k=='select') $this->$k=$this->text_to_json($v);
			else  $this->$k=$v;  
		} 
		$this->save();
	}
	 
	  
}