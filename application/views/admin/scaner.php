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
					<h4><i class="icon fa fa-ban"></i> Товар принят!</h4>
					 
				 </div>
				<?endif;?>
				<form action="/admin55/page/scaner" method="post" enctype="multipart/form-data"  role="form">
					<div class="box-body">
						
						<div id="div_name" class="form-group">
							<label for="code">Штрихкод</label>
							<input required  placeholder="Штрихкод" id="code"  type="text" class="form-control" value="" name="code" />						
						</div>
						 
						<div id="div_name" class="form-group">
							<label for="shelf">Полка</label>
							<input required  placeholder="Полка" id="shelf"  type="text" class="form-control" value="" name="shelf" />						
						</div>
						<div id="div_name" class="form-group">
							<label for="rack">Стеллаж</label>
							<input required  placeholder="Стеллаж" id="rack"  type="text" class="form-control" value="" name="rack" />						
						</div>
					</div>
					 
					<button type="submit" class="btn btn-block btn-primary">Принять</button>
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