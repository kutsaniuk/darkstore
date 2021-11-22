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
					<h4><i class="icon fa fa-ban"></i> Заказ создан  <?=$id?>!</h4>
					 
				 </div>
				<?endif;?>
				<form action="/admin55/page/new_order" method="post" enctype="multipart/form-data"  role="form">
					<div class="box-body">
						
						<div id="div_name" class="form-group">
							<label for="phone">Телефон</label>
							<input required  placeholder="Телефон" id="phone"  type="text" class="form-control" value="" name="phone" />						
						</div>
						 
						<div id="div_name" class="form-group">
							<label for="adress">Адрес</label>
							<input required  placeholder="Адрес" id="adress"  type="text" class="form-control" value="" name="adress" />						
						</div>
						<div id="div_name" class="form-group">
							<label for="rack">Партнер</label>
							<select name="parnet_id">
							<?foreach($this->db->get('partners')->result_array() as $row):?>
								<option value="<?=$row['id']?>"><?=$row['name']?></option>
							<?endforeach;?>
							</select>
						</div>
					</div>
					 
					<button type="submit" class="btn btn-block btn-primary">Создать</button>
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