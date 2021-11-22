<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Рефоводы</h3>
			   
			</div>
			    
			
			<form action='?' id='form1' method="get">
				<?if ($_GET['time1']==0) $_GET['time1']=date('Y-m-d',time()-30*24*3600);
				if ($_GET['time2']==0) $_GET['time2']=date('Y-m-d');
				
				$time1=strtotime($_GET['time1']);
				$time2=strtotime($_GET['time2']);
				?>
				<input type="date" id="time1" name="time1" value="<?=date('Y-m-d',strtotime($_GET['time1']))?>">
				<input type="date" id="time2" name="time2" value="<?=date('Y-m-d',strtotime($_GET['time2']))?>">
				<button type="submit" >Применить</button>
				
				
				<a href="javascript:" OnClick="$('#time1').val('<?=date('Y-m-d')?>');$('#time2').val('<?=date('Y-m-d')?>');">Сегодня</a> | 
				<a href="javascript:" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600)?>');$('#time2').val('<?=date('Y-m-d',time()-24*3600)?>');">Вчера</a> | 
				<a href="javascript:" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600*7)?>');$('#time2').val('<?=date('Y-m-d',time())?>');">Неделя</a> | 
				<a href="javascript:" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600*14)?>');$('#time2').val('<?=date('Y-m-d',time()-24*3600*7)?>');">Прошлая</a> | 
				<a href="javascript:" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600*30)?>');$('#time2').val('<?=date('Y-m-d',time())?>');">30 дней</a>
			</form>
			 
			 
            <!-- /.box-header -->
            <div class="box-body">
              <table id="table_edit"  class="table table-bordered table-striped" >
                <thead>
                <tr> 
					<th>ID</th>
					<th>Пользователь</th>
					<th>Рефералов</th>
					<th>Заработал USD</th>
					<th>Заработал EUR</th>
                </tr>
                </thead>
                <tbody>
				<?foreach ($this->db->query("SELECT u.*, COUNT(u2.id) count, SUM(IFNULL(e.referal_profit,0)) eur, SUM(IFNULL(e2.referal_profit,0)) usd FROM users u , users u2 LEFT JOIN exchange e ON e.user_id=u2.id AND e.status=2   AND (e.from=2 OR e.to=2) LEFT JOIN exchange e2 ON e2.user_id=u2.id AND e2.status=2   AND (e2.from=1 OR e2.to=1)
				WHERE u2.referal=u.id AND u2.reg_time>'$time1' AND u2.reg_time<'$time2'      GROUP BY u.id ORDER BY u.id DESC  ")->result_array() as $row):?>
                <tr>
					 
					<td title="<?=$row['id']?>"><?=$row['id']?></td>
					<td title="<?=$row['name']?>"><?=$row['name']?></td>
					<td title="<?=$row['count']?>"><?=$row['count']?></td>
					<td title="<?=$row['eur']?>"><?=$row['eur']?></td>
					<td title="<?=$row['usd']?>"><?=$row['usd']?></td>
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