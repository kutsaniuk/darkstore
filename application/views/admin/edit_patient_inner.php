 <h4 class="card-title"><?=$model_name?></h4>
	  <p class="card-description" id="ajax_status"></p>
		<div class="row">
			<div class="col-md-6">
                   <a href="/admin55/edit/<?=$model_name?>/0/add?<?=http_build_query($_GET)?>"><button type="button" class="btn   btn-primary"><?=l('Создать');?></button></a>
				<a target="_blank" href="/admin55/export_excel/<?=$model_name?>"><button type="button" class="btn btn-info btn-fw"><?=l('Экспорт в эксель');?></button></a>
				
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
					<th  ><?=l('Редактировать');?></th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<?if (strtolower($model_name)=='patient'):?>
					<th  ><?=l('Создать задачу');?></th>
					<?elseif (strtolower($model_name)=='tasks'):?>
					<th  ><?=l('Закрыть задачу');?></th>
					<?elseif (strtolower($model_name)=='sms'):?>
					<th  ><?=l('Разослать');?></th>
					<?endif;?>
					
					<th  ><?=l('Удалить');?></th>
                </tr>
                </thead>
                <tbody>
				<?foreach ($model->get_all_to_edit(5000) as $r):
				$mod = new $model_name($this,$r['id']);
				?>
                <tr>
					<td><a href="/admin55/edit/<?=$model_name?>/<?=$r['id']?>?<?=http_build_query($_GET)?>">
					<?if (strtolower($model_name)=='patient'):?>
					Карта пациента
					<?else:?>
					Редактировать
					<?endif;?>
					</a></td>
					
					<?foreach ($mod->get_col_settings(['column'=>1]) as $row):  
					?>
					<td style="width:400px" title="<?=$val?>">
						<remove><?=$model->get_table_row($row['name'],$r)?></remove>
						<div class="form_input">
						<?if ($row['type']=='date_time'):?>
							<?=date('d.m.Y H:i',$r[$row['name']])?>
						<?else:?>
							<?=$mod->generate_form($row['name'],$row['type'],'',$mod->json_to_select($row['select']),$row['placeholder'],'OnChange="ajax(\'adm_upd/'.$model_name.'/'.$r['id'].'\',\'key='.$row['name'].'&val=\'+this.value,\'#ajax_status\');"');?>
						<?endif;?>
						</div>
					</td>
					<?endforeach;?> 
					<?if (strtolower($model_name)=='patient'):?>
					<td  >
						<a target="_blank" href="/admin55/edit/Tasks/0/add?patient=<?=$r['id']?>"><?=l('Создать задачу');?></a>
						<br>
						<?=$mod->show_last_task();?>
					</td>
					<?elseif (strtolower($model_name)=='sms'):?>
					<td  >
						<a href="/admin55/edit/Sms/0/?send_sms=<?=$r['id']?>"><?=l('Разослать');?></a>
					 
					</td>
					<?elseif (strtolower($model_name)=='tasks'):?>
					<td  >
						<button id="btn_task_close<?=$r['id']?>" OnClick="$('#task_close<?=$r['id']?>').toggle();" class="btn" ><?=l('Закрыть задачу');?></button>
						<div id="task_close<?=$r['id']?>" style="display:none">
							<form   action="javascript:void(null);" method="post" OnSubmit="ajax_post('task_close/<?=$r['id']?>',this,'#res-task_close<?=$r['id']?>');">
								<input type="text" class="form-control" name="comment" placeholder="<?=l('Комментарий к закрытию');?>"><br>
								<select name="user_id" class="form-control">
									<option value="0"><?=l('Не перенаправлять задачу');?></option>
									<?foreach ((new Users($this))->get_all() as $usr):?>
									<option value="<?=$usr['id']?>"><?=$usr['name']?></option>
									<?endforeach;?>
								</select>
								<div id="res-task_close<?=$r['id']?>"></div>
								<button type="submit" class="btn  btn-primary" ><?=l('Завершить');?></button>
							</form>
						</div>
					</td>
					<?endif;?>
					
					<td><a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;"  href="/admin55/edit/<?=$model_name?>/<?=$r['id']?>/delete">Удалить</a></td>
                </tr>
				<?endforeach;?> 
				</tbody>
                <tfoot>
                <tr>
					<th  ><?=l('Редактировать');?></th>
					<?foreach ($model->get_table_cols() as $val):?>
					<th><?=$val?></th>
					<?endforeach;?>
					<?if (strtolower($model_name)=='patient'):?>
					<th  ><?=l('Создать задачу');?></th>
					<?elseif (strtolower($model_name)=='tasks'):?>
					<th  ><?=l('Закрыть задачу');?></th>
					<?elseif (strtolower($model_name)=='sms'):?>
					<th  ><?=l('Разослать');?></th>
					<?endif;?>
					
					<th  ><?=l('Удалить');?></th>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->

<!-- DataTables -->


    