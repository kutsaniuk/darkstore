<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
?>
<div class="row">
        <div class="col-xs-12">
          <div class="box">
				<? $graph_model->print_graphes(); ?>
		  </div>
		</div>
</div>

<?include('footer.php');?>
</body>
</html>