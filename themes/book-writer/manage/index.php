<?php
/* Template Name: Dashboard */ 
admin_only();
global $bundle;
$bundle = global_bundle('manage');
$bundle->js('js/manage');
$bundle->enqueue();
get_header();

//Query Database
$stats = _landing::query(array(
    'unique'        => 'vfs',
    'actions'       => array(
        'from'      => 1
    ),
    'order'         => 'DESC',
    'orderby'       => 'ID'
));
?>
<style>
    /* Timeline Start */
    .container{
        width: 90% !important;
    }
    .timeline {
    position: relative;
    }

    .timeline .timeline-event {
    position: relative;
    padding-top: 5px;
    padding-bottom: 5px;
    }

    .timeline .timeline-event .timeline-content {
    position: relative;
    width: calc(50% - 50px);
    }

    .timeline-content{
        padding:0 !important;
    }

    .timeline .timeline-event::before {
    display: block;
    content: "";
    width: 2px;
    height: calc(50% - 30px);
    position: absolute;
    background: #d2d2d2;
    left: calc(50% - 1px);
    top: 0;
    }

    .timeline .timeline-event::after {
    display: block;
    content: "";
    width: 2px;
    height: calc(50% - 30px);
    position: absolute;
    background: #d2d2d2;
    left: calc(50% - 1px);
    top: calc(50% + 30px);
    }

    .timeline .timeline-event:first-child::before {
    display: none;
    }

    .timeline .timeline-event:last-child::after {
    display: none;
    }

    .timeline .timeline-event:nth-child(even) .timeline-content {
    margin-left: calc(50% + 50px);
    }

    .timeline .timeline-event:nth-child(odd) .timeline-content {
    margin-left: 0;
    }

    .timeline .timeline-badge {
    display: block;
    position: absolute;
    width: 40px;
    height: 40px;
    background: #d2d2d2;
    top: calc(50% - 20px);
    right: calc(50% - 20px);
    border-radius: 50%;
    text-align: center;
    cursor: default;
    }

    .timeline .timeline-badge i {
    font-size: 25px;
    line-height: 40px;
    }

    @media (max-width: 600px) {
    .timeline .timeline-event .timeline-content {
        width: calc(100% - 70px);
    }
    .timeline .timeline-event::before {
        left: 19px;
    }
    .timeline .timeline-event::after {
        left: 19px;
    }
    .timeline .timeline-event:nth-child(even) .timeline-content {
        margin-left: 70px;
    }
    .timeline .timeline-event:nth-child(odd) .timeline-content {
        margin-left: 70px;
    }
    .timeline .timeline-badge {
        left: 0;
    }
    }
    /* Timeline End */
    main,header#masthead{
        padding-left:210px;
    }
    .user_info{
        transform-origin: top;
        transform: scale(0.7);
    }
    .sidebar-left{
        width: 210px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        z-index: 999;
        padding-top: 50px;
        background-color: var(--background-accent);
    }
    .sidebar-left li{
        display:block;
        padding: 15px 20px;
        color: var(--text-color);
    }
    json_data{
        display:none;
    }
</style>
<json_data><?= json_encode($stats); ?></json_data>

<div class="sidebar-left">
    <li class="btn-hover active">
        Users
    </li>
</div>
<main>
<table class="responsive-table highlight">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Most Recent Visit</th>
            <th>Most Recent IP</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
</main>
<div id="track_user" class="modal modal-large">
    <div class="modal-content">
        <h3 class="row">Track User</h3>
        <div class="container">
            <div class="user_info timeline"></div>
        </div>

    </div>
</div>
<?php
get_footer( );
