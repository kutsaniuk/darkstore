<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Post extends BaseRow
{
	public $parent_value=array();
	 
	public function get_table_cols()
	{
		return array('name'=>'Название', 'url'=>'Адрес страницы' , 'language'=>'Язык'		); 
	} 
	 
	
	public function get_parent($val)
	{
		if ($this->parent==0) return '';
		if (count($this->parent_value)<1) 
			$this->parent_value=$this->CI->db->get_where('post',array('id'=>$this->parent))->row_array();
		
		return  $this->parent_value[$val];
	}
	
	public function get_table_cols_template()
	{
		return array('url'=>'<a href="/[val]">/[val]</a>','language'=>'select_language'); 
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
	
	public function get_count_posts()
	{
		$res=$this->CI->db->query("SELECT count(id) as count FROM post WHERE parent='{$this->id}' ")->row_array();
		return (int)$res['count'];
	}
	
	public function update($array)
	{ 
		if (strlen($this->url)<1 && strlen($array['url'])<1) {
			if (strlen($array['name'])>0) $array['url']=($array['name']);
			else $array['url']=time().rand(10000,9999999);
		}
		
		//обычное сохранение
		foreach ($array as $k=>$v) 
			if (is_array($v)) {
				if (isset($v['date']) && isset($v['time'])) $this->$k=$this->get_timestamp($v['date'],$v['time']);
			} 
			elseif ($k=='url') {
				if (isset($array['language'])) $lang=$array['language'];
				else $lang=$this->language;
				$v=(mb_strtolower($v,'utf-8'));
				$res = $this->CI->db->get_where('post',array('url'=>$v,'language'=>$lang))->row_array();
				
				if ($res['id']!=$this->id && $res['id']>0) $v=$v.rand(100,9999); 
				$this->$k=$v; 
			} 
			//elseif ($k=='text') $this->$k=str_replace('\n','',str_replace('\r','',$v));
			else	$this->$k=$v; 
		
		 
		
		$this->save();
	}
	 
	public function generate_form_rows($class='')
	{
		//простые поля  
		$rows=array('name'=>'text','title'=>'text','description'=>'text','keywords'=>'text','url'=>'text','time'=>'date_time','text'=>'textarea');
		$placeholder=array('name'=>'Название','language'=>'Язык материала','title'=>'meta-Title','description'=>'meta-description','keywords'=>'meta-keywords','url'=>'Адрес страницы','time'=>'Время публикации','text'=>'Текст','is_category'=>'Является разделом','parent'=>'Родительская категория');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		//со справочниками
		$categories[0]='Без раздела'; 
		foreach ($this->get_all(150,0,'name','asc',array('is_category'=>1,'id !='=>$this->id))  as $row ) $categories[$row['id']]=$row['name'];
		$select_array=array('is_category'=>array('0'=>'Нет',1=>'Да'),'parent'=>$categories,'language'=>'language'		);
		foreach ($select_array as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v ,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'post',$id); 
	} 
	
	
}