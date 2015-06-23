<?php

// get config from: JSCMOD

// handle bad file names from:
// 'extensions/LabeledSectionTransclusion/lst.php' 
// 'extensions/LabeledSectionTransclusion/lsth.php' 
// "$IP/extensions/intersection/DynamicPageList.php"

// move to composer?: SemanticMeetingMinutes
// pretty sure this isn't needed with SMM: "$IP/extensions/Synopsize/Synopsize.php";

// consider this:
// 'ParserFunctionHelper' => array(
// 	'git' => 'https://github.com/enterprisemediawiki/ParserFunctionHelper.git',
// 	'branch' => 'master',
// ),

$egExtensionLoaderConfig += array(

	'ParserFunctions' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ParserFunctions.git',
		'branch' => 'master',
		'globals' => array(
			'wgPFEnableStringFunctions' => true,
		),
	),

	'StringFunctionsEscaped' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/StringFunctionsEscaped.git',
		'branch' => 'master',
	),

	'ExternalData' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ExternalData.git',
		'branch' => 'master',
	),

	'Cite' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Cite.git',
		'branch' => 'master',
		'globals' => array(
			'wgCiteEnablePopups' => true,
		),
	),

	'PipeEscape' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/PipeEscape.git',
		'branch' => 'master',
	),

	'HeaderFooter' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/HeaderFooter.git',
		'branch' => 'master',
	),

	'WhoIsWatching' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/WhoIsWatching.git',
		'branch' => 'master',
		'globals' => array(
			'wgPageShowWatchingUsers' => true,
		),
	),

	'CharInsert' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/CharInsert.git',
		'branch' => 'master',
	),

	// 'SemanticForms' => array(
	// 	'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SemanticForms.git',
	// 	'branch' => 'master',
	// ),

	// 'SemanticInternalObjects' => array(
	// 	'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SemanticInternalObjects.git',
	// 	'branch' => 'master',
	// ),

	// 'SemanticCompoundQueries' => array(
	// 	'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SemanticCompoundQueries.git',
	// 	'branch' => 'master',
	// ),

	'Arrays' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Arrays.git',
		'branch' => 'master',
	),

	'TitleKey' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/TitleKey.git',
		'branch' => 'master',
	),

	'TalkRight' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/TalkRight.git',
		'branch' => 'master',
	),

	'AdminLinks' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/AdminLinks.git',
		'branch' => 'master',
		'afterFn' => function() {
			$wgGroupPermissions['sysop']['adminlinks'] = true;
		}
	),

	'DismissableSiteNotice' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/DismissableSiteNotice.git',
		'branch' => 'master',
	),

	'BatchUserRights' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/BatchUserRights.git',
		'branch' => 'master',
	),

	'ImportUsers' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ImportUsers.git',
		'branch' => 'master',
		'globals' => array(
			'wgShowExceptionDetails' => true,
		)
	),

	'HeaderTabs' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/HeaderTabs.git',
		'branch' => 'master',
		'globals' => array(
			'htEditTabLink' => false,
			'htRenderSingleTab' => true,
		)
	),

	'WikiEditor' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/WikiEditor.git',
		'branch' => 'master',
		'afterFn' => function() {
			$wgDefaultUserOptions['usebetatoolbar'] = 1;
			$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
			# displays publish button
			$wgDefaultUserOptions['wikieditor-publish'] = 1;
			# Displays the Preview and Changes tabs
			$wgDefaultUserOptions['wikieditor-preview'] = 1;
		}
	),

	'CopyWatchers' => array(
		'git' => 'https://github.com/jamesmontalvo3/MediaWiki-CopyWatchers.git',
		'branch' => 'master',
	),

	'SyntaxHighlight_GeSHi' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/SyntaxHighlight_GeSHi.git',
		'branch' => 'master',
	),

	'Wiretap' => array(
		'git' => 'https://github.com/enterprisemediawiki/Wiretap.git',
		'branch' => 'master',
	),

	'ApprovedRevs' => array(
		'git' => 'https://github.com/jamesmontalvo3/MediaWiki-ApprovedRevs.git',
		'branch' => 'master',
		'globals' => array(
			'egApprovedRevsAutomaticApprovals' => false,
		),
	),

	'InputBox' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/InputBox.git',
		'branch' => 'master',
	),

	'ReplaceText' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ReplaceText.git',
		'branch' => 'master',
	),

	'Interwiki' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Interwiki.git',
		'branch' => 'master',
		'afterFn' => function() {
			$wgGroupPermissions['sysop']['interwiki'] = true;
		}
	),

	'IMSQuery' => array(
		'git' => 'https://github.com/jamesmontalvo3/IMSQuery.git',
		'branch' => 'master',
	),

	'MasonryMainPage' => array(
		'git' => 'https://github.com/enterprisemediawiki/MasonryMainPage.git',
		'branch' => 'master',
	),

	'WatchAnalytics' => array(
		'git' => 'https://github.com/enterprisemediawiki/WatchAnalytics.git',
		'branch' => 'master',
		'globals' => array(
			'egPendingReviewsEmphasizeDays' => 10, // makes Pending Reviews shake after X days
		),
	),

	'NumerAlpha' => array(
		'git' => 'https://github.com/jamesmontalvo3/NumerAlpha.git',
		'branch' => 'master',
	),

	'Variables' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/Variables.git',
		'branch' => 'master',
	),

	'SummaryTimeline' => array(
		'git' => 'https://github.com/darenwelsh/SummaryTimeline.git',
		'branch' => 'master',
	),

	'YouTube' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/YouTube.git',
		'branch' => 'master',
	),

	'ContributionScores' => array(
		'git' => 'https://git.wikimedia.org/git/mediawiki/extensions/ContributionScores.git',
		'branch' => 'master',
		'afterFn' => function() {
			$wgContribScoreIgnoreBots = true;          // Exclude Bots from the reporting - Can be omitted.
			$wgContribScoreIgnoreBlockedUsers = true;  // Exclude Blocked Users from the reporting - Can be omitted.
			$wgContribScoresUseRealName = true;        // Use real user names when available - Can be omitted. Only for MediaWiki 1.19 and later.
			$wgContribScoreDisableCache = false;       // Set to true to disable cache for parser function and inclusion of table.
			//Each array defines a report - 7,50 is "past 7 days" and "LIMIT 50" - Can be omitted.
			$wgContribScoreReports = array(
			    array(7,50),
			    array(30,50),
			    array(0,50));
		}
	),

);