<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Подтверждение <?=$model_name?></h3> 
            </div>
            <!-- /.box-header -->
            <div class="table-responsive">
            <div class="box-body">
              <table class="table table-hover nowrap" id="example2" width="100%" >
                <thead>
                <tr>
					<?foreach ($model->get_table_cols('confirmation') as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<th  >Подтвердить</th>
					<th  >Отказать</th>
                </tr>
                </thead>
                <tbody>
				<?foreach ($model->confirmation_get() as $row):
				$Us = new $model_name($this,$row['id']);
				
						 
				?>
                <tr <?if ($row['status']==1) echo ' style="color:red;" ';?> id="row<?=$row['id']?>">
					<?foreach ($model->get_table_cols('confirmation') as $key => $val):
					if (!isset($row[$key])) $row[$key]=$Us->$key;
					?>
					<td title="<?=$val?>"><?=$model->get_table_row($key,$row)?></td>
					<?endforeach;?> 
					<td><a href="javascript:" OnClick="if (confirm('Вы уверены?')) ajax('admin_confirm/<?=$model_name?>/<?=$row['id']?>/accept','','#row<?=$row['id']?>');">Подтвердить</a></td>
					<td>
					<input id="dic<?=$row['id']?>" type="text" value="" placeholder="Причина отказа">
					<a OnClick="if (confirm('Вы уверены?')) ajax('admin_confirm/<?=$model_name?>/<?=$row['id']?>/decline','text='+$('#dic<?=$row['id']?>').val(),'#row<?=$row['id']?>');" href="javascript:">Отклонить</a></td>
                </tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<?foreach ($model->get_table_cols('confirmation') as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<th  >Подтвердить</th>
					<th  >Отказать</th>
                </tr>
                </tfoot>
              </table>
            </div>
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
    $("#table_edit").DataTable( {
        "order": [[ 0, "desc" ]]
    });
    
  });
</script>
<?if ($model_name=='Exchange'):?>
<script>
function renew_kurs()
{
	$.ajax({
								   type: "GET",
								   url: '/ajax/renew_kurs' ,
								   dataType: 'json', 
									cache:false,
									contentType: false,
									processData: false,
								  
								   success: function(data)
								   {
									  $.each( data  , function( index, value ) {
										   console.log( value.id+' '+  value.sum_to );
										   
										   $('#sum_to'+value.id).html(value.sum_to);
										   $('#status'+value.id).html(value.status);
										});
									      
								   }
						});
}
setInterval(renew_kurs,60000);
renew_kurs();
</script>	
<?endif;?>
<!-- DataTables -->
<script>
    $(function(){


        $('#example2').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
            "order": [[ 0, "desc" ]]
        });

    });
</script>
<!-- DataT
</body>
</html>