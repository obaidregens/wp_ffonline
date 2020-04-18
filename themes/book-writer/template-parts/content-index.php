<?php
$chapters = get_chapters_query();
$count = 0;
?>
  <table class="highlight">
    <thead>
      <tr>
          <th style="width:7%">#</th>
          <th style="width:75%">Chapter</th>
          <th style="width:10%">Words</th>
          <th style="width:8%">Reviews</th>
      </tr>
    </thead>
    <tbody>
  <?php
while ( $chapters->have_posts() ) :
	$count ++;
	$chapters->the_post();
	?>
      <tr style="cursor:pointer;" onclick="window.location = '<?php echo get_permalink($post->ID); ?>'">
        <td><?php echo $count; ?></td>
        <td><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></td>
        <td><?php echo get_post_meta($post->ID,'word-count',true); ?></td>
        <td><?php echo get_comments_number($post->ID); ?></td>
      </tr>
    <?php
	// End the loop.
	endwhile;
wp_reset_query();
?>
        </tbody>
      </table>