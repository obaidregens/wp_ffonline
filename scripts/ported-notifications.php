<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$from_dir = MAIN_DIR . '/temp_notifications/';

if (!file_exists($from_dir)) {
    echo "Directory doesn't exist";
    exit();
}

$files = scandir($from_dir);

$inst = new notifications_insert;
$dots = ['.','..'];
$c = 0;
foreach ($files as $file) {
    if (in_array($file,$dots)) {
        continue;
    }
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext !== "jsonl") {
        continue;
    }
    $fn = $from_dir . $file;
    $rows = file($fn);
    foreach ($rows as $row) {
        $chapter_id = json_decode($row,true)['chapter_id'];
        $inst->updateStory($chapter_id);
        echo "Added notifications for Chapter ID: $chapter_id \n";
        $c++;
    }
    unlink($fn);
}
echo "$c notifications added\n";