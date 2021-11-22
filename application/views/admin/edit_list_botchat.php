<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?=$model_name?></h3>
			  
			  <?if ($model_name=='Strategy'):?>
			  <?include('bid_form.php');?>
			  <?else:?>
			  <a href="/admin55/edit/<?=$model_name?>/0/add"><button type="button" class="btn btn-block btn-primary">Создать</button></a>
				<?endif;?>

		   </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table   class="table table-bordered table-striped" >
                <thead>
                <tr>
					<th>Глубина веток</th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<th  >Редактировать</th>
					<th  >Удалить</th>
                </tr>
                </thead>
                <tbody>
				<?$model->show_list($root+1,0);?>
				</tbody>
                <tfoot>
                <tr>
					<th>Глубина веток</th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<th  >Редактировать</th>
					<th  >Удалить</th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
 
        </div>
        <!-- /.col -->
      </div> 

<?include('footer.php');?>
	  <script>
  $(function () {
    $("#table_edit").DataTable();
    
  });
</script>
<!-- DataTables -->
<script src="<?=$path?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?=$path?>plugins/datatables/dataTables.bootstrap.min.js"></script>
</body>
</html>