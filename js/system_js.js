	function ajax(file,post,div)
    {
       $.post('/ajax/'+file,
					post , 
				function(data, textStatus){ 
					$(div).html(data);  
				},
					"html" // "xml", "script", "json", "jsonp", "text"
			);
    }
	
	function ajax_post(file,form_id,div)
    {
		$('select').removeAttr('disabled');
		
       $.ajax({
				   type: "POST",
				   url: '/ajax/'+file,
				    cache:false,
					contentType: false,
					processData: false,
				   data: new FormData(form_id), // serializes the form's elements.
				   success: function(data)
				   {
					    $(div).html(data);  
				   }
		});
    }
	
	