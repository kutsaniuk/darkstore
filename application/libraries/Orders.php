<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Orders    extends BaseRow
{
	public function get_table_cols()
	{
		return array('id'=>'Номер заказа','status_info'=>'Статус','nomeclature'=>'Информация','partner_id'=>'Партнер','number'=>'Номер телефона','address'=>'Адрес' ,'zip'=>'zip'); 
	} 
	
	public function get_table_cols_template()
	{
		return array('partner_id'=>'select_partners','img'=>'<img src="/upload/[val]" width="50" >'); 
	} 
	
	function status_info($status=-1,$thisid=0)
	{
		if ($status==-1 && $this->id)
		{
			$status=$this->status;
		}
		
		$statuses=[
			-1=>'Ожидает подтверждение, <a href="?send_status='.($this->id ? $this->id : $thisid).'">Отправить</a>',
			0=>'Заказ принят, отправляем в службу доставки',
			1=>'Ожидаем приезда курьера',
			2=>'Доставляется курьером',
			3=>'Доставлен',
			4=>'Возникла ошибка, свяжитесь с администратором',
		];
		
		return $statuses[$status];
	}
	
	public function get_table_row($key,$row=array())
	{
		if ($key=='status_info') return $this->status_info($row['status'],$row['id']).'<br>'.$row['status_text'];
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
		$rows=array('number'=>'text','address'=>'text', 'zip'=>'text','zones'=>'text' ,'lat'=>'text','lng'=>'text');
		$placeholder=array('partner_id'=>'Партнер','number'=>'Номер телефона','address'=>'Адрес', 'zip'=>'zip','zones'=>'Зоны (через запятую)' ,'lat'=>'lat','lng'=>'lng');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		} 
		
		//со справочниками
		$rows=array('partner_id'=>'partners' );
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'orders',$id); 
	} 
	
	
}