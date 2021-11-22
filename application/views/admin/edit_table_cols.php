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
		
		
 
				<form action="/admin55/edit/<?=$model_name?>/<?=$model->id?>/save?<?=http_build_query($_GET)?>" method="post" enctype="multipart/form-data"  role="form">
					 
					<?
					if ($model->id==0) $model->table_name=strtolower($_GET['table_name']);
					foreach ($model->generate_form_rows('form-control') as $k=>$form_row):?>
						<div class="form-group">
						<label for="form_<?=$k?>"><?=$form_row['title']?></label>
						<?=$form_row['form']?>
						</div>
					<?endforeach;?>
					<br><br>
					<div class="form-group">
						<label for="form_<?=$k?>"><?=l('Элементы списка');?><br><?=l('Новый элемент вводите на новой строке');?></label>
					</div>	
					<div class="form-group">
						<textarea rows="9" name="select"><?=$model->json_to_text($model->select)?></textarea>
					</div>
						
					<a href="/admin55/edit/<?=$model_name?>/?<?=http_build_query($_GET)?>" class="btn btn-block btn-default">Назад</a>
					<button type="submit" class="btn btn-block btn-primary">Сохранить</button>
				</form>
 


<script src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
    $('textarea').each(function(e){
        CKEDITOR.replace( this.id, {
		  extraPlugins: 'imageuploader'
		});
    });
	 
	
});
</script>
<?include('footer.php');?> 