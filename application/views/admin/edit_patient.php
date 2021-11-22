<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?>
<h4 class="card-title"><?=$model_name?></h4>
	   <p class="card-description">
            <?if (count($result)):?>
				<div class="alert alert-danger alert-dismissible"> 
					<h4><i class="icon fa fa-ban"></i> Ошибка!</h4>
					<?foreach ($result as $v) echo '<br>'.$v;?> 
				 </div>
				<?endif;?>
		</p>
		
		<ul class="nav nav-tabs" role="tablist">
			<li OnClick="$('li .active').removeClass('active');$(this).addClass('active');" class="active "><a class="btn btn-secondary btn-fw" href="#info" aria-controls="info" role="tab" data-toggle="tab"><?=l('Информация');?></a></li>
			<li OnClick="$('li .active').removeClass('active');$(this).addClass('active');"  ><a class="btn btn-secondary btn-fw"  href="#pay0" aria-controls="pay0" role="tab" data-toggle="tab"><?=l('Оплаты услуг');?></a></li>
			<li OnClick="$('li .active').removeClass('active');$(this).addClass('active');"  ><a class="btn btn-secondary btn-fw"  href="#pay1" aria-controls="pay1" role="tab" data-toggle="tab"><?=l('Оплаты доп.услуг');?></a></li>
			<li OnClick="$('li .active').removeClass('active');$(this).addClass('active');"   ><a class="btn btn-secondary btn-fw"  href="#tasks" aria-controls="tasks" role="tab" data-toggle="tab"><?=l('Задачи');?></a></li> 
		 </ul>
		 <br>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="info"> 
				<form action="/admin55/edit/<?=$model_name?>/<?=$model->id?>/save" method="post" enctype="multipart/form-data"  role="form">
					 
					<?foreach ($model->generate_form_rows('form-control') as $k=>$form_row):?>
						<div class="form-group">
						<label for="form_<?=$k?>"><?=$form_row['title']?></label>
						<?=$form_row['form']?>
						</div>
					<?endforeach;?>
					 
					<a href="/admin55/edit/<?=$model_name?>/" class="btn btn-block btn-default">Назад</a>
					<button type="submit" class="btn btn-block btn-primary">Сохранить</button>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="pay0">
				<?
				$_GET['patient']=$model->id; 
				$data['model'] = new Payment_Service($this,$id);
				$data['model_name'] = 'Payment_Service';
				$this->load->view('admin/edit_patient_inner',$data);
				?>
			</div>
			<div role="tabpanel" class="tab-pane" id="pay1">
				<?
				$_GET['patient']=$model->id; 
				$data['model'] = new Payment_Add_Service($this,$id);
				$data['model_name'] = 'Payment_Add_Service';
				$this->load->view('admin/edit_patient_inner',$data);
				?>
			</div>
			<div role="tabpanel" class="tab-pane" id="tasks">
				<?
				$_GET['patient']=$model->id; 
				$data['model'] = new Tasks($this,$id);
				$data['model_name'] = 'Tasks';
				$this->load->view('admin/edit_patient_inner',$data);
				?>
			</div> 
		</div>


<script src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
    $('form textarea').each(function(e){
        CKEDITOR.replace( this.id, {
		  extraPlugins: 'imageuploader'
		});
    });
	 
	$('#my-tabs a').click(function (e) {
	  e.preventDefault()
	  $(this).tab('show')
	})
	
	 // Datatables
        $('.dataTable').DataTable({
            "lengthMenu": [[50, 100, 200, -1], [50, 100, 200, "All"]],
            responsive: true,
            "autoWidth": false 
			 ,"language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
            }
        });
		$('remove').remove();
});
</script> 
<?php
include('footer.php');?> 