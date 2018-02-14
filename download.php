<?php

$ini_val = ini_get('upload_tmp_dir');
$temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
if (!isset($_SESSION)) {
        session_start();
}
$table_name = $_SESSION['table_name'];
$file_url = $temp_path . '/' . $table_name . '.csv';
$size = filesize($file_url);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="woo_ready_' . $table_name . '.csv"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $size);
ob_clean();
flush();
readfile($file_url);
exit(0);

