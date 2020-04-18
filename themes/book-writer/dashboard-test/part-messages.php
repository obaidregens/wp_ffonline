<main>
<?php get_template_part('dashboard/part','messagesdiv'); ?>
</main>
<script>
    function refresh_messages() {
    	jQuery.ajax({
    		url: '/wp-content/themes/book-writer/dashboard/ajax/resend_messages.php',
    		type: 'post',
    		data: {ajax: 1},
    		success: function(response){
    		    jQuery(jQuery('main')[0]).html(response);
    		}
    	});            
    }
    
    var intervalID = setInterval(refresh_messages, 10000);
</script>