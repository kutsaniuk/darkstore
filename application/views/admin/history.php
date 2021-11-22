<?php
defined('BASEPATH') OR exit('No direct script access allowed');


include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">История сообщений</h3>
			   

		   </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="table_edit"  class="table table-bordered table-striped" >
			  <?if ($_GET['id']>0):?>
				<thead>
                <tr> 
					<td>#</td>
					<th>Отправитель</th>
					<th>Время</th> 
					<th>Текст</th>  
                </tr>
                </thead>
                <tbody>
				<?foreach ($this->db->get_where('chat_history',['user_id'=>$_GET['id'],'bot'=>$_GET['bot']])->result_array() as $row):?>
                <tr  >
					<td><?=$row['id']?></td> 
					<td  ><?=($row['sender_bot'] ? 'Бот' : $bot_user['name'] )?></td>
					<td  ><?=date('d.m.Y H:i',$row['time'])?></td>
					<td  ><?=$row['text']?></td>
				</tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<td>#</td>
					<th>Отправитель</th>
					<th>Время</th> 
					<th>Текст</th>  
                </tr>
                </tfoot>
			  <?else:?>
                <thead>
                <tr>
					 
					<th>ИД Пользователя</th>
					<th>Имя</th> 
					<th>Бот</th> 
					<th  >Посмотреть историю</th>
					<th  >Удалить</th>
                </tr>
                </thead>
                <tbody>
				<?foreach ($this->db->get_where('bot_users')->result_array() as $row):?>
                <tr  >
					 
					<td  ><?=$row['id']?></td>
					<td  ><?=$row['name']?></td>
					<td  ><?=$row['bot']?></td>
					
					<td><a href="/admin55/page/history?id=<?=$row['id']?>&bot=<?=$row['bot']?>">Просмотр истории</a></td>
					<td><a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;" href="/admin55/page/history?del=<?=$row['id']?>&bot=<?=$row['bot']?>">Удалить</a></td>
                </tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<th>ИД Пользователя</th>
					<th>Имя</th> 
					<th>Бот</th> 
					<th  >Посмотреть историю</th>
					<th  >Удалить</th>
                </tr>
                </tfoot>
			<?endif;?>
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