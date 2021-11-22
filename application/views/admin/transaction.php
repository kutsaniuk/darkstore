<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header2.php');
?> 
<section class="card">
    <div class="card-header">
        <span class="cat__core__title">
            <strong>Подтверждение <?=$model_name?></strong>
        </span>
    </div>
    <div class="card-block">
        <div class="row">
        <div class="col-sm-12">
			 
              <table class="table table-hover nowrap" id="example2" width="100%" >
                <thead>
              
                <tbody>
				<?
				$Us = new Transactions($this,(int)$_GET['id']);
				foreach ($Us->get_table_cols('',$user->id)  as $key => $val):
				$row[$key]=$Us->$key;
				?>
					<tr>
						<td><?=$val?></td>
						<td><?=$Us->get_table_row($key,$row,$Us)?></td>
					</tr>
				<?endforeach;?>
					 
				 
				</tbody>
                
              </table>
			  
              </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
 
        </div>
        <!-- /.col -->
      </section> 

<?include('footer2.php');?>
<script>
    $(function(){

        $('#example1').DataTable({
            responsive: true
        });

        $('#example2').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
            "order": [[ 0, "desc" ]]
        });

        $('#example3').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
        });
    });
</script>
<!-- DataTables -->
</body>
</html>