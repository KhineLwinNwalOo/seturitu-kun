<?php
/*
 * Create Date: 2008/11/05
 *
 */
include_once("../common/include.ini");


echo "\n<br />PHP_INT_SIZE='".PHP_INT_SIZE."'";

$large_number =  2147483647;
var_dump($large_number);
// ����: int(2147483647)
echo "\n<br />\$large_number='".$large_number."'";

$large_number =  2147483648999;
var_dump($large_number);
// ����: float(2147483648)

echo "\n<br />\$large_number='".$large_number."'";

for ($i = 0; $i < 2010; $i++) {
	echo "\n<br />����".$i." = "._ConvertAD2Jp($i)."";
}
?>
