<main>
<?php get_template_part('dashboard/part','messagesdiv'); ?>
</main>
<script>
    function refresh_messages() {
    	api('resend_messages',{
    		data: {},
    		callback: function(response){
    		    jQuery(jQuery('main')[0]).html(response);
    		}
    	});            
    }
    
    var intervalID = setInterval(refresh_messages, 10000);
</script>