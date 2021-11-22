<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Логи <?=$model_name?></h3>
			   </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="table_edit"  class="table table-bordered table-striped" >
                <thead>
                <tr>
					<?foreach ($model->get_table_cols('log') as $val):?>
					<th><?=$val?></th>
					<?endforeach;?> 
                </tr>
                </thead>
                <tbody>
				<?foreach ($model->get_log($log_type) as $row):?>
                <tr>
					<?foreach ($model->get_table_cols('log') as $key => $val):?>
					<td title="<?=$val?>"><?=$model->get_table_row($key,$row)?></td>
					<?endforeach;?> 
				 </tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<?foreach ($model->get_table_cols('log') as $val):?>
					<th><?=$val?></th>
					<?endforeach;?> 
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