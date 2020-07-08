<?php
global $bundle;
if (! isset($bundle)){
	$bundle = global_bundle('global');
	$bundle->enqueue();
}
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>
		</div><!-- .site-content -->
	</div><!-- .site-inner -->

</div><!-- .site -->


<?php wp_footer(); ?>

<!-- Fonts -->
<!-- Font Awesome -->
<link rel="stylesheet" id="font-awesome-official-css" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" type="text/css" media="all" integrity="sha384-KA6wR/X5RY4zFAHpv/CnoG2UW1uogYfdnP67Uv7eULvTveboZJg0qUpmJZb5VqzN" crossorigin="anonymous">
<!--Import Google Icon Font-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	
<!-- Google Tools -->
<!-- reCAPTCHA -->
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script>
	function recaptchaOnload(){
		if (document.getElementById('reCAPTCHA_div')){
			grecaptcha.render("reCAPTCHA_div", {
				sitekey: '6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth',
			});
		}
	}
</script>
<!--Fixed Elements -->
<!--Progress Bar -->
<div class="progress" style="display:none;margin:0;position: fixed;bottom: 0px;right: 0px;left: 0px;"><div class="indeterminate"></div></div>
</body>
</html>
