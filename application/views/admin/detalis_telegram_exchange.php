<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Детали </h3>
			   </div>
            <!-- /.box-header -->
			
            <div class="box-body">
			 
			<div class="table-responsive">
              <table id="table_edit"  class="table table-bordered table-striped" >
                
                <tbody>
				 
                <tr> 
					<td ><?=date('d.m.Y',$model->create_time)?></td>
					<td >Rate, %/ price</td>
					<td >BTC, USD</td>
					<td >BTC, EUR</td>
					<td >RUB</td>
					<td >Course</td>
					<td >USD</td>
					<td >Course</td>
					<td >EUR</td>
				 </tr>
				 <tr> 
					<td >Client trade amount</td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->sum_trade*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->sum_trade,2)?></td>
				 </tr>
				 <tr> 
					<td >Interest</td>
					<td ><?=$model->com?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->sum_eur*$model->kurs_usd_eur*$model->com/100,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->sum_eur*$model->com/100,2)?></td>
				 </tr>
				 <tr> 
					<td > Client total amount</td>
					<td ><?=$model->com?></td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut==7):?><?=number_format($model->sum,2)?><?endif;?></td>
					<td ><?if ($model->valut==7):?><?=$model->kurs_rub_usd?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->sum_eur*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->sum_eur,2)?></td>
				 </tr>
				 <tr> 
					<td > Trade amount (buy for client)</td>
					<td ><?=$model->kurs?></td>
					<td ><?=$model->sum_to?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->sum_trade*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->sum_trade,2)?></td>
				 </tr> 
				 <tr> 
					<td > Profit/loss:</td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
				 </tr>
				 <tr> 
					<td > Interest Q_BTC</td>
					<td ><?=$model->q_btc_com?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->sum_eur*$model->kurs_usd_eur*$model->q_btc_com/100,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->sum_eur*$model->q_btc_com/100,2)?></td>
				 </tr>
				<tr> 
					<td > Andrej commission  </td>
					<td ><?if($model->valut==7):?><?=$model->andrey_com?><?endif;?></td>
					<td > </td>
					<td > </td>
					<td ><?if($model->valut==7):?><?=number_format($model->sum/100*$model->andrey_com,2)?><?endif;?></td>
					<td > </td>
					<td > </td>
					<td > <?if($model->valut==7):?><?=$model->kurs_rub_eur?><?endif;?></td>
					<td >-<?=number_format($model->andrey_com_eur,2)?></td>
				 </tr> 			
				<tr> 
					<td > Buy commission </td>
					<td ><?=$model->buy_com?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?>-<?=number_format($model->sum_eur*$model->buy_com/100*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td >-<?=number_format($model->sum_eur*$model->buy_com/100,2)?></td>
				 </tr> 	
				<tr> 
					<td > Transfer commission</td>
					<td ><?=$model->trade_com?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?>-<?=number_format($model->sum_eur*$model->trade_com/100*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td >-<?=number_format($model->sum_eur*$model->trade_com/100,2)?></td>
				 </tr> 		
				<tr> 
					<td >Profit/loss</td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->profit*$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->profit,2)?></td>
				 </tr> 		
				 <tr> 
					<td >Zhenya commission</td>
					<td ><?=$model->zhenya_com?></td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td > </td>
					<td ><?if ($model->valut!=2):?><?=number_format($model->zhenya_profit *$model->kurs_usd_eur,2)?><?endif;?></td>
					<td ><?if ($model->valut!=2):?><?=$model->kurs_usd_eur?><?endif;?></td>
					<td ><?=number_format($model->zhenya_profit  ,2)?></td>
				 </tr>
				</tbody>
                 
              </table>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
 
        </div>
        <!-- /.col -->
      </div> 

<?include('footer.php');?>
	  <script>
  $(function () {
    $("#table_edit").DataTable();
    
  });
</script>
<!-- DataTables -->
<script src="<?=$path?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?=$path?>plugins/datatables/dataTables.bootstrap.min.js"></script>
</body>
</html>