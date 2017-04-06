<?php
/**
 * Command line script for executing PHPCS during a Travis build.
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only run on the CLI SAPI
(php_sapi_name() == 'cli' ?: die('CLI only'));

// Script defines
define('REPO_BASE', dirname(__DIR__));

// Require Composer autoloader
if (!file_exists(REPO_BASE . '/vendor/autoload.php'))
{
	fwrite(STDOUT, "\033[37;41mThis script requires Composer to be set up, please run 'composer install' first.\033[0m\n");
}

require REPO_BASE . '/vendor/autoload.php';

// Welcome message
fwrite(STDOUT, "\033[32;1mInitializing PHP_CodeSniffer checks.\033[0m\n");

// Ignored files
$ignored = array(
	REPO_BASE . '/component/admin/views/*/tmpl/*',
	REPO_BASE . '/component/admin/layouts/*',
	REPO_BASE . '/component/admin/tables/*',
	REPO_BASE . '/component/site/views/*/tmpl/*',
	REPO_BASE . '/component/site/layouts/*',
	REPO_BASE . '/component/admin/extras/*',
	REPO_BASE . '/libraries/reditem/layouts/*',
	REPO_BASE . '/libraries/reditem/html/*',
	REPO_BASE . '/libraries/reditem/media/*',
	REPO_BASE . '/libraries/reditem/vendor/*',
	REPO_BASE . '/libraries/reditem/helper/mobiledetect.php',
	REPO_BASE . '/modules/admin/*/tmpl/*',
	REPO_BASE . '/modules/admin/*/layouts/*',
	REPO_BASE . '/modules/admin/*/media/*',
	REPO_BASE . '/modules/site/*/tmpl/*',
	REPO_BASE . '/modules/site/*/media/*',
	REPO_BASE . '/plugins/*/layouts/*',
	REPO_BASE . '/plugins/*/media/*',
	REPO_BASE . '/plugins/*/tmpl/*',

	// Ignored due to a PHPCS bug
	REPO_BASE . '/component/admin/models/visit.php',
	REPO_BASE . '/libraries/reditem/functions.php',

	// Ignored because extends a Joomla class with invalid properties
	REPO_BASE . '/libraries/reditem/src/Core/Table/AbstractTable.php',
	REPO_BASE . '/libraries/reditem/src/Core/Table/AbstractNestedTable.php',
	REPO_BASE . '/libraries/reditem/src/Table/CustomDataTable.php'
);

// Build the options for the sniffer
$options = array(
	'files'        => array(
		REPO_BASE . '/plugins',
		REPO_BASE . '/component',
		REPO_BASE . '/modules',
		REPO_BASE . '/libraries',
	),
	'standard'     => array(__DIR__ . '/phpcs/Joomla'),
	'ignored'      => $ignored,
	'showProgress' => true,
	'verbosity'    => false
);

// Instantiate the sniffer
$phpcs = new PHP_CodeSniffer_CLI;

// Ensure PHPCS can run, will exit if requirements aren't met
$phpcs->checkRequirements();

// Run the sniffs
$numErrors = $phpcs->process($options);

// If there were errors, output the number and exit the app with a fail code
if ($numErrors)
{
	fwrite(STDOUT, sprintf("\033[37;41mThere were %d issues detected.\033[0m\n", $numErrors));

	exit(1);
}
else
{
	fwrite(STDOUT, "\033[32;1mThere were no issues detected.\033[0m\n");
	exit(0);
}
