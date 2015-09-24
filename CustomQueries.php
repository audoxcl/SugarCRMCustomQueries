<?php

/********************************************************************************
 * Copyright (C) 2015 Audox Ingeniería Ltda.
 * All Rights Reserved.
 ********************************************************************************/

if(!defined('sugarEntry')) define('sugarEntry', true);

$customQueriesVersion = 1.5;
global $db;
global $sugar_config, $app_list_strings, $GLOBALS;
global $current_user;

$SugarQueriesApiKey = $sugar_config['CustomQueries']['ApiKey'];
$url="http://www.sugarqueries.com/validate.php";
$fields = array(
	'Remote' => $_SERVER['REMOTE_ADDR'],
	'Url' => $sugar_config['site_url'],
	'ApiKey' => $SugarQueriesApiKey,
);
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, true); 
curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($curl));
if($response->licence == 0) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'No Valid License')));

if($_REQUEST['entryPoint']==='CustomQueriesRemote'){
	$user_hash="encrypt(lower(md5('".$_SERVER['PHP_AUTH_PW']."')),user_hash)";
	$query="SELECT * FROM users WHERE user_name='".$_SERVER['PHP_AUTH_USER']."' AND user_hash=".$user_hash." AND is_admin = 1 AND status = 'Active' AND !deleted";
	$res=$db->query($query, true, 'Error: '.mysql_error());
	if($res->num_rows==0) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'Unauthorized User')));
	if(!isset($_REQUEST['queries'])) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'The query is empty')));
	if(!isset($_REQUEST['format'])) $_REQUEST['format'] = 'json';
	$current_user->is_admin=1;
}

if(isset($_REQUEST['array'])){
	switch ($_REQUEST['array']) {
		case "sugar_config":
			echo json_encode($sugar_config);
			break;
		case "GLOBALS":
			echo json_encode($GLOBALS);
			break;
		case "app_list_strings":
			echo json_encode($app_list_strings);
			break;
	}
	return;
}

if(isset($_REQUEST['query'])) $_REQUEST['queries'] = $sugar_config['CustomQueries'][$_REQUEST['query']];

if($_REQUEST['entryPoint']==='CustomQueries'){
	echo '<script type="text/javascript" src="http://www.kunalbabre.com/projects/jquery-1.3.2.js" ></script>';
	echo '<script type="text/javascript" src="http://www.kunalbabre.com/projects/table2CSV.js" ></script>';
	echo "<script>
function getCSVData(table){
 var csv_value=$('#'+table).table2CSV({delivery:'value', separator : ';'});
 $(\"#csv_text_\"+table).val(csv_value);	
}
</script>";
	echo '
	<form name="input" action="" method="post">
	<table>
	<tr><td>Queries:<br /><textarea rows="4" cols="50" name="queries">'.trim(htmlspecialchars_decode(isset($_REQUEST['queries'])?$_REQUEST['queries']:"", ENT_QUOTES)).'</textarea></td></tr>
	</table>
	<input type="submit" value="Submit">
	</form>
	';
}

if(isset($_REQUEST['queries']) && $current_user->is_admin){
	$html_result="";
	$array_result=array();
	$array_result['version'] = $customQueriesVersion; 
	$array_result['error'] = 0;
	$queries=trim(htmlspecialchars_decode($_REQUEST['queries'], ENT_QUOTES));
	$queries = rtrim($queries, ';');
	$queries = explode(";", $queries);
	$query_id=0;
	foreach ($queries as $query) {
		$query = trim($query);
		$html_result.="Query: ".$query."<br />";
		$array_result['results'][$query_id]['query']=$query;
		$res=$db->query($query, true, 'Error buscando Reservas: ');
		$html_result.="<table id=\"table_".$query_id."\" border=\"1\" cellspacing=0 cellpadding=0>";
		$header_style="style=\"background-color:black; color:white;\"";
		$i=0;
		while($row=$db->fetchByAssoc($res)){
			if($i==0){
				$html_result.="<tr><td ".$header_style.">#</td>";
				foreach ($row as $field => $value){
					$html_result.="<td ".$header_style.">".$field."</td>";
					$array_result['results'][$query_id]['header'][]=$field;
					}
				$html_result.="</tr>";
			}
			$html_result.="<tr><td>".$i."</td>";
			foreach ($row as $field => $value){
				$value = str_replace("\r\n", "", $value);
				$html_result.="<td>".$value."</td>";
				}
			$html_result.="</tr>";
			$array_result['results'][$query_id]['rows'][$i]=$row;
			$i++;
		}
		$html_result.="</table>";
		if($_REQUEST['entryPoint']==='CustomQueries'){
			$html_result.="<input value=\"View CSV\" type=\"button\" onclick=\"$('#table_".$query_id."').table2CSV()\">";
			$html_result.='<form id ="get_csv_form_table_'.$query_id.'" action="index.php?entryPoint=getCSV" method ="post" > 
<input type="hidden" id="csv_text_table_'.$query_id.'" name="csv_text">
<input type="submit" id="submit_'.$query_id.'" value="Download CSV File" onclick="getCSVData(\'table_'.$query_id.'\')">
</form>';
		}
		$html_result.="<br />";
		$query_id++;
	}
	if($_REQUEST['format']==='array') echo print_r($array_result);
	elseif($_REQUEST['format']==='json') echo json_encode($array_result);
	else echo $html_result;
}

?>