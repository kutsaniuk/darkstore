<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?>
<div class="row">
        <div class="col-xs-12">
          <div class="box">
				<?if (count($result)):?>
				<div class="alert alert-danger alert-dismissible"> 
				 
					<?foreach ($result as $v) echo '<br>'.$v;?> 
				 </div>
				<?endif;?>
				<form action="/admin55/page/sender" method="post" enctype="multipart/form-data"  role="form">
					<div class="box-body">
					 
						<div id="div_1" class="form-group">
						<label for="form_1">Текст</label>
						<textarea class="form-control" name="text" ></textarea>
						</div>
					 
					</div>
					 
					<button type="submit" class="btn btn-block btn-primary">Отправить всем</button>
				</form>
		  </div>
		</div>
</div>
<?if ($model->budget_fix==0):?>
<script>
	$('#div_day').hide();
</script>
<?endif;?>
<script>
	 
	$( "#budget_fix" ).click(function() {
	  $('#div_day').toggle();
	});
	
</script>

<?include('footer.php');?>
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
</body>
</html>