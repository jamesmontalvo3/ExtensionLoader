<?php

$egExtensionLoaderConfig += array(
	'SemanticForms' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SemanticForms.git',
		'branch' => 'master',
	),

	// 'WikiEditor' => true,
	'WikiEditor' => array(
		'afterFn' => function () {
			# Enables use of WikiEditor by default but still allow users to disable it in preferences
			$GLOBALS['wgDefaultUserOptions']['usebetatoolbar'] = 1;
			$GLOBALS['wgDefaultUserOptions']['usebetatoolbar-cgd'] = 1;
			 
			# Displays the Preview and Changes tabs
			$GLOBALS['wgDefaultUserOptions']['wikieditor-preview'] = 1;
			 
			# Displays the Publish and Cancel buttons on the top right side
			$GLOBALS['wgDefaultUserOptions']['wikieditor-publish'] = 1;
		},
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/WikiEditor.git',
		'branch' => 'master', // @todo: should this be REL1_25 ???
	),

	'ParserFunctionHelper' => array(
		'git' => 'https://github.com/enterprisemediawiki/ParserFunctionHelper.git',
		'branch' => 'master',
	),

	'BasicParserFunction' => array(
		'git' => 'https://github.com/jamesmontalvo3/BasicParserFunction.git',
		'branch' => 'master',
	),

	'WatchAnalytics' => array(
		'globals' => array(
			'egPendingReviewsEmphasizeDays' => 10,
		),
		// 'afterFn' => function( /* what to pass in? */ ) {
		// 	print_r( array( "this"  => "is a test" ) );
		// },
		'git' => 'https://github.com/jamesmontalvo3/WatchAnalytics.git',
		'branch' => 'master',
	),

	'Cite' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Cite.git',
		'branch' => 'master',
	),

	'ParserFunctions' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ParserFunctions.git',
		'branch' => 'master',
	),

	'Interwiki' => array(
		'afterFn' => function() {
			// To grant sysops permissions to edit interwiki data
			$GLOBALS['wgGroupPermissions']['sysop']['interwiki'] = true;
		},
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Interwiki.git',
		'branch' => 'master',
	),

	'SyntaxHighlight_GeSHi' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SyntaxHighlight_GeSHi.git',
		'branch' => 'master',
	),

);