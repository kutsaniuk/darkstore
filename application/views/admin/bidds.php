<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?> 
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            
			
			
			<?include('bid_form.php');?>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="table_edit"  class="table table-bordered table-striped" >
                <thead>
                <tr> 
					<th>Галочка</th>
					<th>Ключевая фраза</th>
					<th>Текущая ставка</th>
					<th>Ставки для позиций</th>					
                </tr>
                </thead>
                <tbody>
				<?if($_GET['acc_id']>0):
				$bids = (new Ya_Bids($this))->renew((int)$_GET['acc_id'],(int)$_GET['compaign_id'],); 
				?> 
					<?foreach ($bids as $bid):?>
					<tr   >	 
						<td  ><input type="checkbox" name="wids[]" value="<?=$bid['KeywordId']?>" ></td>
						<td  ><a href="?wid=<?=$bid['KeywordId']?>&w=<?=$bid['keyword']?>&acc_id=<?=$_GET['acc_id']?>&adgroups_id=<?=$_GET['adgroups_id']?>&compaign_id=<?=$_GET['compaign_id']?>"><?=$bid['keyword']?></a></td>
						<td  ><?=$bid['Bid']?></td>
						<td  >
							<table class="table" >
								<tr><td>Position</td><td>Bid</td><td>Price</td></tr>
							<?foreach ($bid['AuctionBids'] as $pos):?>
								<tr><td><?=$pos['Position']?></td><td> <?=$pos['Bid']/1000000?></td><td> <?=$pos['Price']/1000000?></td></tr>
							<?endforeach;?>
							</table>
						</td>
					</tr>
					<?endforeach;?> 
				<?endif;?>
				 
				</tbody>
                <tfoot>
                <tr>
					<th>Ключевая фраза</th>
					<th>Текущая ставка</th>
					<th>Ставки для позиций</th>
                </tr>
                </tfoot>
              </table>
            </div>
			</form>
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