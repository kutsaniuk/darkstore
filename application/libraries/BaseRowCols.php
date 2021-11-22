<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class BaseRowCols extends BaseRow
{
	 
	public function get_table_cols()
	{
		$cols=[];
		foreach ($this->get_col_settings(['column'=>1]) as $row) 
			$cols[$row['name']]=$row['placeholder'];
			
		return $cols; 
	}

	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		
		$res=$this->get_col_settings(['name'=>$key]);
		$r=$res[0]; 
		if ($r['type']!='select') return $row[$key];
		else {
			$select=$this->json_to_select($r['select']); 
			if (!$select) $select=$r['select'];
			else return $select[$row[$key]];
			
			$tcol=row($select,$row[$key]);
			return $tcol['name'];
		}
	}	
	 
	function get_edit_list()
	{
		return 'edit_list_patient.php';
	}
	
	public function update($array)
	{  
		//обычное сохранение
		foreach ($array as $k=>$v) {
			if (is_array($v)) {
				if (isset($v['date']) && isset($v['time'])) $this->$k=$this->get_timestamp($v['date'],$v['time']);
			}
			else  $this->$k=$v;  
		} 
		$this->save();
	}
	
	public function generate_form_rows($class='')
	{
		//простые поля   
		$form=array();
		foreach ($this->get_col_settings(['form'=>1]) as $row) { 
			$addhtml='';
			if ($row['type']=='select') {
				$addhtml='   data-live-search="true" ';
				$class.=' selectpicker ';
			}
			$form[$row['name']]['form']=$this->generate_form($row['name'],$row['type'],$class,$this->json_to_select($row['select']),$row['placeholder'],$addhtml);
			$form[$row['name']]['title']=$row['placeholder'];
		}
		 
		return $form;
	} 
	  
}