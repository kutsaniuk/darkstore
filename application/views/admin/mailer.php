<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Рассылка</h3>
			   
			</div>
			    
			
			<form action='?' id='form1' method="post" >
				<div class="box-body">
						<div class="form-group">
						<label >Уровень юзера</label>
						<select class="form-control"  name="user_type_id">
							<?foreach ((new User_Type($this))->get_all() as $row):?>
							<option value="<?=$row['id']?>" ><?=$row['name']?></option>
							<?endforeach;?>
						</select>
						</div>
						<div class="form-group">
						<label >Тема</label>
						<input  class="form-control" type="text" name="theme">
						</div>
						<div class="form-group">
						<label >Текст</label>
						<textarea  class="form-control" name="text"></textarea>
						</div>
				 
				<button  class="btn btn-block btn-primary" type="submit" >Разослать</button>
				</div>
				 
			</form>
			 
			  
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
<!-- DataTables -->
<script src="<?=$path?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?=$path?>plugins/datatables/dataTables.bootstrap.min.js"></script>
</body>
</html>