<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php'); 
?> 
<div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?=$codes_activated?></h3>

              <p>Активировано кодов</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
          
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?=$codes?> </h3>

              <p>Не активировано</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            
          </div>
        </div>
        <!-- ./col -->
         
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
          <!-- Custom tabs (Charts with tabs)-->
          
          <!-- TO DO List -->
          <div class="box box-primary">
              
            <div class="box-body">
					<? $graph_model->print_graphes(); ?>
            </div>
            <!-- /.box-body -->
            
          </div>

          

        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
       
        <!-- right col -->
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