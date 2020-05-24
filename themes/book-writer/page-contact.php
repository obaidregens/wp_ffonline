<?php
/**
 * Template Name: Contact
 *
 * Contact form
 *
 */
global $js_bundle;
$js_bundle = global_bundle('contact');
$js_bundle->add('contact');
$js_bundle->enqueue();
?>
<?php get_header(); ?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	    <div class="mobile-margin"><p style="font-size:12px;color:grey;">If you have any issues, need help in some way, or maybe you can help us? Whatever it is, just shoot us a message through the form below. We're waiting for it.</p></div>
		<form name="message" class="col s12 mobile-margin">
			<div class="row">
				<p>
				  <label>
					<input name="contact_options" type="radio" value="Book Migration"/>
					<span>I want to migrate my book from another site.</span>
				  </label>
				</p>
				<p>
				  <label>
					<input name="contact_options" type="radio" value="Issue"/>
					<span>I have an issue/need help.</span>
				  </label>
				</p>
				<p>
				  <label>
					<input name="contact_options" type="radio" value="Feature" />
					<span>I want to suggest a feature.</span>
				  </label>
				</p>
				<p>
				  <label>
					<input name="contact_options" type="radio" value="Bug"/>
					<span>I want to report a bug.</span>
				  </label>
				</p>
				<p>
				  <label>
					<input name="contact_options" type="radio" value="Other"/>
					<span>Other</span>
				  </label>
				</p>
			</div>			
			<div class="row">
				<div class="input-field col s12">
					  <input name="email" id="email" type="email" class="validate" required value="<?php echo get_the_author_meta('user_email',get_current_user_id()); ?>">
					  <label for="email">Email</label>
					  <span id="email_help" class="helper-text"></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<textarea name="message" id="message" required value="" class="validate materialize-textarea"></textarea>
					<label for="message">Anything?</label>
				</div>
			</div>
			<button class="waves-effect waves-light btn-small right" id="message_btn" onclick="validateoption()" type="submit">Send</button>
		</form>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>