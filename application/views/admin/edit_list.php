<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title"><?=$model_name?></h3>
			  
			  <?if ($model_name=='Reports'):?>
				 <a href="/cron/cron_day/1"><button type="button" class="btn btn-block btn-primary">Скачать текущий отчет</button></a>
			
			  <?else:?>
			  <a href="/admin55/edit/<?=$model_name?>/0/add"><button type="button" class="btn btn-block btn-primary">Создать</button></a>
				<?endif;?>
		 
		   </div>
		   
            <!-- /.box-header -->
            <div class="box-body">
              <table id="table_edit"  class="table table-bordered table-striped" >
                <thead>
                <tr>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<?if ($model->allow_edit()):?>
					<th  >Редактировать</th>
					<?endif;?>
					<th  >Удалить</th>
                </tr>
                </thead>
                <tbody>
				<?foreach ($model->get_all(5000) as $row):?>
                <tr <?if ($row['orange']==1):?>style="color: orange;"<?endif;?> >
					<?foreach ($model->get_table_cols() as $key => $val):?>
					<td title="<?=$val?>"><?=$model->get_table_row($key,$row)?></td>
					<?endforeach;?> 
					<?if ($model->allow_edit()):?>
					<td><a href="/admin55/edit/<?=$model_name?>/<?=$row['id']?>">Редактировать</a></td>
					<?endif;?>
					<td><a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;" href="/admin55/edit/<?=$model_name?>/<?=$row['id']?>/delete">Удалить</a></td>
                </tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<?if ($model->allow_edit()):?>
					<th  >Редактировать</th>
					<?endif;?>
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