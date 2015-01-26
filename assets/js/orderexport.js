jQuery(document).ready(function() {

	jQuery('.wpg-datepicker').datepicker({
			dateFormat : 'yy-mm-dd'
		}
	);

	/**
	 * Export csv ajax
	 */
	jQuery('.wpg-order-export').on('click', function(e){

		e.preventDefault();
		var start_date = jQuery('#wpg-start-date').val();
		var end_date = jQuery('#wpg-end-date').val();

		if( jQuery.trim(start_date) === '' || jQuery.trim(end_date) === '' )
			return;

		var data = {
			action : 'wpg_order_export',
			start_date: start_date,
			end_date : end_date
		};

		jQuery.post( ajaxurl, data, function(response){
			
			response = jQuery.parseJSON(response);
			if( response.error === false ) {
				window.location = window.location.href+'&oe=1';
			}else{
				jQuery('.wpg-response-msg').html( response.msg ).addClass('wpg-error');
			}
			
		});
	});
	
});