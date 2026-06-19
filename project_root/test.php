<?php
$uri = '/WEB/project_root/public/index.php/ping';
preg_match('/public(?:\/index\.php)?\/?(.*)$/', $uri, $matches);
print_r($matches);
