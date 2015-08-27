<?php
error_reporting(2147483647);

require_once '/home/sin-kaisha/seturitu-kun.com/lib_admin_tools/AdminToolsDispatcher.php';
$result = AdminToolsDispatcher::run('CompanyCustomerInfoWriter');

echo '--------------------------------------------------------------------------' . "<br />\n";
echo $result . "<br />\n";
echo '--------------------------------------------------------------------------' . "<br />\n";