<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header2.php');
$row_sum=$this->db->query("SELECT SUM(profit) profit, SUM(sum) sum, COUNT(id) count FROM transactions WHERE user_id='{$model->id}' ")->row_array();

?>
<nav class="cat__core__top-sidebar cat__core__top-sidebar--bg">
    <div class="row">
        <div class="col-xl-8 offset-xl-4">
            <div class="width-180 text-center pull-right hidden-md-down">
                <h4>€<?=number_format($row_sum['profit'],2,',',' ')?></h4>
                <p class="mb-0">Доход</p>
            </div> 
            <div class="width-180 text-center pull-right hidden-md-down pr-4">
                <h4>€<?=number_format($row_sum['sum'],2,',',' ')?></h4>
                <p class="mb-0">На сумму</p>
            </div> 
            <div class="width-180 text-center pull-right hidden-md-down">
                <h4><?=$row_sum['count']?></h4>
                <p class="mb-0">Обменов</p>
            </div>
            <h3>
                <span class="text-black">
                    <strong><?=$model->name?> <?=$model->suname?></strong>
                </span>
            </h3>
        </div>
    </div>
</nav>
<div class="row">
    <div class="col-xl-3">
        <section class="card cat__apps__profile__card bg-secondary">
            <div class="card-block text-center">
                <br />
                <br />
                <br />
                <p class="text-white">
                    Последний раз был: <?=date('d.m.Y H:i',$model->last_access)?>
                </p>
                <p class="text-white">
					<?if ($model->last_access<time()-15*60):?>
                    <span class="cat__core__donut cat__core__donut--danger"></span>
                    Не в сети
					<?else:?>
					<span class="cat__core__donut cat__core__donut--success"></span>
					В сети
					<?endif;?>
                </p>
            </div>
        </section>
        <section class="card">
            <div class="card-block">
                <h5 class="mb-3 text-black">
                    <strong>Действия</strong>
                </h5>
                <div class="btn-group-vertical btn-group-justified">
                    <button type="button" class="btn" data-toggle="modal" data-target="#send-message">Отправить письмо</button>
					<?if ($model->ban==0):?>
                    <button type="button" class="btn" data-toggle="modal" data-target="#ban-user">Забанить</button>
					<?else:?>
					<button type="button" class="btn"  OnClick="ajax('/ban/','id=<?=$modle->id?>&ban=0' ,'1');">Разбанить</button> 
					<?endif;?>
                    <a OnClick="if (!confirm('Вы уверены что желаете удалить этот элемент?')) return false;"  href="/admin55/edit/Users/<?=$model->id?>/delete"><button type="button" class="btn btn-danger">Удалить</button></a>
                </div>
            </div>
        </section>
        <section class="card">
            <div class="card-block">
                <h5 class="mb-3 text-black">
                    <strong>Документы</strong>
                </h5>
                <p id="del_passport" ><a href="#" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#photopass"><i class="icmn-eye"><!-- --></i> Паспорт или ID</a><a href="javascript: void(0);" OnClick="ajax('user_delete_val','id=<?=$model->id?>&type=passpord','#del_passport');" class="cat__core__link--underlined text-danger"><small><i class="icmn-cross"><!-- --></i></small></a></p>
                <p id="del_bank" ><a href="javascript: void(0);" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#photoutility"><i class="icmn-eye"><!-- --></i> Подтверждение адреса</a><a  OnClick="ajax('user_delete_val','id=<?=$model->id?>&type=bank','#del_bank');"  href="javascript: void(0);" class="cat__core__link--underlined text-danger"><small><i class="icmn-cross"><!-- --></i></small></a></p>
                <?for ($i=1;$i<=25;$i++):
				$k='add_file'.$i;
				if (strlen($model->$k)>0):
				$lastdoc=$i;
				?>
				<p id="del_doc<?=$i?>" ><a href="javascript: void(0);" OnClick="" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#photoutility<?=$i?>"><i class="icmn-eye"><!-- --></i> Доп. документ №<?=$i?></a><a href="javascript: void(0);"  OnClick="ajax('user_delete_val','id=<?=$model->id?>&type=add_file<?=$i?>','#del_doc<?=$i?>');"  class="cat__core__link--underlined text-danger"><small><i class="icmn-cross"><!-- --></i></small></a></p> 
				<?endif;?>
				<?endfor;?>
				
				<h5 class="mb-3 text-black">
                    <strong>История загрузок</strong>
                </h5>
				<?foreach ($this->db->query("SELECT * FROM user_aprove_log WHERE user_id='{$model->id}' ORDER BY id DESC ")->result_array() as $row):?>
				
				<p class="text-truncate"><?=date('d.m.Y H:i',$row['time']);?>  <a href="javascript: void(0);" OnClick="$('#olddoc_img').attr('src','/upload/<?=$row['file_name']?>');$('#olddoc').attr('href','/upload/<?=$row['file_name']?>');" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#olddoc_modal"><i class="icmn-eye"><!-- --></i> <?=$row['file_name']?></a> </p> 
				<?endforeach;?>
				 
			<div class="row">
            <div class="col-lg-12">
                <h5 class="text-black"><strong>Добавить документ</strong></h5>
				<form id="formadd" action="/admin55/edit/<?=$model_name?>/<?=$model->id?>/save" method="post" enctype="multipart/form-data"  role="form">
				<div class="mb-5">
                    <input type="file" OnChange="$('#formadd').submit();" name="add_file<?=$lastdoc+1?>" class="dropify" data-height="150" />
                </div>
				<input type="hidden" name="test">
				</form>
            </div>
        </div>
            </div>
        </section>
        <section class="card">
            <div class="card-block">
                <h5 class="mb-3 text-black">
                    <strong>Информация</strong>
                </h5>
                <dl class="row">
                    <dt class="col-xl-4">Имя:</dt>
                    <dd class="col-xl-8"><?=$model->name?></dd>
                    <dt class="col-xl-4">Фамилия:</dt>
                    <dd class="col-xl-8"><?=$model->suname?></dd>
					<?if ($model->network=='vk'):?>
					<dt class="col-xl-4">Профиль VK:</dt>
                    <dd class="col-xl-8"><a href="http://vk.com/id<?=$model->network_id?>"><?=$model->network_id?></a></dd>
					<?endif;?>
					<?if ($model->network=='facebook'):?>
					<dt class="col-xl-4">Профиль FB:</dt>
                    <dd class="col-xl-8"><a href="http://facebook.com/<?=$model->network_id?>"><?=$model->network_id?></a></dd>
					<?endif;?>
                    <dt class="col-xl-4">Страна</dt>
					<?$Country = new Country($this,$model->country_id);?>
                    <dd class="col-xl-8"><h3><span class="badge badge-default"><?=$Country->name?></span></h3></dd>
                    <dt class="col-xl-4">Город:</dt>
                    <dd class="col-xl-8"><h3><span class="badge badge-default"><?=$model->city?></span></h3></dd>
                    <dt class="col-xl-4">Адрес:</dt>
                    <dd class="col-xl-8"><a href="javascript: void(0);" data-toggle="modal" data-target="#map-address" target="_blank" class="cat__core__link--underlined"><i class="icmn-location2"></i><?=$model->street?></a></dd>
                    <dt class="col-xl-4">Почтовый индекс:</dt>
                    <dd class="col-xl-8"><?=$model->zip?></dd>
                    <dt class="col-xl-4">Номер телефона:</dt>
                    <dd class="col-xl-8">+<?=$model->tel_prefix?> <?=$model->tel?></dd>
                    <dt class="col-xl-4">IP адрес:</dt>
                    <dd class="col-xl-8"><?=$model->ip?></dd>
                </dl>
            </div>
        </section>
        <section class="card">
            <div class="card-block">
                <h5 class="mb-3 text-black">
                    <strong>Платежная информация</strong>
                </h5>
                <dl class="row">
                    <dt class="col-xl-3">BTC:</dt>
                    <dd class="col-xl-9"><p class="font-size-12"><a href="https://blockchain.info/address/<?=$model->cash3?>" target="_blank" class="cat__core__link--underlined"><?=$model->cash3?></a></p></dd>
                    <dt class="col-xl-3">ETH:</dt>
                    <dd class="col-xl-9"><p class="font-size-12"><a href="javascript: void(0);" target="_blank" class="cat__core__link--underlined"><?=$model->cash4?></a></p></dd>
                    <dt class="col-xl-3">LTC</dt>
                    <dd class="col-xl-9"><p class="font-size-12"><a href="javascript: void(0);" target="_blank" class="cat__core__link--underlined"><?=$model->cash5?></a></p></dd>
                    <dt class="col-xl-3">XRP:</dt>
                    <dd class="col-xl-9"><p class="font-size-12"><a href="javascript: void(0);" target="_blank" class="cat__core__link--underlined"><?=$model->cash6?></a></p></dd>
                   
                    <dt class="col-xl-3">Bank(SEPA):</dt>
                    <dd class="col-xl-9">
                    <?=$model->cash?>
                    </dd>
                </dl>
            </div>
        </section>
    </div>
    <div class="col-xl-9">
        <section class="card">
            <div class="card-block">
                <div class="nav-tabs-horizontal">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="javascript: void(0);" data-toggle="tab" data-target="#posts" role="tab">
                                <i class="icmn-menu"></i>
                                История обменов
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#messaging" role="tab">
                                <i class="icmn-credit-card"></i>
                               История авторизаций/апрувов
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#his-market" role="tab">
                                <i class="icmn-library"></i>
                                 История кошельков
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript: void(0);" data-toggle="tab" data-target="#settings" role="tab">
                                <i class="icmn-cog"></i>
                                Настройки пользователя
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content py-4">
                        <div class="tab-pane active" id="posts" role="tabcard">
                        <h5 class="text-black mt-4">
                                <strong>История обменов</strong>
                            </h5>
                        <div class="mb-5">
						<form action='?' id='form1' method="get">
                    <div class="row">
						<?if ($_GET['time1']==0) $_GET['time1']=date('Y-m-d',time()-3000*24*3600);
						if ($_GET['time2']==0) $_GET['time2']=date('Y-m-d');
						?>
                        <div class="col-lg-3">
                            <input type="date" id="time1" name="time1" value="<?=date('Y-m-d',strtotime($_GET['time1']))?>" class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату от" />
                        </div>
                        <div class="col-lg-1">
                            <div class="text-center mt-2">—</div>
                        </div>
                        <div class="col-lg-3">
                            <input type="date"  id="time2" name="time2" value="<?=date('Y-m-d',strtotime($_GET['time2']))?>"class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату до" />
                        </div>
                        <div class="col-lg-3">
                        	<button type="submit" class="btn btn-rounded btn-default display-inline-block mr-2 mb-2">Применить</button>
                        </div>
                    </div>
					</form>
                    <hr />
                    <div class="mb-5" style="padding-left:10px;">
                    <div class="row">
                    <ul class="list-inline">
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time1').val('<?=date('Y-m-d')?>');$('#time2').val('<?=date('Y-m-d')?>');" class="cat__core__link--underlined mr-2">Сегодня</a></li>
 					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600)?>');$('#time2').val('<?=date('Y-m-d',time()-24*3600)?>');" class="cat__core__link--underlined mr-2">Вчера</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);"  OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600*7)?>');$('#time2').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">За 7 дней</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time1').val('<?=date('Y-m-d',time()-24*3600*30)?>');$('#time2').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">30 дней</a></li>
					</ul>
                    </div>
                </div>
                </div>
			<div class="row">
            <div class="col-lg-12">
                <div class="mb-5">
				<form action="/admin55/save_pdf/<?=$model->id?>" method="post">
                    <table class="table thead-default table-hover nowrap" id="example1" width="100%">
                        <thead>
                        <tr>
                            <th> </th>
                            <th>Инвойс</th>
                            <th>Дата</th>
                            <th>Карта</th>
                            <th>Метод  </th>
                            <th>Крипта</th>
                            <th>Зачислено</th>
                            <th>Сделка</th>
                            <th>Доход</th>
                            <th>Курс без наценки</th>
                            <th>Курс с наценкой</th>
                            <th>Тип сделки</th>
                            <th>Кошелек</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        
                        <tbody>
						
						 
							<?
							$s1=$s2=$s3=0;
							 foreach ($this->db->query("SELECT   t.*, e.CardMasked FROM exchange e, transactions t  WHERE t.time>='".strtotime($_GET['time1'])."' AND  t.time<'".(strtotime($_GET['time2'])+24*3600)."'  AND e.user_id='{$model->id}'  AND t.ex_id=e.id ORDER BY e.id DESC ")->result_array() as $row):
							 $pay_methods=row('pay_methods',$row['pay_method']);
							 $valut=row('valut',$row['crypto']);
							 $s1+=$row['sum'];
							 $s2+=$row['sum_to']; 
							  $s3+=$row['profit']; 
							 ?>
					 		<tr>
                            <td><input type="checkbox" value="<?=$row['ex_id']?>" name="download[]" >  </td>
                            <td><a target="_blank" href="https://btcbit.net/pdf_exchange/<?=$row['ex_id']?>"><?=$row['ex_id']?></a></td>
                            <td><?=date('d.m.Y H:i',$row['time'])?></td>
                            <td>   
								<a href="javascript: void(0);" OnClick="ajax('trans_detalis_card','id=<?=$row['id']?>','#ajax_trans');" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#details-trans"><i class="icmn-eye"><!-- --></i> <?=$row['CardMasked']?></a>
							</td>
                            <td class="text-center"><img src="/upload/<?=$pay_methods['img']?>" title="<?=$pay_methods['name']?>" width="25" height="25"> </td>
                            <td class="text-center"><img src="/upload/<?=$valut['img']?>" title="<?=$valut['name']?>" width="25" height="25"> </td>
                            <td><?=$row['sum_to']?></td>
                            <td>€<a href="/admin55/page/transaction?id=<?=$row['id']?>" target="_blank"><?=$row['sum']?></a></td>
                            <td><?=$row['profit']?></td>
                            <td><?=$row['kurs_bn']?></td>
                            <td><?=$row['kurs']?></td>
                            <td><?if ($row['buy']) echo 'BUY'; else echo 'SELL';?></td>
                            <td><a href="https://blockchain.info/address/<?=$row['cash']?>" target="_blank" class="cat__core__link--underlined"><?=$row['cash']?><a/></td>
                            <td>
                            <a href="javascript: void(0);" OnClick="ajax('trans_detalis','id=<?=$row['id']?>','#ajax_trans');" class="cat__core__link--underlined mr-2" data-toggle="modal" data-target="#details-trans"><i class="icmn-eye"><!-- --></i> Детали транзакции</a>
                    		</td>
                        </tr>
                        <?endforeach;?>
                        </tbody>
						<tfoot>
                        <tr>
                            <th>Всего</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>€<?=$s1?></th>
                            <th><?=$s2?></th>
                            <th>€<?=$s3?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							 <th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
					<button class="btn">Скачать PDF</button>
					</form>
                </div>
            </div>
        </div>
			
			
                        </div>
                        <div class="tab-pane" id="messaging" role="tabcard">
                        <div class="row">
            			<div class="col-lg-12">
            			    <h5 class="text-black mt-4">
                                <strong> История авторизаций/апрувов</strong>
                            </h5>
            			<div class="table-responsive mb-5">
                    <table class="table table-hover thead-default nowrap"> 
                        <thead>
                            <tr>
                                <th>Date</th><th>Status</th><th>Admin</th><th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
							<?foreach ($this->db->query("SELECT * FROM log_aprove WHERE  time>='".strtotime($_GET['time1'])."' AND   time<'".(strtotime($_GET['time2'])+24*3600)."'  AND user_id='{$model->id}' ORDER BY id DESC ")->result_array() as $row):
							$adm =new Users($this,$row['admin_id']);
							$lv = new User_Type($this,$row['user_level']);
							?>
								<tr>
									<td><?=date('d.m.Y H:i',$row['time'])?></td><td><?=$row['text']?></td><td><?=$adm->name?></td><td><?=$lv->name?></td>
								</tr>
							<?endforeach;?>
                        </tbody>
                    </table>
                </div>
                <hr />
                        <div class="mb-5">
						<form action='?' id='form1' method="get">
                    <div class="row">
						 
                        <div class="col-lg-3">
                            <input type="date" id="time12" name="time1" value="<?=date('Y-m-d',strtotime($_GET['time1']))?>" class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату от" />
                        </div>
                        <div class="col-lg-1">
                            <div class="text-center mt-2">—</div>
                        </div>
                        <div class="col-lg-3">
                            <input type="date"  id="time22" name="time2" value="<?=date('Y-m-d',strtotime($_GET['time2']))?>"class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату до" />
                        </div>
                        <div class="col-lg-3">
                        	<button type="submit" class="btn btn-rounded btn-default display-inline-block mr-2 mb-2">Применить</button>
                        </div>
                    </div>
					</form>
                    <hr />
                    <div class="mb-5" style="padding-left:10px;">
                    <div class="row">
                    <ul class="list-inline">
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time12').val('<?=date('Y-m-d')?>');$('#time22').val('<?=date('Y-m-d')?>');" class="cat__core__link--underlined mr-2">Сегодня</a></li>
 					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time12').val('<?=date('Y-m-d',time()-24*3600)?>');$('#time22').val('<?=date('Y-m-d',time()-24*3600)?>');" class="cat__core__link--underlined mr-2">Вчера</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);"  OnClick="$('#time12').val('<?=date('Y-m-d',time()-24*3600*7)?>');$('#time22').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">За 7 дней</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time12').val('<?=date('Y-m-d',time()-24*3600*30)?>');$('#time22').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">30 дней</a></li>
					</ul>
                    </div>
                </div>
                </div>
               
                <div class="table-responsive mb-5">
                    <table class="table table-hover thead-default nowrap">
						 
                        <thead>
                            <tr>
                               <th>Date</th><th>IP</th><th>Browser</th><th>REF</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?foreach ($this->db->query("SELECT * FROM user_log WHERE time>='".strtotime($_GET['time1'])."' AND   time<'".(strtotime($_GET['time2'])+24*3600)."' AND user_id='{$model->id}'  ORDER BY id DESC ")->result_array() as $row):?>
								<tr>
									<td><?=date('d.m.Y H:i',$row['time'])?></td><td><?=$row['ip']?></td><td><?=$row['browser']?></td><td><?=$row['ref']?></td>
								</tr>
							<?endforeach;?>
                        </tbody>
                       
                    </table>
                 
                </div>
            </div>
        </div>    
                        </div>
                        <div class="tab-pane" id="his-market" role="tabcard">
                            <h5 class="text-black mt-4">
                                <strong>История </strong>
                            </h5>
                     <div class="mb-5">
						<form action='?' id='form1' method="get">
                    <div class="row">
						 
                        <div class="col-lg-3">
                            <input type="date" id="time13" name="time1" value="<?=date('Y-m-d',strtotime($_GET['time1']))?>" class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату от" />
                        </div>
                        <div class="col-lg-1">
                            <div class="text-center mt-2">—</div>
                        </div>
                        <div class="col-lg-3">
                            <input type="date"  id="time23" name="time2" value="<?=date('Y-m-d',strtotime($_GET['time2']))?>"class="form-control  width-200 display-inline-block mr-2 mb-2" placeholder="выберите дату до" />
                        </div>
                        <div class="col-lg-3">
                        	<button type="submit" class="btn btn-rounded btn-default display-inline-block mr-2 mb-2">Применить</button>
                        </div>
                    </div>
					</form>
                    <hr />
                    <div class="mb-5" style="padding-left:10px;">
                    <div class="row">
                    <ul class="list-inline">
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time13').val('<?=date('Y-m-d')?>');$('#time23').val('<?=date('Y-m-d')?>');" class="cat__core__link--underlined mr-2">Сегодня</a></li>
 					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time13').val('<?=date('Y-m-d',time()-24*3600)?>');$('#time23').val('<?=date('Y-m-d',time()-24*3600)?>');" class="cat__core__link--underlined mr-2">Вчера</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);"  OnClick="$('#time13').val('<?=date('Y-m-d',time()-24*3600*7)?>');$('#time23').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">За 7 дней</a></li>
  					<li class="list-inline-item"><a href="javascript: void(0);" OnClick="$('#time13').val('<?=date('Y-m-d',time()-24*3600*30)?>');$('#time23').val('<?=date('Y-m-d',time())?>');" class="cat__core__link--underlined mr-2">30 дней</a></li>
					</ul>
                    </div>
                </div>
                </div>
						<div class="table-responsive mb-5">
                    <table class="table thead-default nowrap">
                        <thead>
                            <tr>
                                <th>Дата</th><th>Система</th><th>Кошелек</th> 
                            </tr>
                        </thead>
                         	
                        <tbody>
                            <?foreach ($this->db->query("SELECT * FROM user_cash  WHERE user_id='{$model->id}' AND  time>='".strtotime($_GET['time1'])."' AND   time<'".(strtotime($_GET['time2'])+24*3600)."'  ORDER BY id DESC ")->result_array() as $row):?>
								<tr>
									<td><?=date('d.m.Y H:i',$row['time'])?></td><td><?=$row['valut']?></td><td><?=$row['cash']?></td> 
								</tr>
							<?endforeach;?>
                        </tbody>
                    </table>
                </div>
 
                        </div>
                        <div class="tab-pane" id="settings" role="tabcard">
                            <h5 class="text-black mt-4">
                                <strong>Персональная информация</strong>
                            </h5>
							 
							<div id="result_sav" class="alert alert-danger alert-dismissible"> 
								 
							 </div>
							 
							<form  action="javascript:void(null);" method="post" OnSubmit="ajax_post('save_admin/Users/<?=$model->id?>',this,'#result_sav');" method="post" enctype="multipart/form-data"  role="form">
								
								<div class="row"> 
									<?foreach ($model->generate_form_rows('form-control') as $k=>$form_row):?>
									 <div class="col-lg-6">
										<div class="form-group">
										<label  class="form-control-label" for="form_<?=$k?>"><?=$form_row['title']?></label>
										<?=$form_row['form']?>
										</div>
									</div>
									<?endforeach;?> 
								</div>
                            <h5 class="text-black mt-4">
                                <strong>Пароли</strong>
                            </h5>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l3">Новый пароль</label>
                                        <input type="password" name="password" class="form-control" id="l3">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">Еще раз</label>
                                        <input type="password" name="password2"  class="form-control" id="l4">
                                    </div>
                                </div>
                            </div>
                            <h5 class="text-black mt-4">
                                <strong>Платежная информация</strong>
                            </h5>
                            <div class="row"> 
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l3">BTC</label>
                                        <input type="text" name="cash3" class="form-control" id="l3" value="<?=$model->cash3?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">ETH</label>
                                        <input type="text"  name="cash4" class="form-control" id="l4" value="<?=$model->cash4?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">LTC</label>
                                        <input type="text"  name="cash5" class="form-control" id="l4" value="<?=$model->cash5?>">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">XRP</label>
                                        <input type="text"  class="form-control" id="l4" value="<?=$model->cash6?>"  name="cash6">
                                    </div>
                                </div>
                            </div>
                            <h5 class="text-black mt-4">
                                <strong>Партнерка</strong>
                            </h5>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l3">BTC кошелек партнера</label>
                                        <input type="text" class="form-control" value="<?=$model->partner_cash?>"  name="partner_cash" id="l3">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">account_id партнера на Coinbase</label>
                                        <input type="text" class="form-control" value="<?=$model->partner_acc_id?>"  name="partner_acc_id" id="l4">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label" for="l4">Сюда введите слово passport</label>
                                        <input type="text" class="form-control" value="<?=$model->pay_methods?>"  name="pay_methods" id="l4" value="passport">
                                    </div>
                                </div>
                            </div>
                             
                            <div class="form-actions">
                                <div class="form-group">
                                    <button type="submit" class="btn width-200 btn-primary">Сохранить</button>
                                </div>
                            </div>
							</form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<div class="modal fade" id="photopass" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><a href="/upload/<?=$model->passport_doc?><?=$model->passport?>">Скачать</a></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="/upload/<?=$model->passport_doc?><?=$model->passport?>" class="img-fluid" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="photoutility" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><a href="/upload/<?=$model->bank_doc?><?=$model->bank?>">Скачать</a></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">  
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="/upload/<?=$model->bank_doc?><?=$model->bank?>" class="img-fluid" />  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
<?for ($i=1;$i<=25;$i++):
$k='add_file'.$i;
if (strlen($model->$k)>0):
?>
<div class="modal fade" id="photoutility<?=$i?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><a href="/upload/<?=$model->$k?>">Скачать</a></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img src="/upload/<?=$model->$k?>" class="img-fluid" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
<?endif;?>
<?endfor;?>
<div class="modal fade" id="map-address" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Просмотр адреса</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
<iframe width="100%" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=<?=urlencode($Coutry->name.' '.$model->city.' '.$model->street)?>&key=AIzaSyBvrCkPfvuT9InGk6pLfincpYTDwbZPPoY" allowfullscreen></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="details-bin" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Детали для BIN 493702</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<div class="modal-body">
                <dl class="row">
                    <dt class="col-xl-4">BIN:</dt>
                    <dd class="col-xl-8">493702</dd>
                    <dt class="col-xl-4">Банк эмитент:</dt>
                    <dd class="col-xl-8">BANCO DE GALICIA Y BUENOS AIRES, S.A.</dd>
                    <dt class="col-xl-4">Бренд карты:</dt>
                    <dd class="col-xl-8">VISA</dd>
                    <dt class="col-xl-4">Тип карты:</dt>
                    <dd class="col-xl-8">CREDIT</dd>
                    <dt class="col-xl-4">Уровень карты:</dt>
                    <dd class="col-xl-8">BUSINESS</dd>
                    <dt class="col-xl-4">ISO название страны:</dt>
                    <dd class="col-xl-8">ARGENTINA</dd>
                    <dt class="col-xl-4">ISO номер страны:</dt>
                    <dd class="col-xl-8">32</dd>
                </dl>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade modal-size-large" id="send-message" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog" role="document">
		<form   class="form-horizontal" action="javascript:void(null);" method="post" OnSubmit="ajax_post('send_mail',this,'#resmail');">
      
        <div class="modal-content">
            <div class="modal-header">
                <h4>Отправить письмо <?=$model->name?> <?=$model->suname?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" disabled class="form-control" name="email" value="<?=$model->email?>" />
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="name" placeholder="Заголовок письма" />
                </div>
				<div class="form-group">
					<label for="message-ban" class="form-control-label">Текст:</label>
					<textarea class="form-control" name="text"></textarea>
				  </div>
                <div id="resmail"  ></div>
            </div>
            <div  class="modal-footer">
                <button type="submit" class="btn width-200 btn-primary"><i class="fa fa-send mr-2"></i> Отправить</button>
                 
            </div>
        </div>
		</form>
    </div>
</div>
<div class="modal fade" id="ban-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel22">Забанить пользователя</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
          <div class="form-group">
            <label for="message-ban" class="form-control-label">Причина бана:</label>
            <textarea class="form-control" id="message-ban"></textarea>
          </div>
         
      </div>
      <div class="modal-footer">
		 <button OnClick="ajax('/ban/','id=<?=$model->id?>&ban=1&text='+$('#message-ban').val(),'#exampleModalLabel22');" type="button" class="btn btn-secondary" >Забанить</button>
        <button   type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="details-trans" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Просмотр деталей карты</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
                <dl id="ajax_trans" class="row">
                    
                </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="olddoc_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Просмотр документа <a id="olddoc" href="">(Скачать)</a></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img id="olddoc_img" src="http://vancouver.ca/images/cov/content/John-UtilityBill-NoLabels.png" class="img-fluid" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<!-- START: page scripts -->
<script>
    $(function(){

        $('.select2').select2();
        $('.select2-tags').select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });

        $('.selectpicker').selectpicker();

    })
</script>
<script>
    $(function() {

        $('.dropify').dropify();

    });
</script>
<script>
    $(function() {

        // TEXT EDITOR
        $(function() {
            $('.summernote').summernote({
                height: 200
            });
        });

    });
</script>
<script>
    $(function(){


		
        $('#example1').DataTable({
            responsive: true
        });

        $('#example2').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
        });

        $('#example3').DataTable({
            autoWidth: true,
            scrollX: true,
            fixedColumns: true
        });
    });
</script>
<script>
    $(function(){

        $('.datepicker-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            }
        });

        $('.datepicker-only-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'LL'
        });

        $('.timepicker-init').datetimepicker({
            widgetPositioning: {
                horizontal: 'left'
            },
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'LT'
        });

        $('.datepicker-inline-init').datetimepicker({
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            inline: true,
            sideBySide: false
        });

        $('.timepicker-inline-init').datetimepicker({
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: 'fa fa-arrow-left',
                next: 'fa fa-arrow-right'
            },
            format: 'LT',
            inline: true,
            sideBySide: false
        });

    })
</script>
<script>
    $(function() {

        ///////////////////////////////////////////////////////////
        // ADJUSTABLE TEXTAREA
        autosize($('.adjustable-textarea'));

        ///////////////////////////////////////////////////////////
        // CALENDAR
        $('.example-calendar-block').fullCalendar({
            //aspectRatio: 2,
            height: 450,
            header: {
                left: 'prev, next',
                center: 'title',
                right: 'month, agendaWeek, agendaDay'
            },
            buttonIcons: {
                prev: 'none fa fa-arrow-left',
                next: 'none fa fa-arrow-right',
                prevYear: 'none fa fa-arrow-left',
                nextYear: 'none fa fa-arrow-right'
            },
            Actionable: true,
            eventLimit: true, // allow "more" link when too many events
            viewRender: function(view, element) {
                if (!(/Mobi/.test(navigator.userAgent)) && jQuery().jScrollPane) {
                    $('.fc-scroller').jScrollPane({
                        autoReinitialise: true,
                        autoReinitialiseDelay: 100
                    });
                }
            },
            eventClick: function(calEvent, jsEvent, view) {
                if (!$(this).hasClass('event-clicked')) {
                    $('.fc-event').removeClass('event-clicked');
                    $(this).addClass('event-clicked');
                }
            },
            defaultDate: '2016-05-12',
            events: [
                {
                    title: 'All Day Event',
                    start: '2016-05-01',
                    className: 'fc-event-success'
                },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: '2016-05-09T16:00:00',
                    className: 'fc-event-default'
                },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: '2016-05-16T16:00:00',
                    className: 'fc-event-success'
                },
                {
                    title: 'Conference',
                    start: '2016-05-11',
                    end: '2016-05-14',
                    className: 'fc-event-danger'
                }
            ]
        });

        ///////////////////////////////////////////////////////////
        // SWAL ALERTS
        $('.swal-btn-success').click(function(e){
            e.preventDefault();
            swal({
                title: "Following",
                text: "Now you are following Artour Scott",
                type: "success",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ok"
            });
        });

        $('.swal-btn-success-2').click(function(e){
            e.preventDefault();
            swal({
                title: "Friends request",
                text: "Friends request was succesfully sent to Artour Scott",
                type: "success",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ok"
            });
        });

    });
</script>
<!-- END: page scripts -->
<!-- END: apps/profile -->

<script type="text/javascript"> 
$('textarea').summernote({
   
});
</script>
</div>
</body>
</html>
 