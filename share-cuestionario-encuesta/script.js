(function($){

	$(document).on('click','.submitdelete',function(e){
	 	e.preventDefault();
	 	link = $(this);
        valorinicio= $('#valorenvio').val()
	 	id   = link.attr('href').replace(/^.*#more-/,'');

		$.ajax({
			url : 'http://localhost/wordpress/wp-admin/admin-ajax.php',
			type: 'post',
			data: {
				action : 'dcms_ajax_readmore',
				id_post: id,
                estado: valorinicio
			},
			
			success: function(resultado){
				 $('#post-'+id).find('.entry-content').html(resultado);		
			}

		});

	});
})(jQuery);