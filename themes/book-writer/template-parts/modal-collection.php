<!-- Modal Structure -->
<?php
$book_ids = array_column($wp_query->posts,'ID');
$book_collections = collection::query_by_book($book_ids,'ID');
$collections = collection::query(array(
    'authors'  => array(get_current_user_id()),
));
echo '<span id="book_collections" style="display:none;">' . json_encode($book_collections) . '</span>';
?>
<div class="collections-modal modal bottom-sheet">
    <div class="modal-content">
    <table><tbody>
        <?php foreach($collections as $collection) { ?>
            <tr>
                <td><?= $collection['title'] ?> <label>(<?= $collection['type'] ?>)</label></td>
                <td class="right-align">
                    <div class="switch"><label>
                        <input class="save-collection" collection_id="<?php echo $collection['ID']; ?>" type="checkbox">
                        <span class="lever"></span>
                    </label></div>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td><a href="/dashboard/collections">Create Collection</a></td>
            <td></td>
        </tr>            
    </tbody></table>
    </div>
</div>
