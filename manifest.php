<?php

$manifest = array(
	'acceptable_sugar_flavors' => array(
		'CE',
		'PRO',
		'ENT',
		'CORP',
		'ULT',
	),
	'acceptable_sugar_versions' => array(
		'6*',
		'7*',
	),
	'is_uninstallable' => true,
	'name' => 'Custom Queries',
	'author' => 'Audox Ingenieria Ltda',
	'description' => 'Custom Queries',
	'published_date' => '2017/07/09',
	'version' => 'v1.7',
	'type' => 'module',
);

$installdefs = array(
	'copy' => array(
		array(
			'from' => '<basepath>/CustomQueries.php',
			'to' => 'custom/CustomQueries.php',
		),
		array(
			'from' => '<basepath>/table2CSV.js',
			'to' => 'custom/include/javascript/table2CSV.js',
		),
		array(
			'from' => '<basepath>/getCSV.php',
			'to' => 'custom/getCSV.php',
		)
	),
	'entrypoints' => array(
		array(
			'from' => '<basepath>/CustomQueriesEntryPoint.php',
			'to_module' => 'application',
			),
	),
	'linkdefs' => array(
		array(
			'from' => '<basepath>/CustomQueriesLink.php',
			'to_module' => 'application',
			),
	),
	'language' => array(
		array(
			'from' => '<basepath>/en_us.CustomQueries.php',
			'to_module' => 'application',
			'language' => 'en_us',
		)
	),
);

?>