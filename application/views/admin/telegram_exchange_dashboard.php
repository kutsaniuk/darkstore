<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php'); 
?> 
<div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?=number_format($profit1,2)?></h3>

              <p>Доход за сутки</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
           
          </div>
        </div>
		
		 <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?=number_format($profit3,2)?></h3>

              <p>Доход за неделю</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
           
          </div>
        </div>
		
		 <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?=number_format($profit4,2)?></h3>

              <p>Доход за месяц</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
           
          </div>
        </div>
		<?/*
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3>53<sup style="font-size: 20px">%</sup></h3>

              <p>Bounce Rate</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3>44</h3>

              <p>User Registrations</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3>65</h3>

              <p>Unique Visitors</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
		*/?>
      </div>
      <!-- /.row -->
      <!-- Main row -->
	  <div class="row">
			 <div class="box box-primary">
            <div class="box-header">
              <i class="ion ion-clipboard"></i>

              <h3 class="box-title">Курсы</h3>

             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <ul class="todo-list">
			  <?foreach ($this->db->query("SELECT r.rate, v1.name name1, v2.name name2 FROM valut v1, valut v2 , valut_rates r WHERE r.valut1=v1.id AND r.valut2=v2.id ")->result_array() as $row):?>
                <li>
                  <!-- drag handle -->
                      <span class="handle">
                        <i class="fa fa-ellipsis-v"></i>
                        <i class="fa fa-ellipsis-v"></i>
                      </span>
                  <!-- checkbox -->
                  <input type="checkbox" value="">
                  <!-- todo text -->
                  <span class="text"><?=$row['name1']?> -> <?=$row['name2']?></span>
                  <!-- Emphasis label -->
                  <span class="text"><i class="fa fa-clock-o"></i> <?=$row['rate']?></span>
                  <!-- General tools such as edit or delete-->
                  
                </li>
               <?endforeach;?>
              </ul>
            </div>
            <!-- /.box-body -->
 
          </div>
          <!-- /.box -->
	   </div>
       
	<?include('footer.php');?>
<script src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
    $('textarea').each(function(e){
        CKEDITOR.replace( this.id);
    });
});
</script>
</body>
</html>