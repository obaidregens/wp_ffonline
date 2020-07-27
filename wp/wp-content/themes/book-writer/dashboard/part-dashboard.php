<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	    <?php get_template_part('dashboard/part','dashboarddiv'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<script>
    function refresh_dashboard() {
    	api('resend_dashboard',{
    		data: {},
    		callback: function(response){
    		    jQuery(jQuery('main')[0]).html(response);
    		}
    	});            
    }
    
    var intervalID = setInterval(refresh_dashboard, 10000);
</script>