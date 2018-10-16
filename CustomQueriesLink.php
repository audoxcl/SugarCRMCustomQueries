<?php

global $current_user;

if($current_user->is_admin){
	$global_control_links['custom_queries'] = array(
		'linkinfo' => array(
			$app_strings['LBL_CUSTOM_QUERIES'] => ' javascript:void window.open(\'index.php?entryPoint=CustomQueries\', \'CustomQueries\', \'top=0,left=0,width=600,height=550\')'
		)
	);
}

?>