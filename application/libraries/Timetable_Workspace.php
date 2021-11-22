<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Timetable_Workspace extends BaseRow
{
	 
	public function get_table_cols($type='base')
	{
		 
		return array('date'=>'Дата','day'=>'День/Ночь','zone'=>'Место' , 'count'=>'Количество мест','name'=>'Имя','suname'=>'Фамилия','tel'=>'Телефон' ,'tarif'=>'Тариф'    ); 
	} 
	
	
	
	public function generate_form_rows($class='',$rows='',$placeholder='',$rows_select='' )
	{
		//простые поля  
		if (!is_array($rows)) $rows=array('date'=>'text' ,'zone'=>'text' , 'days'=>'text' , 'count'=>'text','name'=>'text','suname'=>'text','tel'=>'text'    ); 
		if (!is_array($placeholder)) $placeholder=array('date'=>'Дата','tarif'=>'Тариф', 'days'=>'Количество дней','day'=>'День/Ночь','zone'=>'Место','place'=>'Место', 'count'=>'Количество мест','name'=>'Имя','suname'=>'Фамилия','tel'=>'Телефон'    ); 
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		$select=[];
		$answer = row('alisa_quests',15);
		$answer_buttons =  explode(',',$answer['buttons']);
		foreach ($answer_buttons as $v) $select[mb_strtolower($v)]= ($v);
		 
		if (!is_array($rows_select)) $rows_select=array( 'day'=>[0=>'Ночь',1=>'День'] , 'tarif'=>$select);
		foreach ($rows_select as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		 
		 
		
		return $form;
	} 
	
	 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'timetable_workspace',$id); 
		
		 
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
		
		if ($key=='date') return '<span style="display: none;">'.strtotime($row[$key]).'</span> '.$row[$key];
		
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
		
		if ($array['days']>1)
		{
			$days = $array['days'];			
			unset($array['days']);
			
			for ($i=2;$i<=$days;$i++)
			{
				$array2=$array;
				$array2['date']=date('d.m.Y',strtotime($array2['date'])+24*3600*($i-1));
				(new Timetable_Workspace($this->CI))->update($array2);
			}
		}
		 
		//обычное сохранение
		foreach ($array as $k=>$v) {
			  $this->$k=$v;  
		}
		$this->save();
	}
	 
}