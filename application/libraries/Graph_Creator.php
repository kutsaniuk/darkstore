<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Graph_Creator  
{
	private $java_script='';
	private $graphes=array();
	private $init=false;
	public $title;
	
	public function InitJS()
	{
		echo '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
		$this->java_script.=' google.charts.load("current", {packages:["corechart"]});';
		$this->init=true;
	}
	
	public function add_graph($data)
	{
		$this->graphes[]=$data;
	}
	
	public function clear()
	{
		$this->graphes=array();
	}
	
	public function print_graphes($title='',$class='')
	{
		if (strlen($title)>0) $this->title=$title;
		foreach ($this->graphes as $k=> $data) echo '<div class="'.$class.'" id="curve_chart'.$k.'"></div>';
		if (!$this->init) $this->InitJS();
		echo '<script type="text/javascript">';
		echo $this->java_script;
		foreach ($this->graphes as $k=> $data) $this->print_graph( $k,$data);
		echo '</script>';
	}
	
	public function print_graph($index,$data)
	{
		$graph_array=$graph_print=$line_names=array();
		
		foreach ($data as $line_name=>$line_data) {
			$line_names[$line_name]='"'.$line_name.'"';
			foreach ($line_data as $k=>$v)  
				if (count(explode('-',$v))==3)  $graph_array[$k][$line_name]="new Date('".$v."')";
				elseif ($v==(int)$v)  $graph_array[$k][$line_name]=$v; else   $graph_array[$k][$line_name]=''.$v.''; //перебираем полоски
		} 
		
	 
		$graph_print[]="[".implode(',',$line_names)."]";
		foreach ($graph_array as $k=>$line_data)
		{
			$graph_print[]="[".implode(',',$line_data)."]";
		}
		
		echo 'google.charts.setOnLoadCallback(drawChart'.$index.');';		
		echo 'function drawChart'.$index.'() {  		
			var data = google.visualization.arrayToDataTable([ '.implode(',',$graph_print).'  ]);
			var options = {
			  title: "'.$this->title.'",
			  curveType: "line",
			  legend: { position: "bottom" }
			};
			var chart = new google.visualization.LineChart(document.getElementById("curve_chart'.$index.'"));
			chart.draw(data, options);
		}';
		
	}
}