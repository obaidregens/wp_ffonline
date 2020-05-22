<!-- Modal Structure -->
<?php
$collection_data = collection_data(array_column($wp_query->posts,'ID'));
echo '<span id="book_collections" style="display:none;">' . json_encode($collection_data['book_collections']) . '</span>';
?>
<div class="collections-modal modal bottom-sheet">
    <div class="modal-content">
    <table><tbody>
        <?php foreach($collection_data['collections'] as $collection_id => $collection) { ?>
            <tr>
                <td><?= $collection['name'] ?> <label>(<?= $collection['type'] ?>)</label></td>
                <td class="right-align">
                    <div class="switch"><label>
                        <input 
                        <?php if ($collection_id == 'favorites') { ?>
                        class="book-favorite"
                        <?php } 
                            else if ($collection_id == 'hidden') { ?>
                        class="book-hide"
                        <?php }
                            else { ?>
                        class="save-collection"
                        <?php } ?>
                         collection_id="<?php echo $collection_id; ?>" type="checkbox">
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
