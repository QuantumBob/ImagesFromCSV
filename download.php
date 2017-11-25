<?php

$ini_val = ini_get('upload_tmp_dir');
$temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
$table_name = $_POST['table_name'];
$file_url = $temp_path . '/' . $table_name . '.csv';
$size = filesize($file_url);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="alterego_test.csv"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_url));
ob_clean();
flush();
readfile($file_url);
exit(0);

