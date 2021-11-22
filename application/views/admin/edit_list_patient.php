<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include('header.php');
include('edit_patient_inner.php');
?>
  <script>
  $(function () {

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
		
    })
</script>
<?
include('footer.php');?>
