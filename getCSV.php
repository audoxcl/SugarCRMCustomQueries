<?php
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"report.csv\"");
$data=htmlspecialchars_decode(stripcslashes($_REQUEST['csv_text']));
echo $data; 
?>