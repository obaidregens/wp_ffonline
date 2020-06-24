<?php 
   // the query
   $the_query = new WP_Query( array(
     'post_type' => 'book',
      'posts_per_page' => 3,
   )); 
?>

<?php if ( $the_query->have_posts() ) : ?>
  <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
    <div class="book-block">
        <?php echo '<a href="' . get_permalink() . '">' . get_the_title() . "</a><br>" ;?>
        <?php echo get_the_excerpt() . "<br>"; ?>
		<?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
    </div>
  <?php endwhile; ?>
  <?php wp_reset_postdata(); ?>

<?php else : ?>
  <p><?php __('No Book'); ?></p>
<?php endif; ?>