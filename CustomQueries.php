<?php

/*********************************************************************************
* This code was developed by:
* Audox Ingenier�a Ltda.
* You can contact us at:
* Web: www.audox.cl
* Email: info@audox.cl
* Skype: audox.ingenieria
********************************************************************************/

if(!defined('sugarEntry')) define('sugarEntry', true);

$customQueriesVersion = 1.8;

global $db;
global $sugar_config, $app_list_strings, $GLOBALS;
global $current_user;

$options = array(
	"show_queries" => true,
);

// Validate $SugarQueriesApiKey to enable the use of this module
// Feel free to disable it or edit it and validate the use of this module against other criteria for your own purposes
function validate($url, $fields){
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_POST, true); 
	curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$response = json_decode(curl_exec($curl));
	return $response->licence;
}

$SugarQueriesApiKey = $sugar_config['CustomQueries']['ApiKey'];
$url="https://www.sugarqueries.com/validate.php";
$fields = array(
	'Remote' => $_SERVER['REMOTE_ADDR'],
	'Url' => $sugar_config['site_url'],
	'ApiKey' => $SugarQueriesApiKey,
);

if(validate($url, $fields) == 0) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'No Valid License')));

if($_REQUEST['entryPoint']==='CustomQueriesRemote'){
	$username = (isset($_SERVER['PHP_AUTH_USER']))?$_SERVER['PHP_AUTH_USER']:$_REQUEST['username'];
	$password = (isset($_SERVER['PHP_AUTH_PW']))?$_SERVER['PHP_AUTH_PW']:$_REQUEST['password'];
	$user = new User();
	if(is_null($user->retrieve_by_string_fields(array('user_name' => $username)))) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'Non existing User: '.$username)));
	if($user->checkPassword($password, $user->user_hash)== false) die(json_encode(array('version' => $customQueriesVersion, 'error' => 1, 'msg' => 'Unauthorized User ('.$username.'/'.$password.')')));
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

$javascript = <<<EOQ
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js" ></script>
<script type="text/javascript" src="custom/include/javascript/table2CSV.js" ></script>
<script>
function getCSVData(table){
	var csv_value=$('#'+table).table2CSV({delivery:'value', separator : ','});
	$("#csv_text_"+table).val(csv_value);
}
</script>
EOQ;

if($_REQUEST['entryPoint']==='CustomQueries'){
	echo $javascript;
	echo '
	<form name="input" action="" method="post">
	<table>
	<tr><td>Queries:<br /><textarea rows="4" cols="50" name="queries">'.trim(htmlspecialchars_decode(isset($_REQUEST['queries'])?$_REQUEST['queries']:"", ENT_QUOTES)).'</textarea></td></tr>
	</table>
	<input type="submit" value="Submit">
	</form>
	Custom Queries Version '.$customQueriesVersion.' by <a href="http://www.audox.cl">Audox Ingenier&iacute;a SpA.</a><br/><br/>
	';
}

if(isset($_REQUEST['queries']) && $current_user->is_admin){
	$html_result="";
	$array_result=array();
	$array_result['version'] = $customQueriesVersion; 
	$array_result['error'] = 0;
	$additional_options = array();
	if($_REQUEST['entryPoint']==='CustomQueries'){
		$queries = trim(htmlspecialchars_decode($_REQUEST['queries'], ENT_QUOTES));
		$queries = rtrim($queries, ';');
		$queries = explode(";", $queries);
	}
	else{
		$queries = json_decode(urldecode($_REQUEST['queries']));
		if(isset($_REQUEST['options'])) $additional_options = json_decode(urldecode($_REQUEST['options']));
	}
	foreach($additional_options as $key => $value){
		$options[$key] = $value;
	}
	foreach($queries as $query_id => $query){
		$query = trim($query);
		if($options["show_queries"] == true) $html_result.="Query: ".$query."<br />";
		$array_result['results'][$query_id]['query']=$query;
		$res=$db->query($query, false, 'Error');
		$lastDbError = $db->lastDbError();
		if($lastDbError == false){
			$html_result.="<table id=\"table_".$query_id."\" border=\"1\" cellspacing=0 cellpadding=0>";
			$header_style="style=\"background-color:black; color:white;\"";
			$i = 1;
			while($row=$db->fetchByAssoc($res)){
				if($i==1){
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
		}
		else $html_result .= $lastDbError."<br/>";
		if(($_REQUEST['entryPoint']==='CustomQueries' || $_REQUEST['format']==='html') && $lastDbError == false){
			$html_result.="<input value=\"View CSV\" type=\"button\" onclick=\"$('#table_".$query_id."').table2CSV()\">";
			$html_result.='<form id ="get_csv_form_table_'.$query_id.'" action="index.php?entryPoint=getCSV" method ="post" > 
<input type="hidden" id="csv_text_table_'.$query_id.'" name="csv_text">
<input type="submit" id="submit_'.$query_id.'" value="Download CSV File" onclick="getCSVData(\'table_'.$query_id.'\')">
</form>';
		}
		$html_result.="<br />";
	}
	if($_REQUEST['format']==='array') echo print_r($array_result);
	elseif($_REQUEST['format']==='json') echo json_encode($array_result);
	else echo $javascript.$html_result;
}

?>