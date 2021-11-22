<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header2.php');
?> 
      <section class="card">
	   <div class="card-header">
        <span class="cat__core__title">
            <strong>Детали <?=$model_name?></strong>
</span>
    </div>
            
		 <div class="card-block">
		 <div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
              <table class="table table-hover nowrap" id="example2" width="100%">
                <thead>
                <tr>
					<?foreach ($model->get_table_cols('log') as $val):?>
					<th><?=$val?></th>
					<?endforeach;?> 
                </tr>
                </thead>
                <tbody>
				<?foreach ($model->get_log($user_id) as $row):?>
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
            </div>
        </div>
    </div>
</section> 
			  
<?include('footer2.php');?>
<script>
    $(function(){

        $('#example2').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
            
        });

    });
</script> 
</body>
</html>