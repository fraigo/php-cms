<?php

require_once("../lib/index.php");


$route=cms_route();
$params=cms_params();

echo cms_view($route,$params);





