<main id="main" class="site-main" role="main">
<?php
$collections = get_terms(array(
    'meta_key' => 'author',
    'meta_value' => get_current_user_id(),
    'taxonomy' => 'collection',
    'hide_empty' => false,
));
?>
<ul class="collection">
	<li onclick="M.Modal.getInstance(document.getElementById('modal-favorites')).open();" style="color:var(--text-color) !important;padding-left:20px;border-radius:0%;" class="btn-hover collection-item avatar">
		<span class="title"><?php echo 'Favorites <label>(Private)</label>'; ?></span><span class="right" style="padding-left:6px;"></span>
	</li>

	<!-- Modal Structure -->
	<div id="modal-favorites" class="modal">
		<div class="modal-content">
			<h4 class="row valign-wrapper"><span class="col s10">Favorites</span><span class="col s2"><a onclick="notifications_toggle('favorites')" class="right btn-hover btn-floating"><i style="font-size:1.3rem;" class="notification-icon fas <?php if (get_user_meta(get_current_user_id(),'fav_notify',true) == 'notify' || get_user_meta(get_current_user_id(),'fav_notify',true) === ''){echo 'fa-bell';}else{echo 'fa-bell-slash';} ?>"></i></a></span></h4>
			<?php
			$favs = get_stats_of('user_fav',get_current_user_id());
			?>
			<ul class="collection with-header">
				<?php
				foreach($favs as $fav){
				$book = get_post($fav);
				?><li id="<?php echo 'favorites_' . $book->ID; ?>" class="collection-item"><div><a href="<?php echo get_permalink($book->ID); ?>"><?php echo $book->post_title; ?></a><a class="secondary-content"><i onclick="delete_book('<?php echo 'favorites_' . $book->ID; ?>')" class="btn-favorite far fa-trash-alt"></i></a></div></li><?php
				}
				if (empty($favs)){
					?><li class="collection-item"><div><a>No Books</a></div></li><?php
				}
				?>
			</ul>
		</div>
		<div class="modal-footer">
			<a onclick="save_it('favorites');" class="waves-effect btn-flat">Save</a>
		</div>
	</div>
	<li onclick="M.Modal.getInstance(document.getElementById('modal-hidden')).open();" style="color:var(--text-color) !important;padding-left:20px;border-radius:0%;" class="btn-hover collection-item avatar">
		<span class="title"><?php echo 'Hidden <label>(Private)</label>'; ?></span><span class="right" style="padding-left:6px;"></span>
	</li>

	<!-- Modal Structure -->
	<div id="modal-hidden" class="modal">
		<div class="modal-content">
		    <h4 class="row valign-wrapper"><span class="col s10">Hidden</span><span class="col s2"><a onclick="notifications_toggle('hidden')" class="right btn-hover btn-floating"><i style="font-size:1.3rem;" class="notification-icon fas <?php if (get_user_meta(get_current_user_id(),'hidden_notify',true) == 'notify' || get_user_meta(get_current_user_id(),'hidden_notify',true) === ''){echo 'fa-bell';}else{echo 'fa-bell-slash';} ?>"></i></a></span></h4>
			<?php
			$hidden = get_stats_of('user_hidden',get_current_user_id());
			?>
			<ul class="collection with-header">
				<?php
				foreach($hidden as $id){
				$book = get_post($id);
				?><li id="<?php echo 'hidden_' . $book->ID; ?>" class="collection-item"><div><a href="<?php echo get_permalink($book->ID); ?>"><?php echo $book->post_title; ?></a><a class="secondary-content"><i onclick="delete_book('<?php echo 'hidden_' . $book->ID; ?>')" class="btn-favorite far fa-trash-alt"></i></a></div></li><?php
				}
				if (empty($hidden)){
					?><li class="collection-item"><div><a>No Books</a></div></li><?php
				}
				?>
			</ul>
		</div>
		<div class="modal-footer">
			<a onclick="save_it('hidden');" class="waves-effect btn-flat">Save</a>
		</div>
	</div>
	<?php

	foreach($collections as $collection){
    ?>
    <li onclick="M.Modal.getInstance(document.getElementById('modal-<?php echo $collection->term_id; ?>')).open();" style="color:var(--text-color) !important;padding-left:20px;border-radius:0%;" class="btn-hover collection-item avatar">
        <span class="title"><?php echo $collection->name . ' <label>(' . get_term_meta($collection->term_id,'public_collection',true) . ')</label>'; ?></span><span class="right" style="padding-left:6px;"><a href="<?php echo get_term_link($collection->term_id); ?>">View</a></span>
        <p><?php echo $collection->description; ?></p>
    </li>

  <!-- Modal Structure -->
  <div id="modal-<?php echo $collection->term_id; ?>" class="modal">
    <div class="modal-content">
      <div class="row">
        <div class="input-field col s10">
          <input name="name" type="text" class="validate" value="<?php echo $collection->name; ?>" required>
          <label for="name">Name</label>
          <span class="helper-text" data-error="Enter a name for your collection."></span>
        </div>
        <div class="input-field col s2">
            <a onclick="notifications_toggle(<?php echo $collection->term_id; ?>)" class="right btn-hover btn-floating"><i style="font-size:1.3rem;" class="notification-icon fas <?php if (in_array(get_current_user_id(),get_stats_of('collection_follow',$collection->term_id))){echo 'fa-bell';}else{echo 'fa-bell-slash';} ?>"></i></a>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <textarea name="description" class="materialize-textarea"><?php echo $collection->description; ?></textarea>
          <label for="description">Description</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
            <div class="switch">
            <label>
            Private
            <input <?php if(get_term_meta($collection->term_id,'public_collection',true) == 'Public'){echo 'checked';} ?> name="public_switch" type="checkbox">
            <span class="lever"></span>
            Public
            </label>
            </div>
        </div>
      </div>
    <?php
        $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
            'taxonomy' => 'collection',
            'field' => 'term_id',
            'terms' => $collection->term_id
             )
          )
        );
        $query = new WP_Query( $args );
        ?>
        <ul class="collection with-header">
            <?php
            foreach($query->posts as $book){
                ?><li id="<?php echo $collection->term_id . '_' . $book->ID; ?>" class="collection-item"><div><a href="<?php echo get_permalink($book->ID); ?>"><?php echo $book->post_title; ?></a><a class="secondary-content"><i onclick="delete_book('<?php echo $collection->term_id . '_' . $book->ID; ?>')" class="btn-favorite far fa-trash-alt"></i></a></div></li><?php
            }
            if (empty($query->posts)){
                ?><li class="collection-item"><div><a>No Books</a></div></li><?php
            }
            ?>
        </ul>
    </div>
    <div class="modal-footer">
      <a onclick="delete_collection('<?php echo $collection->term_id; ?>');" class="waves-effect btn-flat">Delete</a>
      <a onclick="save_it('<?php echo $collection->term_id; ?>');" class="waves-effect btn-flat">Save</a>
    </div>
  </div>
    <?php
}
?>
    <li onclick="M.Modal.getInstance(document.getElementById('modal-new')).open();" style="color:var(--text-color) !important;padding-left:20px;border-radius:0%;" class="btn-hover collection-item avatar">
        <span class="title">New Collection</span>
    </li>

  <!-- Modal Structure -->
  <div id="modal-new" class="modal">
    <div class="modal-content">
      <div class="row">
        <div class="input-field col s12">
          <input name="name" type="text" class="validate" required>
          <label for="name">Name</label>
          <span class="helper-text" data-error="Enter a name for your collection."></span>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <textarea name="description" class="materialize-textarea"></textarea>
          <label for="description">Description</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
            <div class="switch">
            <label>
            Private
            <input checked name="public_switch" type="checkbox">
            <span class="lever"></span>
            Public
            </label>
            </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <a onclick="save_it('new');" class="waves-effect btn-flat">Save</a>
    </div>
  </div>

</ul>
<script>
  var deleted_books = [];
  var titles = [];
  jQuery(document).ready(function(){
    jQuery('.modal').modal();
  });
  function delete_book(collection_book){
      if (deleted_books.indexOf(collection_book) == -1){
          titles.push(jQuery('#' + collection_book + ' a')[0]);
          deleted_books.push(collection_book);
          document.getElementById(collection_book).innerHTML = '<div><a style="color:var(--text-color);">Removed</a><a onclick="delete_book(\'' + collection_book + '\')" class="secondary-content">UNDO</a></div>';
      }
      else{
          var index = deleted_books.indexOf(collection_book);
          document.getElementById(collection_book).innerHTML = '<div><a href="' + titles[index].getAttribute('href') + '">' + titles[index].innerHTML + '</a><a class="secondary-content"><i onclick="delete_book(\'' + collection_book + '\')" class="btn-favorite far fa-trash-alt"></i></a></div>';
          deleted_books.splice(index, 1);
          titles.splice(index, 1);
      }
  }
    function delete_collection(collection){
        var confirm = window.confirm("Are you sure you want to delete this collection? You won't be able to recover it later.");
        if (confirm == false){
            return;
        }
        jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    	jQuery.ajax({
    		url: '/wp-content/themes/book-writer/dashboard/ajax/save_collection.php',
    		type: 'post',
    		data: {ajax: 1,collection:collection,to_delete:'delete'},
    		success: function(response){
                M.Toast.dismissAll();
    		    if (response == 1){
    		        M.toast({html: 'Collection Deleted.'});
    		    }
    		    else{
    		        M.toast({html: 'An error occured.'});
    		    }
    			load_page('collections');
    		}
    	});
    }
    function save_it(collection){
        var collection_deleted_books = [];
        for (i = 0; i < deleted_books.length; i++) {
            if (deleted_books[i].split('_')[0] == collection){
                collection_deleted_books.push(deleted_books[i].split('_')[1]);
            }
        }
		if (collection != 'favorites' && collection != 'hidden'){
			var name = jQuery('#modal-' + collection + ' [name=name]')[0].value;
			if (name == ''){
				jQuery('#modal-' + collection).animate({
					scrollTop: jQuery('#modal-' + collection + ' [name=name]').offset().top
				}, 500);
			}
			var description = jQuery('#modal-' + collection + ' [name=description]')[0].value;
			if (jQuery('#modal-' + collection + ' [name=public_switch]')[0].checked == true){
				var public_switch = 'Public';
			}
			else{
				var public_switch = 'Private';
			}
			var data = {ajax: 1,collection:collection,deleted_books:collection_deleted_books,name:name,description:description,public_switch:public_switch};
		}
		else{
			var data = {ajax: 1,collection:collection,deleted_books:collection_deleted_books};
		}
        jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    	jQuery.ajax({
    		url: '/wp-content/themes/book-writer/dashboard/ajax/save_collection.php',
    		type: 'post',
    		data: data,
    		success: function(response){
                M.Toast.dismissAll();
    		    if (response == 1){
    		        M.toast({html: 'Collection Updated.'});
    		    }
    		    else if (response == 2){
    		        M.toast({html: 'Collection Created.'});
    		    }
    		    else if (response == 0){
    		        M.toast({html: 'Collection name is unavailable.'});
    		    }
    		    else{
    		        M.toast({html: 'An error occured.'});
    		    }
    			load_page('collections');
    		}
    	});
    }
    function notifications_toggle(collection){
		var data = {ajax: 1,collection:collection};
        //jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    	jQuery.ajax({
    		url: '/wp-content/themes/book-writer/php/collections_notifications.php',
    		type: 'post',
    		data: data,
    		success: function(response){
                M.Toast.dismissAll();
    		    if (response == 1){
    		        M.toast({html: "You'll receive notifications for this collection."});
    		        jQuery('#modal-' + collection + ' .notification-icon').removeClass('fa-bell');
    		        jQuery('#modal-' + collection + ' .notification-icon').removeClass('fa-bell-slash');
    		        jQuery('#modal-' + collection + ' .notification-icon').addClass('fa-bell');
    		    }
    		    else if (response == 0){
    		        M.toast({html: "You won't receive notifications for this collection from now."});
    		        jQuery('#modal-' + collection + ' .notification-icon').removeClass('fa-bell');
    		        jQuery('#modal-' + collection + ' .notification-icon').removeClass('fa-bell-slash');
    		        jQuery('#modal-' + collection + ' .notification-icon').addClass('fa-bell-slash');
    		    }
    		    else if (response == 3){
    		        M.toast({html: 'Please login.'});
    		    }
    		    else{
    		        M.toast({html: 'An error occured.'});
    		    }
    		}
    	});
    }
</script>
</main><!-- .site-main -->