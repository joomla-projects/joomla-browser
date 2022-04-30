<?php
/**
 * @package         Joomla.Tests
 * @subpackage      Acceptance.tests
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Execute all features of JoomlaBrowser once
 *
 * @since  4.0
 */
class JoomlaBrowserCest
{
	public function testInstallations(AcceptanceTester $I)
	{
		$path = $I->getConfig('cmsPath');
		$I->wantToTest('installing Joomla');
		$I->installJoomla();

		// Resetting installation to be installed again.
		if (is_file($path . '/configuration.php'))
		{
			unlink($path . '/configuration.php');
		}

		$I->wantToTest('installing Joomla and removing the install folder');
		$I->installJoomlaRemovingInstallationFolder();

		if (is_dir($path . '/installation'))
		{
			$I->fail('Installation folder wasn\'t deleted');
		}

		// Restoring installation folder to be installed again.
		if (is_file($path . '/configuration.php'))
		{
			unlink($path . '/configuration.php');
		}

		\Joomla\Filesystem\Folder::copy(
			__DIR__ . '/../../cache/installation',
			$path . '/installation'
		);

		$I->wantToTest('installing Joomla with multiple languages.');
		$I->installJoomlaMultilingualSite(['German', 'Chinese, Simplified', 'French']);
	}

	public function testSupportMethods(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
		$I->disableStatistics();
		$I->setErrorReportingToDevelopment();
		$I->checkForPhpNoticesOrWarnings();
		$I->amOnPage('/administrator/index.php?option=com_config');
		$I->verifyAvailableTabs(
			['Site', 'System', 'Server', 'Logging', 'Text Filters', 'Permissions'],
			"//joomla-tab[@id='configTabs']/div[@role='tablist']/button"
		);
		//$I->clickToolbarButton($button, $subselector = null);
	}

	public function testUserManagement(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
		$I->createUser('Test User', 'testuser', 'Password123!', 'user@example.org', ['Registered']);
		$I->amOnPage('administrator/index.php');
		$I->doAdministratorLogout();
		$I->doFrontEndLogin('testuser', 'Password123!');
		$I->doFrontendLogout();
	}
}
