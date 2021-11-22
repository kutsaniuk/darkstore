<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

require_once './application/libraries/BaseRow.php';

class Admin_Law extends BaseRow
{
	public function get_table_cols()
	{
		return array( 'id'=>'Пользователь' ); 
	} 
	
	public function get_all($limit=50,$st=0,$order='id',$order_type='desc',$where=array()) {	
		foreach ($where as $k=>$v) $this->CI->db->where($this->CI->db->escape_str($k),$this->CI->db->escape_str(($v)));
		$this->CI->db->limit($limit,$st); 
		$this->CI->db->where('user_type_id',1);
		$this->CI->db->order_by($order,$order_type);
        $result=$this->CI->db->get('users')->result_array(); 
		return $result;
    }
	
	public function generate_form_rows($class='')
	{
		//простые поля  
		$rows=array();
		$placeholder=array( 'user_id'=>'Пользователь');
		$form=array();
		foreach ($rows as $k=>$v) {
			$form[$k]['form']=$this->generate_form($k,$v,$class,array(),$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		
		
		
		//со справочниками
		$admin_users=array();
		$res=$this->CI->db->query("SELECT u.* FROM users  u , user_type t WHERE t.id=u.user_type_id AND t.admin=1 ")->result_array();
		foreach ($res as $row) $admin_users[$row['id']]=$row['name'];
		 
		$rows=array( 'user_id'=>$admin_users );
		foreach ($rows as $k=>$v) { 
			$form[$k]['form']=$this->generate_form($k,'select',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		
		$rows=array('admin_pages_id'=>'admin_pages' ,'admin_col'=>(new Transactions($this->CI))->get_table_cols() );
		foreach ($rows as $k=>$v) { 
			$form[$k]['form']=$this->generate_form($k,'checkbox',$class,$v,$placeholder[$k]);
			$form[$k]['title']=$placeholder[$k];
		}
		
		return $form;
	} 
	
	public function __construct($CI,$id=0) 
	{ 
		$this->construct($CI,'admin_law',$id); 
	} 
	
	public function get_table_cols_template()
	{
		return array('admin_pages_id'=>'select_admin_pages','id'=>'select_users'); 
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
		//обычное сохранение
		
		 $this->properties['admin_pages_id']=[];
		 
		$this->CI->db->where('user_id',$array['user_id'])->delete('admin_law'); 
		foreach ($array['admin_pages_id'] as $v)
		{
			 $this->properties['admin_pages_id'][$v]=$v;
			 $this->CI->db->insert('admin_law',array('user_id'=>$array['user_id'],'admin_pages_id'=>$v));
		}
		
		$this->CI->db->where('user_id',$array['user_id'])->delete('admin_col'); 
		foreach ($array['admin_col'] as $v)
		{
			 $this->properties['admin_col'][$v]=$v;
			 $this->CI->db->insert('admin_col',array('user_id'=>$array['user_id'],'col'=>$v));
		}
			
			
		$this->id=$array['user_id'];
		
	}
	 
	
	
	public function construct($CI,$table='admin_law', $id=0 ) 
	{ 
		
		$this->CI=$CI;
		if (isset($CI->CI)) $this->CI=$CI->CI;
		$id=(int)$id;
		$this->table='admin_law';  
		
		$table_cols = $this->CI->db->query("SHOW COLUMNS FROM `admin_law` ")->result_array();
		foreach ($table_cols as $v) $this->table_cols[]=$v['Field'];
		
		$row['admin_pages_id']=array();
		 
		if ($id!=0  )
		{ 
			$row['user_id']=$id;
			$row['id']=$id;
			 
				 
				foreach ($this->CI->db->get_where('admin_law',array('user_id'=>$id))->result_array() as $v)
					$row['admin_pages_id'][$v['admin_pages_id']]=$v['admin_pages_id'];
					
				foreach ($this->CI->db->get_where('admin_col',array('user_id'=>$id))->result_array() as $v)
					$row['admin_col'][$v['col']]=$v['col'];
			  
			 
			if (is_array($row)) foreach ($row as $key => $var) $this->properties[$key] = $var; 
			if (isset($this->properties['id'])) $this->id=$this->properties['id'];
			 
			
		}
	} 
}