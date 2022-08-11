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
		'8*',
		'9*',
		'10*',
	),
	'is_uninstallable' => true,
	'name' => 'Custom Queries',
	'author' => 'Audox Ingenieria SpA.',
	'description' => 'Custom Queries',
	'published_date' => '2020/06/15',
	'version' => 'v1.20',
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
			'from' => '<basepath>/en_us.CustomQueries.lang.php',
			'to_module' => 'application',
			'language' => 'en_us',
		)
	),
);

?>