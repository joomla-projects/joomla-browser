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

		/**
		 * @TODO deleting install folder doesn't work
		 *       before Joomla 4.2. This return needs
		 *       to be removed when 4.2 is the default branch.
		 */
		\Joomla\Filesystem\Folder::delete($path . '/installation');

		return;

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
		$I->setSiteSearchEngineFriendly();
		$I->setSiteOffline();
		$I->setSiteOffline(false);
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

	public function testExtensionManagement(AcceptanceTester $I)
	{
		// Get latest weblinks release, so that we have something to install
		$github = new \Joomla\Github\Github();
		$release = $github->repositories->releases->get('joomla-extensions', 'weblinks', 'latest');
		$url = $release->assets[0]->browser_download_url;
		$path = $I->getConfig('cmsPath');

		if (!is_file($path . '/weblinks.zip'))
		{
			file_put_contents(
				$path . '/weblinks.zip',
				file_get_contents($url)
			);
			copy($path . '/weblinks.zip', __DIR__ . '/../_data/weblinks.zip');
		}

		$I->doAdministratorLogin(null, null, false);
		$zip = new ZipArchive;
		$res = $zip->open($path . '/weblinks.zip');

		if ($res === true)
		{
			$zip->extractTo($path . '/tmp');
			$zip->close();
		}
		else
		{
			$I->fail('Can\'t extract weblinks package to folder');
		}

		$I->installExtensionFromFolder($path . '/tmp', 'pkg_weblinks');
		$I->uninstallExtension('Web Links Extension Package');
		$I->installExtensionFromUrl($url, 'pkg_weblinks');
		$I->uninstallExtension('Web Links Extension Package');
		$I->installExtensionFromFileUpload('weblinks.zip', 'pkg_weblinks');

		$I->doAdministratorLogin();
		$I->installLanguage('Czech');
	}
}
