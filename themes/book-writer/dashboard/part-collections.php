<style>
.collections_list > li {
    color:var(--text-color) !important;
    padding-left:20px !important;
    border-radius:0%  !important;
}
.collection_modal.modal.open.focus-temp {
    box-shadow: 0 0 0pt 2pt var(--text-color);
    transform: scale(0.96) !important;
}
</style>
<main id="main" class="site-main" role="main">
<?php
$collections_ = collection::_present_dashboard();
?>
<span id="collections_data" style="display:none;"><?= json_encode($collections_) ?></span>
<ul class="collections_list collection">
</ul>
<div collection_id="" class="collection_modal modal">
    <div class="modal-content">
        <div class="row"></div>
        <div class="row">
            <div class="input-field col s12">
                <input name="title" data-length="50" type="text" class="validate" value="" required>
                <label for="title">Title</label>
                <span class="helper-text"></span>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
                <textarea name="description" data-length="400" class="materialize-textarea"></textarea>
                <label for="description">Description</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
                <select name="type" style="display:none;">
                    <option value="Public">Public</option>
                    <option value="Unlisted">Unlisted</option>
                    <option value="Private">Private</option>
                </select>
                <label>Privacy</label>
            </div>
        </div>
        <ul class="collection_books_list collection with-header">

        </ul>
    </div>
    <div class="modal-footer">
        <a class="waves-effect btn-flat save-collection action-delete">Delete</a>
        <a class="waves-effect btn-flat save-collection action-save">Save</a>
    </div>
</div>
<script>
function collections_load(){
    jQuery('input[data-length], textarea[data-length]').characterCounter();

    let collections_data = JSON.parse(document.getElementById('collections_data').innerHTML);
    const collections_list = jQuery('.collections_list')[0];
    const collection_modal = jQuery('.collection_modal')[0];
    function render_list(){
        let construct = '';
        const keys_ = Object.keys(collections_data);
        for (let i = 0; i <= keys_.length; i++) {
            const id = keys_.length == i ? 'new' : keys_[i];
            const collection = i == keys_.length ? {title: 'New Collection'} : collections_data[keys_[i]];
            construct += `<li collection_id="${id}" class="btn-hover collection-item avatar collection_li"><span class="title">${collection.title}`;
            if (i !== keys_.length){
                construct += ` <label>(${collection.type})</label></span><span class="right" style="padding-left:6px;"><a href="${collection.link}">View</a></span><p>${collection.description}</p>`;
            }
            construct += '</li>';
        }
        jQuery(collections_list).children().remove();
        jQuery(collections_list).append(construct);
    }
    function modal_dismiss(bl = false){
        let m = M.Modal.getInstance(collection_modal);
        m.options.dismissible = bl;
        let temp_save_warning_again;
        if (! bl){
            function closeCollectionModal(){
                jQuery(collection_modal).addClass('focus-temp');
                M.Toast.dismissAll()
                M.toast({html: 'Remember to save your changes if you want to keep them!'});
                const temp_collection_focus_interval = setInterval(function(){
                    jQuery(collection_modal).removeClass('focus-temp');
                    clearInterval(temp_collection_focus_interval);
                }, 50);
                modal_dismiss(true);
                temp_save_warning_again = setInterval(function(){
                    modal_dismiss(false);
                    clearInterval(temp_save_warning_again);
                },30000);
            }
            jQuery('.modal-overlay').on('click.nodismissone',closeCollectionModal);
            return;
        }
        jQuery('.modal-overlay').off('click.nodismissone');
        clearInterval(temp_save_warning_again);
    }
    jQuery(collection_modal).on('change','input, textarea, select',function(){
        modal_dismiss(false);
    });
    render_list();
    jQuery('.collections_list').on('click','.collection_li',function(){
        jQuery(collection_modal).modal('open');
        M.Toast.dismissAll();
        const collection_id = this.getAttribute('collection_id');
        const _collection = collections_data[collection_id];
        jQuery(collection_modal).find('[name="title"]').val(collection_id !== 'new' ? _collection.title : '');

        jQuery(collection_modal).find('[name="description"]').val(collection_id !== 'new' ? _collection.description : '');
        jQuery(collection_modal).find('[name="type"]').val(collection_id !== 'new' ? _collection.type : 'Public');
        M.updateTextFields();
        if (['Favorites','Hidden'].includes(collection_id !== 'new' ? _collection.title : 'New Collection')){
            jQuery(collection_modal).find('[name="title"]').prop('disabled',true);
            jQuery(collection_modal).find('[name="description"]').prop('disabled',true);
            jQuery(collection_modal).find('[name="type"]').prop('disabled',true);
        }
        else{
            jQuery(collection_modal).find('[name="title"]').prop('disabled',false);
            jQuery(collection_modal).find('[name="description"]').prop('disabled',false);
            jQuery(collection_modal).find('[name="type"]').prop('disabled',false);
        }
        jQuery(collection_modal).find('[name="type"]').formSelect();
        //Set Books
        let construct = '';
        const book_keys = collection_id !== 'new' ? Object.keys(_collection.books) : [];
        for (let i = 0; i < book_keys.length; i++) {
            let book_id = book_keys[i];
            let book = _collection.books[book_id];
            construct += `<li book_id="${book_id}" class="collection-item"><div><a class="book_title" href="${book.link}">${book.title}</a><a class="secondary-content"><i class="btn-favorite far fa-trash-alt delete-book"></i></a></div></li>`;
        }
        if (book_keys.length == 0){
            construct += '<li class="collection-item"><div><a>No Books</a></div></li>';
        }
        jQuery('.collection_books_list').children().remove();
        jQuery('.collection_books_list').append(construct);
        collection_modal.setAttribute('collection_id',collection_id);
        modal_dismiss(true);
    });
    const deleted_books = [];
    jQuery(collection_modal).on('click','.delete-book',function(){
        modal_dismiss(false);
        const li_elem = jQuery(this).parents('li[book_id]')[0];
        const title_elem = jQuery(li_elem).find('a.book_title')[0];
        const book_id = li_elem.getAttribute('book_id');
        const collection_id = collection_modal.getAttribute('collection_id');
        const book = collections_data[collection_id].books[book_id];

        const index = deleted_books.indexOf(book_id);
        if (index === -1){
            deleted_books.push(book_id);
            title_elem.removeAttribute('href');
            title_elem.innerText = 'Removed';
            this.innerText = 'UNDO';
            this.className = 'delete-book';
        }
        else{
            deleted_books.splice(index, 1);
            title_elem.setAttribute('href',book.link);
            title_elem.innerText = book.title;
            this.innerText = '';
            this.className = 'far fa-trash-alt btn-favorite delete-book';
        }
    });
    jQuery('.save-collection').click(function (){
        const action = jQuery(this).hasClass('action-save') ? 'save' : 'delete';
        const collection_id = collection_modal.getAttribute('collection_id');
        const books = collection_id !== 'new' ? array_diff(Object.keys(collections_data[collection_id].books),deleted_books) : [];
        var data_submit = {
            collection_id,
            action,
            title: jQuery(collection_modal).find('[name="title"]').val(),
            description: jQuery(collection_modal).find('[name="description"]').val(),
            type: jQuery(collection_modal).find('[name="type"]').val(),
            books
        };
        jQuery(".progress").css('display','block');
        jQuery.ajax({
            url: '/wp-content/themes/book-writer/dashboard/ajax/save_collection.php',
            type: 'post',
            data: data_submit,
            dataType: 'JSON',
            success: function(response){
                M.Toast.dismissAll();
                jQuery(".progress").css('display','none');
                if (response.code <= 5){
                    jQuery(collection_modal).modal('close');
                    collections_data = response.collection_data;
                    render_list();
                }
                let toast = '';
                response.code > 5 && (toast = 'An error occured.');
                response.code === 1 && (toast = 'Collection Updated.');
                response.code === 2 && (toast = 'Collection Created.');
                response.code === 3 && (toast = 'Collection Deleted.');
                response.code === 11 && (toast = 'Collection description is longer than 400 characters.');
                response.code === 12 && (toast = 'Collection title is required.');
                response.code === 13 && (toast = 'Collection title is longer than 50 characters.');
                (response.code === 1001 || response.code === 1002) && (toast = 'Title unavailable.');
                M.toast({html: toast});
            }
        });
    });
}
collections_load();
</script>
</main><!-- .site-main -->