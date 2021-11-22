<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?>
<div class="row">
        <div class="col-xs-12">
          <div class="box">
				<?if (count($result)):?>
				<div class="alert alert-danger alert-dismissible"> 
					<h4><i class="icon fa fa-ban"></i> Ошибка!</h4>
					<?foreach ($result as $v) echo '<br>'.$v;?> 
				 </div>
				<?endif;?>
				<?if ($do=='save'):?>
				<div class="alert alert-success "> 
					<h4><i class="icon fa fa-ban"></i> Сохранено!</h4>
					 
				 </div>
				<?endif;?>
				<form action="/admin55/edit/<?=$model_name?>/<?=$model->id?>/save" method="post" enctype="multipart/form-data"  role="form">
					<div class="box-body">
					<?foreach ($model->generate_form_rows('form-control') as $k=>$form_row):?>
						
						<div id="div_<?=$k?>" class="form-group">
						<label for="form_<?=$k?>"><?=$form_row['title']?></label>
						<?=$form_row['form']?>
						</div>
					<?endforeach;?>
					</div>
					<a href="/admin55/edit/<?=$model_name?>/" class="btn btn-block btn-default">Назад</a>
					<button type="submit" class="btn btn-block btn-primary">Сохранить</button>
				</form>
		  </div>
		</div>
</div> 

<?include('footer.php');?>
<script src="/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(function(){
    $('textarea.editor').each(function(e){
        CKEDITOR.replace( this.id, {
		  extraPlugins: 'imageuploader'
		});
    });
	 
	
});
</script>
</body>
</html>