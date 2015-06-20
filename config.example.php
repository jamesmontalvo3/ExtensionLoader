<?php

$egExtmanConfig += array(

	'SemanticMediaWiki' => array(
		'composer' => true,
		'globals' => array(
			'smwgThings' => true,
			'smwgStuff' => 'foo',
		),
		'afterFn' => function( /* what to pass in? */ ) {
			enableSemantics( 'wiki.nasa.gov' );
		},
	),

	'DynamicPageList' => array(
		'git' => 'https://git.wikimedia.org/...',
		'checkout' => 'master|tags/1.24.1|REL1_25|2b449A',
		'entry' => 'Intersection.php',
		'globals' => array(
			'egDoStuff' => 45,
			'egThingsThatAreConfigurable' => true,
		),
	),

	// 'TalkRight' => array(
	// 	'afterFn' => function( /* ??? */ ) {
	// 		$wgGroupRights['*']['talk'] = true;	
	// 	},
	// 	'tarball' => 'https://mirror.mirror.net/extension.tar.gz' // how does it check for new versions?
	// ),

);