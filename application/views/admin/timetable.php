<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');


if ($_GET['date']) $select_date=$_GET['date'];
else $select_date=date('Y-m-d');
if (!$_GET['office']) $_GET['office']=$user->office;
?> 
      <h4 class="card-title">Расписание</h4>
	  <p class="card-description" id="ajax_status"></p>
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-6">
					<a href="/admin55/edit/<?=$model_name?>/0/add?<?=http_build_query($_GET)?>"><button type="button" class="btn   btn-primary"><?=l('Создать');?></button></a>
				</div> 
				<div class="col-md-6">
					  <input type="date" class="form-control  form-control-sm" OnChange="window.location.href='?date='+this.value+'&office=<?=$_GET['office']?>';" value="<?=$select_date?>" >
					<select  class="form-control  form-control-sm" OnChange="window.location.href='?date=<?=$_GET['date']?>&office='+this.value;" >
					<?foreach ((new Office($this))->get_all() as $office):?>
						<option <?if($_GET['office']==$office['id']) echo 'selected';?> value="<?=$office['id']?>" ><?=$office['name']?></option>
					<?endforeach;?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<button type="button" OnClick="$('#table_settings').toggle();" class="btn  "><?=l('Настроить таблицу');?></button>
		 
				<div id="table_settings" style="display:none">
					<form method="post" action="?<?=http_build_query($_GET)?>">
					<div class="row">
						<div class="col-md-6">
								<div class="form-group">
								<?foreach ($model->get_col_settings() as $row):?>
								  <div class="form-check">
									<label class="form-check-label">
									  <input type="checkbox" <?if ($row['column']>0) echo 'checked';?> name="column[<?=$row['id']?>]" value="<?=$row['id']?>" class="form-check-input"> <?=$row['placeholder']?>
									<i class="input-helper"></i></label>
								  </div>
								<?endforeach;?>  
								</div>
							  </div>
							<div class="col-md-6">
								<div class="form-group">
									<button type="submit" class="btn  btn-primary "><?=l('Сохранить');?></button>
									<a   href="/admin55/edit/table_cols/?table_name=<?=$model_name?>">
										<button type="button" class="btn">
											<?=l('Редактирование колонок');?>
										</button>
									</a>
								</div>
							</div>
					 </div>
					 </form>
				</div>
            </div>
		</div>
		<br>
            
            <!-- /.box-header -->
           <div class="table-responsive">
              <table id="table_edit"  class="table table-striped dataTable" >
                <thead>
                <tr>
					<th><?=l('Время');?></th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					 
                </tr>
                </thead>
                <tbody>
				<?for  ($i=7*60;$i<=22*60;$i=$i+10):
				$time=strtotime($select_date)+$i*60;
				$time2=strtotime($select_date)+$i*60*2;
				?>
                <tr>
					<th><?=date('H:i',$time)?></th>
					<?foreach ($model->get_table_cols() as $key => $val):?>
					<td  >
						<?
						$list = $this->db->query("SELECT * FROM timetable WHERE office='".((int)$_GET['office'])."' AND `column`='{$val}' AND ((time>='{$time}' AND  time_end>'{$time2}') OR (time<='{$time}' AND  time_end>'{$time}')) ")->result_array();
						if (count($list)>0):
						foreach ($list as $task):
						$service=row('service',$task['service']);
						$worker=row('users',$task['worker']);
						$patient=row('patient',$task['patient']);
						?>
						<div <?if($task['status']==1):?>style="background-color:#99ff99;"<?elseif($time<time()):?>style="background-color:#ff9999;"<?endif;?>>
							<a href="/admin55/edit/Patient/<?=$patient['id']?>" target="_blank"><?=$patient['name']?> <?=$patient['suname']?></a>
							<br><?=$worker['name']?> <?=$service['name']?>
							<br>
							<?if($task['status']==1):?>
							<i title="Посетил" class="fa fa-check"></i>
							<?elseif (time()+60*10>$time):?>
							<div class="set<?=$task['id']?>">
								<button OnClick="ajax('timetable_set','id=<?=$task['id']?>','.set<?=$task['id']?>')" class="btn btn-fw btn-success" >Отметить</button>
							</div>
							<?endif;?>
							<?if($task['status']==0):?>
							<a href="/admin55/edit/<?=$model_name?>/<?=$task['id']?>?<?=http_build_query($_GET)?>"><i title="Редактировать" class="fa fa-edit"></i></a>
							<a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;" href="/admin55/edit/<?=$model_name?>/<?=$task['id']?>/delete?<?=http_build_query($_GET)?>"><i title="Удалить" class="fa fa-eraser"></i></a>
							<?endif;?>
						</div>	
						<?
						endforeach;
						else:?>
						<?=l('Свободно');?>
						<?endif;?>
						
					</td>
					<?endforeach;?>  
                </tr>
				<?endfor;?> 
				</tbody>
                <tfoot>
                <tr>
					<th><?=l('Время');?></th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?> 
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
        	  <script>
  $(function () {

        // Datatables
        $('.dataTable').DataTable({
            "lengthMenu": [[50, 100, 200, -1], [50, 100, 200, "All"]],
            responsive: true,
            "autoWidth": false 
			  ,"language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
            }
        });

    })
</script>
<!-- DataTables -->
<script src="<?=$path?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?=$path?>plugins/datatables/dataTables.bootstrap.min.js"></script>
    
<?include('footer.php');?>
