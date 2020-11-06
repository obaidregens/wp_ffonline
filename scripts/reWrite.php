<?php
$start = time();
set_time_limit(0);
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

bundle::reWrite();

echo "Script Ended in: " . (time() - $start) . "seconds \n";