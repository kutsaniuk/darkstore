<div class="box-header">
              <h3 class="box-title">Биды</h3>
			  
			  <form action="/admin55/page/bidds" method="get">
				<table class="table">
					<tr>
						<td>
							<select OnChange="ajax('get_compaigns','id='+this.value,'#add_form');$('#f_acc_id').val(this.value);" class="form-control" id="acc_id" name="acc_id">
								<option value="" >-- Выберите клиента --</option>
							<?foreach ((new BaseRow( $this,'ya_accounts'))->get_all(999) as $client):?>
								
								<option <?=($_GET['acc_id']==$client['id']?'selected':'' ) ?> value="<?=$client['id']?>" ><?=$client['login']?></option>
							<?endforeach;?>
							</select>
						</td>
						<td id="add_form">
						
						</td>
						<td id="add_form_group">
						
						</td>
					</tr>
				</table>
				
				 
				<button type="submit" class="btn btn-block btn-primary">Показать слова</button>
			  </form>
			  
			
			
			</div>

<form action="/admin55/page/bidds" method="post">
<div class="box-header">
              <h3 class="box-title">Изменение</h3>
			  
			  
			  
				<table class="table">
					<tr>
						<td>
							<select   class="form-control"  name="type"> 
								<?
								$types=[1=>'Применить к кампании',3=>'Применить к группе',2=>'Применить к слову',4=>'Применить к выбранным словам'];
								if ($_GET['wid']==0) unset($types[2]);
								if ($_GET['adgroups_id']==0) unset($types[3]);
								
								foreach ($types as $k=>$v):?> 
									<option <?=($k==$_POST['type']?'selected':'' ) ?> value="<?=$k?>" ><?=$v?></option>
								<?endforeach;?>
							</select> 
						</td>
						
						<td>
							<input type="hidden" value="<?=$_GET['wid']?>" name="wid">
							<input type="hidden" value="<?=$_GET['acc_id']?>" id="f_acc_id" name="acc_id">
							<input type="hidden" value="<?=$_GET['compaign_id']?>"  id="f_compaign_id" name="compaign_id">
							<input type="hidden" value="<?=$_GET['adgroups_id']?>"  id="f_adgroups_id" name="adgroups_id">
							
							
							<?if ($_GET['wid']>0):?>
							<input class="form-control" type="disabled" value="<?=$_GET['w']?>">
							<?endif;?>
						</td>
						
						<td>
							<select   class="form-control" id="position" name="position"> 
								<?foreach (['P11','P12','P13','P14','P21','P22','P23','P24'] as $v):?> 
									<option <?=($v==$_POST['position']?'selected':'' ) ?> value="<?=$v?>" ><?=$v?></option>
								<?endforeach;?>
							</select> 
						</td>
						<td>
							<input class="form-control" placeholder="+%"  value="<?=$_POST['procent']?>" name="procent">
						</td>
						<td>
							<input class="form-control" placeholder="Лимит" value="<?=$_POST['high']?>" name="high">
						</td>
						<td>
							<input class="form-control" placeholder="Как часто проверять? (в часах)" value="<?=$_POST['often']?>"  name="often">
						</td>
					</tr>
				</table>
				 
				<button type="submit" class="btn btn-block btn-primary">Сохранить</button>
			 
			  
			
			
			</div>