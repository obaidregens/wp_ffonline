<div class="row">
    <div class="col s12">
        <div class="card-panel">
            <div class="section" style="color:var(--mid-theme-color);"><h3>New Messages</h3></div>
            <div class="row">
                <div class="col s12 m12">
                    <div class="collection">
                        <?php
                        $users = chats::with('new-only');
                        foreach($users as $user){ ?>
                            <a onclick="load_page('chat/<?= $user->username; ?>')" class="collection-item">
                                <?= $user->name; ?>
                                <span data-badge-caption="New Messages" class="new badge red"><?= $user->unread; ?></span>
                            </a>
                        <?php }
                        if (empty($users)){
                            ?><div class="collection-item">No New Messages.</div><?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>