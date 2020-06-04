<?php
get_header();
if (is_author()){
	get_template_part( 'author/header' );	
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <section class="error-404 not-found">
            <div class="page-header">
                <h1 class="page-title">Oops! No collection found.</h1>
            </div>
        </section>
    </main>
</div>

<?php
get_footer();
exit();