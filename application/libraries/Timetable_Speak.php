<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Timetable_Speak extends BaseRow
{
	 
	public function get_table_cols($type='base')
	{
		 
		return array('date'=>'Дата','time'=>'Время','zone'=>'Место'  ,'name'=>'Имя','suname'=>'Фамилия','tel'=>'Телефон'     ); 
	} 
	
	
	
	public function generate_form_rows($class='',$rows='',$placeholder='',$rows_select='' )
	{
		//простые поля  
		if (!is_array($rows)) $rows=array('date'=>'text' ,'time'=>'text','zone'=>'text' ,'name'=>'text','suname'=>'text','tel'=>'text'    ); 
		if (!is_array($placeholder)) $placeholder=array('date'=>'Дата','time'=>'Время','zone'=>'Место','place'=>'Место', 'count'=>'Количество мест','name'=>'Имя','suname'=>'Фамилия','tel'=>'Телефон'    ); 
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		  
		
		return $form;
	} 
	
	 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'timetable_speak',$id); 
		
		 
	} 
	
	public function get_table_cols_template()
	{
		return array( ); 
	} 
	
	public function get_table_row($key,$row=array())
	{
		if (count($row)<1) $row=$this->properties;
		$template = $this->get_table_cols_template();
		
		if ($key=='day' && $row[$key]==1) return 'День';
		elseif ($key=='day'  ) return 'Ночь';
		
		if (isset($template[$key])) $template[$key]=str_replace('[id]',$row['id'],$template[$key]);
		
		if (isset($template[$key])) {
			if (strpos($template[$key],'select_')!==false) { 
				$table = row(substr($template[$key],7),$row[$key]);
				return $table['name'];
			}
			elseif ($template[$key]=='time') return date('d.m.Y H:i',$row[$key]);
			elseif (isset($row[$key])) return str_replace('[val]',$row[$key],$template[$key]);//возвращаем шаблон со значением
			else return $template[$key];//возвращаем шаблон без значения
		}
		return $row[$key];
	}
	  
	
	public function update($array)
	{ 
		
		if (count($_FILES))
		{ 
			foreach ($_FILES as $k=>$v)
			{
				
				if (strlen($_FILES[$k]['name'] )>1)
				{
					$file_name = $this->img_upload($k,'./upload/post/');
					if (!is_array($file_name)) $array[$k]='post/'.$file_name; 
					else $result[]=$file_name['error'];
					 
					
				}
				
			} 
		}
		 
		 
		if ($array['time'])
		{
			$time0 = explode('-',$array['time']);
			$array['time_start']=(int)$time0[0];
			$array['time_end']=(int)$time0[1];
		}
		 
		//обычное сохранение
		foreach ($array as $k=>$v) {
			  $this->$k=$v;  
		}
		$this->save();
	}
	 
}