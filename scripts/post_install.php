<?php

require_once('modules/Configurator/Configurator.php');

function post_install(){
	global $sugar_config;
	if(!isset($sugar_config['ApiKey'])){
		$cfg = new Configurator();
		$cfg->config['CustomQueries']['ApiKey'] = 'CustomQueriesFreeApiKey';
		$cfg->handleOverride();
	}
}

?>
