<?php
/**
 * @package    JoomlaBrowser
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Codeception\Module;

use Codeception\Module\WebDriver;

/**
 * Joomla Browser class to perform test suits for Joomla.
 *
 * @since  1.0
 */
class JoomlaBrowser extends WebDriver
{
	/**
	 * The module required fields, to be set in the suite .yml configuration file.
	 *
	 * @var array
	 */
	protected $requiredFields = array(
		'url',
		'browser',
		'username',
		'password',
		'database type',
		'database host',
		'database user',
		'database password',
		'database name',
		'database type',
		'database prefix',
		'admin email',
		'language'
	);

	/**
	 * Function to Do Admin Login In Joomla!
	 *
	 * @param   string|null  $user      Optional Username. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $password  Optional password. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @return  void
	 */
	public function doAdministratorLogin($user = null, $password = null)
	{
		$i = $this;

		if (is_null($user))
		{
			$user = $this->config['username'];
		}

		if (is_null($password))
		{
			$password = $this->config['password'];
		}

		$this->debug('I open Joomla Administrator Login Page');
		$i->amOnPage('/administrator/index.php');
		$i->waitForElement(array('id' => 'mod-login-username'), 60);
		$this->debug('Fill Username Text Field');
		$i->fillField(array('id' => 'mod-login-username'), $user);
		$this->debug('Fill Password Text Field');
		$i->fillField(array('id' => 'mod-login-password'), $password);

		// @todo: update login button in joomla login screen to make this xPath more friendly
		$this->debug('I click Login button');
		$i->click(array('xpath' => "//button[contains(normalize-space(), 'Log in')]"));
		$this->debug('I wait to see Administrator Control Panel');
		$i->waitForText('Control Panel', 4, array('css' => 'h1.page-title'));
	}

	/**
	 * Function to Do Frontend Login In Joomla!
	 *
	 * @param   string|null  $user      Optional username. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $password  Optional password. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @return  void
	 */
	public function doFrontEndLogin($user = null, $password = null)
	{
		$i = $this;

		if (is_null($user))
		{
			$user = $this->config['username'];
		}

		if (is_null($password))
		{
			$password = $this->config['password'];
		}

		$this->debug('I open Joomla Frontend Login Page');
		$i->amOnPage('/index.php?option=com_users&view=login');
		$this->debug('Fill Username Text Field');
		$i->fillField(array('id' => 'username'), $user);
		$this->debug('Fill Password Text Field');
		$i->fillField(array('id' => 'password'), $password);

		// @todo: update login button in joomla login screen to make this xPath more friendly
		$this->debug('I click Login button');
		$i->click(array('xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button"));
		$this->debug('I wait to see Frontend Member Profile Form with the Logout button in the module');
		$i->waitForElement(array('xpath' => "//form[@id='login-form']/div[@class='logout-button']"), 60);
	}

	/**
	 * Function to Do frontend Logout in Joomla!
	 *
	 * @return  void
	 */
	public function doFrontendLogout()
	{
		$i = $this;
		$this->debug('I open Joomla Frontend Login Page');
		$i->amOnPage('/index.php?option=com_users&view=login');
		$this->debug('I click Logout button');
		$i->click(array('xpath' => "//div[@class='logout']//button[contains(text(), 'Log out')]"));
		$this->debug('I wait to see Login form');
		$i->waitForElement(array('xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]"), 30);
		$i->seeElement(array('xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]"));
	}

	/**
	 * Installs Joomla
	 *
	 * @return  void
	 */
	public function installJoomla()
	{
		$i = $this;

		// Install Joomla CMS');

		$this->debug('I open Joomla Installation Configuration Page');
		$i->amOnPage('/installation/index.php');
		$this->debug('I check that FTP tab is not present in installation. Otherwise it means that I have not enough '
			. 'permissions to install joomla and execution will be stopped'
		);
		$i->dontSeeElement(array('id' => 'ftp'));

		// I Wait for the text Main Configuration, meaning that the page is loaded
		$this->debug('I wait for Main Configuration');
		$i->waitForElement('#jform_language', 10);
		$i->debug('Wait for chosen to render the Languages list field');
		$i->wait(2);
		$i->debug('I select dk-DK as installation language');

		// Select a random language to force reloading of the lang strings after selecting English
		$i->selectOptionInChosen('#jform_language', 'Danish (DK)');
		$i->waitForText('Generel konfiguration', 60, 'h3');

		// Wait for chosen to render the field
		$i->debug('I select en-GB as installation language');
		$i->debug('Wait for chosen to render the Languages list field');
		$i->wait(2);
		$i->selectOptionInChosen('#jform_language', 'English (United Kingdom)');
		$i->waitForText('Main Configuration', 60, 'h3');
		$this->debug('I fill Site Name');
		$i->fillField(array('id' => 'jform_site_name'), 'Joomla CMS test');
		$this->debug('I fill Site Description');
		$i->fillField(array('id' => 'jform_site_metadesc'), 'Site for testing Joomla CMS');

		// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
		$this->debug('I fill Admin Email');
		$i->fillField(array('id' => 'jform_admin_email'), $this->config['admin email']);
		$this->debug('I fill Admin Username');
		$i->fillField(array('id' => 'jform_admin_user'), $this->config['username']);
		$this->debug('I fill Admin Password');
		$i->fillField(array('id' => 'jform_admin_password'), $this->config['password']);
		$this->debug('I fill Admin Password Confirmation');
		$i->fillField(array('id' => 'jform_admin_password2'), $this->config['password']);
		$this->debug('I click Site Offline: no');

		// ['No Site Offline']
		$i->click(array('xpath' => "//fieldset[@id='jform_site_offline']/label[@for='jform_site_offline1']"));
		$this->debug('I click Next');
		$i->click(array('link' => 'Next'));

		$this->debug('I Fill the form for creating the Joomla site Database');
		$i->waitForText('Database Configuration', 60, array('css' => 'h3'));

		$this->debug('I select MySQLi');
		$i->selectOption(array('id' => 'jform_db_type'), $this->config['database type']);
		$this->debug('I fill Database Host');
		$i->fillField(array('id' => 'jform_db_host'), $this->config['database host']);
		$this->debug('I fill Database User');
		$i->fillField(array('id' => 'jform_db_user'), $this->config['database user']);
		$this->debug('I fill Database Password');
		$i->fillField(array('id' => 'jform_db_pass'), $this->config['database password']);
		$this->debug('I fill Database Name');
		$i->fillField(array('id' => 'jform_db_name'), $this->config['database name']);
		$this->debug('I fill Database Prefix');
		$i->fillField(array('id' => 'jform_db_prefix'), $this->config['database prefix']);
		$this->debug('I click Remove Old Database ');
		$i->selectOptionInRadioField('Old Database Process', 'Remove');
		$this->debug('I click Next');
		$i->click(array('link' => 'Next'));
		$this->debug('I wait Joomla to remove the old database if exist');
		$i->wait(1);
		$i->waitForElementVisible(array('id' => 'jform_sample_file-lbl'), 30);

		$this->debug('I install joomla with or without sample data');
		$i->waitForText('Finalisation', 60, array('xpath' => '//h3'));

		// @todo: installation of sample data needs to be created

		// No sample data
		$i->selectOption(array('id' => 'jform_sample_file'), array('id' => 'jform_sample_file0'));
		$i->click(array('link' => 'Install'));

		// Wait while Joomla gets installed
		$this->debug('I wait for Joomla being installed');
		$i->waitForText('Congratulations! Joomla! is now installed.', 60, array('xpath' => '//h3'));
	}

	/**
	 * Install Joomla removing the Installation folder at the end of the execution
	 *
	 * @return  void
	 */
	public function installJoomlaRemovingInstallationFolder()
	{
		$i = $this;

		$i->installJoomla();

		$this->debug('Removing Installation Folder');
		$i->click(array('xpath' => "//input[@value='Remove installation folder']"));

		$i->debug('I wait for Removing Installation Folder button to become disabled');
		$i->waitForJS("return jQuery('form#adminForm input[name=instDefault]').attr('disabled') == 'disabled';", 60);

		$i->debug('Joomla is now installed');
		$i->see('Congratulations! Joomla! is now installed.', array('xpath' => '//h3'));
	}

	/**
	 * Installs Joomla with Multilingual Feature active
	 *
	 * @param   array  $languages  Array containing the language names to be installed
	 *
	 * @example: $i->installJoomlaMultilingualSite(['Spanish', 'French']);
	 *
	 * @return  void
	 */
	public function installJoomlaMultilingualSite($languages = array())
	{
		if (!$languages)
		{
			// If no language is passed French will be installed by default
			$languages[] = 'French';
		}

		$i = $this;

		$i->installJoomla();

		$this->debug('I go to Install Languages page');
		$i->click(array('id' => 'instLangs'));
		$i->waitForText('Install Language packages', 60, array('xpath' => '//h3'));

		foreach ($languages as $language)
		{
			$i->debug('I mark the checkbox of the language: ' . $language);
			$i->click(array('xpath' => "//label[contains(text()[normalize-space()], '$language')]"));
		}

		$i->click(array('link' => 'Next'));
		$i->waitForText('Multilingual', 60, array('xpath' => '//h3'));
		$i->selectOptionInRadioField('Activate the multilingual feature', 'Yes');
		$i->waitForElementVisible(array('id' => 'jform_activatePluginLanguageCode-lbl'));
		$i->selectOptionInRadioField('Install localised content', 'Yes');
		$i->selectOptionInRadioField('Enable the language code plugin', 'Yes');
		$i->click(array('link' => 'Next'));

		$i->waitForText('Congratulations! Joomla! is now installed.', 60, array('xpath' => '//h3'));
		$this->debug('Removing Installation Folder');
		$i->click(array('xpath' => "//input[@value='Remove installation folder']"));

		// @todo https://github.com/joomla-projects/joomla-browser/issues/45
		$i->wait(2);

		$this->debug('Joomla is now installed');
		$i->see('Congratulations! Joomla! is now installed.', array('xpath' => '//h3'));
	}

	/**
	 * Sets in Adminitrator->Global Configuration the Error reporting to Development
	 * {@internal doAdminLogin() before}
	 *
	 * @return  void
	 */
	public function setErrorReportingToDevelopment()
	{
		$i = $this;
		$this->debug('I open Joomla Global Configuration Page');
		$i->amOnPage('/administrator/index.php?option=com_config');
		$this->debug('I wait for Global Configuration title');
		$i->waitForText('Global Configuration', 60, array('css' => '.page-title'));
		$this->debug('I open the Server Tab');
		$i->click(array('link' => 'Server'));
		$this->debug('I wait for error reporting dropdown');
		$i->selectOptionInChosen('Error Reporting', 'Development');
		$this->debug('I click on save');
		$this->clickToolbarButton('save');
		$this->debug('I wait for global configuration being saved');
		$i->waitForText('Global Configuration', 60, array('css' => '.page-title'));
		$i->see('Configuration successfully saved.', array('id' => 'system-message-container'));
	}

	/**
	 * Installs a Extension in Joomla that is located in a folder inside the server
	 *
	 * @param   String  $path  Path for the Extension
	 * @param   string  $type  Type of Extension
	 *
	 * @note: doAdminLogin() before
	 *
	 * @deprecated  since Joomla 3.4.4-dev. Use installExtensionFromFolder($path, $type = 'Extension') instead.
	 *
	 * @return      void
	 */
	public function installExtensionFromDirectory($path, $type = 'Extension')
	{
		$this->debug('Suggested to use installExtensionFromFolder instead of installExtensionFromDirectory');
		$this->installExtensionFromFolder($path, $type);
	}

	/**
	 * Installs a Extension in Joomla that is located in a folder inside the server
	 *
	 * @param   String  $path  Path for the Extension
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 */
	public function installExtensionFromFolder($path, $type = 'Extension')
	{
		$i = $this;
		$i->amOnPage('/administrator/index.php?option=com_installer');
		$i->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$i->click(array('link' => 'Install from Folder'));
		$this->debug('I enter the Path');
		$i->fillField(array('id' => 'install_directory'), $path);
		$i->click(array('id' => 'installbutton_directory'));
		$i->waitForText('was successful', '60', array('id' => 'system-message-container'));
		$this->debug("$type successfully installed from $path");
	}

	/**
	 * Installs a Extension in Joomla that is located in a url
	 *
	 * @param   string  $url   Url address to the .zip file
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 */
	public function installExtensionFromUrl($url, $type = 'Extension')
	{
		$i = $this;
		$i->amOnPage('/administrator/index.php?option=com_installer');
		$i->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$i->click(array('link' => 'Install from URL'));
		$this->debug('I enter the url');
		$i->fillField(array('id' => 'install_url'), $url);
		$i->click(array('id' => 'installbutton_url'));
		$i->waitForText('was successful', '30', array('id' => 'system-message-container'));

		if ($type == 'Extension')
		{
			$this->debug('Extension successfully installed from ' . $url);
		}

		if ($type == 'Plugin')
		{
			$this->debug('Installing plugin was successful.' . $url);
		}

		if ($type == 'Package')
		{
			$this->debug('Installation of the package was successful.' . $url);
		}
	}

	/**
	 * Installs a Extension in Joomla using the file upload option
	 *
	 * @param   string  $file   Path to the file in the _data folder
	 * @param   string  $type   Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 */
	public function installExtensionFromFileUpload($file, $type = 'Extension')
	{
		$i = $this;
		$i->amOnPage('/administrator/index.php?option=com_installer');
		$i->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$i->click(array('link' => 'Upload Package File'));
		$this->debug('I enter the file input');
		$i->attachFile(array('id' => 'install_package'), $file);
		$i->click(array('id' => 'installbutton_package'));
		$i->waitForText('was successful', '30', array('id' => 'system-message-container'));

		if ($type == 'Extension')
		{
			$this->debug('Extension successfully installed.');
		}

		if ($type == 'Plugin')
		{
			$this->debug('Installing plugin was successful.');
		}

		if ($type == 'Package')
		{
			$this->debug('Installation of the package was successful.');
		}
	}

	/**
	 * Function to check for PHP Notices or Warnings
	 *
	 * @param   string  $page  Optional, if not given checks will be done in the current page
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		$i = $this;

		if ($page)
		{
			$i->amOnPage($page);
		}

		$i->dontSeeInPageSource('Notice:');
		$i->dontSeeInPageSource('<b>Notice</b>:');
		$i->dontSeeInPageSource('Warning:');
		$i->dontSeeInPageSource('<b>Warning</b>:');
		$i->dontSeeInPageSource('Strict standards:');
		$i->dontSeeInPageSource('<b>Strict standards</b>:');
		$i->dontSeeInPageSource('The requested page can\'t be found');
	}

	/**
	 * Selects an option in a Joomla Radio Field based on its label
	 *
	 * @param   string  $label   The text in the <label> with for attribute that links to the radio element
	 * @param   string  $option  The text in the <option> to be selected in the chosen radio button
	 *
	 * @return  void
	 */
	public function selectOptionInRadioField($label, $option)
	{
		$i = $this;
		$i->debug("Trying to select the $option from the $label");
		$label = $i->findField(array('xpath' => "//label[contains(normalize-space(string(.)), '$label')]"));
		$radioId = $label->getAttribute('for');

		$i->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
	}

	/**
	 * Selects an option in a Chosen Selector based on its label
	 *
	 * @param   string  $label   The text in the <label> with for attribute that links to the <select> element
	 * @param   string  $option  The text in the <option> to be selected in the chosen selector
	 *
	 * @return void
	 */
	public function selectOptionInChosen($label, $option)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';
		$i = $this;
		$this->debug("I open the $label chosen selector");
		$i->click(array('xpath' => "//div[@id='$chosenSelectID']/a/div/b"));
		$this->debug("I select $option");
		$i->click(array('xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"));

		// Gives time to chosen to close
		$i->wait(1);
	}

	/**
	 * Selects an option in a Chosen Selector based on its id
	 *
	 * @param   string  $selectId  The id of the <select> element
	 * @param   string  $option    The text in the <option> to be selected in the chosen selector
	 *
	 * @return void
	 */
	public function selectOptionInChosenById($selectId, $option)
	{
		$chosenSelectID = $selectId . '_chzn';
		$i = $this;
		$this->debug("I open the $chosenSelectID chosen selector");
		$i->click(array('xpath' => "//div[@id='$chosenSelectID']/a/div/b"));
		$this->debug("I select $option");
		$i->click(array('xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"));

		// Gives time to chosen to close
		$i->wait(1);
	}

	/**
	 * Selects an option in a Chosen Selector based on its id
	 *
	 * @param   string  $selectId  The id of the <select> element
	 * @param   string  $option    The text in the <option> to be selected in the chosen selector
	 *
	 * @return void
	 */
	public function selectOptionInChosenByIdUsingJs($selectId, $option)
	{
		$i = $this;

		$option = trim($option);
		$i->executeJS("jQuery('#$selectId option').filter(function(){ return this.text.trim() === \"$option\" }).prop('selected', true);");
		$i->executeJS("jQuery('#$selectId').trigger('liszt:updated').trigger('chosen:updated');");
		$i->executeJS("jQuery('#$selectId').trigger('change');");

		// Give time to Chosen to update
		$i->wait(1);
	}

	/**
	 * Selects one or more options in a Chosen Multiple Select based on its label
	 *
	 * @param   string  $label    Label of the select field
	 * @param   array   $options  Array of options to be selected
	 *
	 * @return void
	 */
	public function selectMultipleOptionsInChosen($label, $options)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';
		$i = $this;

		foreach ($options as $option)
		{
			$this->debug("I open the $label chosen selector");
			$i->click(array('xpath' => "//div[@id='$chosenSelectID']/ul"));
			$this->debug("I select $option");
			$i->click(array('xpath' => "//div[@id='$chosenSelectID']//li[contains(text()[normalize-space()], '$option')]"));

			// Gives time to chosen to close
			$i->wait(1);
		}
	}

	/**
	 * Function to Logout from Administrator Panel in Joomla!
	 *
	 * @return void
	 */
	public function doAdministratorLogout()
	{
		$i = $this;
		$i->click(array('xpath' => "//ul[@class='nav nav-user pull-right']//li//a[@class='dropdown-toggle']"));
		$this->debug("I click on Top Right corner toggle to Logout from Admin");
		$i->waitForElement(array('xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"), 60);
		$i->click(array('xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"));
		$i->waitForElement(array('id' => 'mod-login-username'), 60);
		$i->waitForText('Log in', 60, array('xpath' => "//fieldset[@class='loginform']//button"));
	}

	/**
	 * Function to Enable a Plugin
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return void
	 */
	public function enablePlugin($pluginName)
	{
		$i = $this;
		$i->amOnPage('/administrator/index.php?option=com_plugins');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$i->searchForItem($pluginName);
		$i->waitForElement($this->searchResultPluginName($pluginName), 30);
		$i->checkExistenceOf($pluginName);
		$i->click(array('xpath' => "//input[@id='cb0']"));
		$i->click(array('xpath' => "//div[@id='toolbar-publish']/button"));
		$i->see('successfully enabled', array('id' => 'system-message-container'));
	}

	/**
	 * Function to return Path for the Plugin Name to be searched for
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return string
	 */
	private function searchResultPluginName($pluginName)
	{
		$path = "//form[@id='adminForm']/div/table/tbody/tr[1]/td[4]/a[contains(text(), '" . $pluginName . "')]";

		return $path;
	}

	/**
	 * Uninstall Extension based on a name
	 *
	 * @param   string  $extensionName  Is important to use a specific
	 *
	 * @return  void
	 */
	public function uninstallExtension($extensionName)
	{
		$i = $this;
		$i->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$i->waitForText('Extensions: Manage', '30', array('css' => 'H1'));
		$i->searchForItem($extensionName);
		$i->waitForElement(array('id' => 'manageList'), '30');
		$i->click(array('xpath' => "//input[@id='cb0']"));
		$i->click(array('xpath' => "//div[@id='toolbar-delete']/button"));
		$i->acceptPopup();
		$i->waitForText('was successful', '30', array('id' => 'system-message-container'));
		$i->see('was successful', array('id' => 'system-message-container'));
		$i->searchForItem($extensionName);
		$i->waitForText(
			'There are no extensions installed matching your query.',
			60,
			array('class' => 'alert-no-items')
		);
		$i->see('There are no extensions installed matching your query.', array('class' => 'alert-no-items'));
		$this->debug('Extension successfully uninstalled');
	}

	/**
	 * Function to Search For an Item in Joomla! Administrator Lists views
	 *
	 * @param   String  $name  Name of the Item which we need to Search
	 *
	 * @return void
	 */
	public function searchForItem($name = null)
	{
		$i = $this;

		if ($name)
		{
			$i->debug("Searching for $name");
			$i->fillField(array('id' => "filter_search"), $name);
			$i->click(array('xpath' => "//button[@type='submit' and @data-original-title='Search']"));
		}
		else
		{
			$i->debug('clearing search filter');
			$i->click(array('xpath' => "//button[@type='button' and @data-original-title='Clear']"));
		}
	}

	/**
	 * Function to Check of the Item Exist in Search Results in Administrator List.
	 *
	 * note: on long lists of items the item that your are looking for may not appear in the first page. We recommend
	 * the usage of searchForItem method before using the current method.
	 *
	 * @param   String  $name  Name of the Item which we are Searching For
	 *
	 * @return void
	 */
	public function checkExistenceOf($name)
	{
		$i = $this;
		$i->debug("Verifying if $name exist in search result");
		$i->seeElement(array('xpath' => "//form[@id='adminForm']/div/table/tbody"));
		$i->see($name, array('xpath' => "//form[@id='adminForm']/div/table/tbody"));
	}

	/**
	 * Function to select all the item in the Search results in Administrator List
	 *
	 * Note: We recommend use of checkAllResults function only after searchForItem to be sure you are selecting only the desired result set
	 *
	 * @return void
	 */
	public function checkAllResults()
	{
		$i = $this;
		$this->debug("Selecting Checkall button");
		$i->click(array('xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"));
	}

	/**
	 * Function to install a language through the interface
	 *
	 * @param   string  $languageName  Name of the language you want to install
	 *
	 * @return void
	 */
	public function installLanguage($languageName)
	{
		$i = $this;
		$i->amOnPage('administrator/index.php?option=com_installer&view=languages');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->debug('Refreshing languages');
		$i->click(array('xpath' => "//div[@id='toolbar-refresh']/button"));
		$i->waitForElement(array('id' => 'j-main-container'), 30);
		$i->searchForItem($languageName);
		$i->waitForElement($this->searchResultLanguageName($languageName), 30);
		$i->click(array('id' => "cb0"));
		$i->click(array('xpath' => "//div[@id='toolbar-upload']/button"));
		$i->waitForText('was successful.', 60, array('id' => 'system-message-container'));
		$i->see('No Matching Results', array('class' => 'alert-no-items'));
		$this->debug($languageName . ' successfully installed');
	}

	/**
	 * Function to return Path for the Language Name to be searched for
	 *
	 * @param   String  $languageName  Name of the language
	 *
	 * @return string
	 */
	private function searchResultLanguageName($languageName)
	{
		$xpath = "//form[@id='adminForm']/div/table/tbody/tr[1]/td[2]/label[contains(text(),'" . $languageName . "')]";

		return $xpath;
	}

	/**
	 * Publishes a module on frontend in given position
	 *
	 * @param   string  $module    The full name of the module
	 * @param   string  $position  The template position of a module. Right position by default
	 *
	 * @return void
	 */
	public function setModulePosition($module, $position = 'position-7')
	{
		$i = $this;
		$i->amOnPage('administrator/index.php?option=com_modules');
		$i->searchForItem($module);
		$i->click(array('link' => $module));
		$i->waitForText("Modules: $module", 30, array('css' => 'h1.page-title'));
		$i->click(array('link' => 'Module'));
		$i->waitForElement(array('id' => 'general'), 30);
		$i->selectOptionInChosen('Position', $position);
		$i->click(array('xpath' => "//div[@id='toolbar-apply']/button"));
		$i->waitForText('Module successfully saved', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Publishes a module on all frontend pages
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 */
	public function publishModule($module)
	{
		$i = $this;
		$i->amOnPage('administrator/index.php?option=com_modules');
		$i->searchForItem($module);
		$i->checkAllResults();
		$i->click(array('xpath' => "//div[@id='toolbar-publish']/button"));
		$i->waitForText('1 module successfully published.', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Changes the module Menu assignment to be shown on all the pages of the website
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 */
	public function displayModuleOnAllPages($module)
	{
		$i = $this;
		$i->amOnPage('administrator/index.php?option=com_modules');
		$i->searchForItem($module);
		$i->click(array('link' => $module));
		$i->waitForElement(array('link' => 'Menu Assignment'), 30);
		$i->click(array('link' => 'Menu Assignment'));
		$i->waitForElement(array('id' => 'jform_menus-lbl'), 30);
		$i->click(array('id' => 'jform_assignment_chzn'));
		$i->click(array('xpath' => "//li[@data-option-array-index='0']"));
		$i->click(array('xpath' => "//div[@id='toolbar-apply']/button"));
		$i->waitForText('Module successfully saved', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar Panel
	 *
	 * @param   string  $button  The full name of the button
	 *
	 * @return  void
	 */
	public function clickToolbarButton($button)
	{
		$i = $this;
		$input = strtolower($button);

		$screenSize = explode("x", $this->config['window_size']);

		if ($screenSize[0] <= 480)
		{
			$i->click('Toolbar');
		}

		switch ($input)
		{
			case "new":
				$i->click(array('xpath' => "//div[@id='toolbar-new']//button"));
				break;
			case "edit":
				$i->click(array('xpath' => "//div[@id='toolbar-edit']//button"));
				break;
			case "publish":
				$i->click(array('xpath' => "//div[@id='toolbar-publish']//button"));
				break;
			case "unpublish":
				$i->click(array('xpath' => "//div[@id='toolbar-unpublish']//button"));
				break;
			case "archive":
				$i->click(array('xpath' => "//div[@id='toolbar-archive']//button"));
				break;
			case "check-in":
				$i->click(array('xpath' => "//div[@id='toolbar-checkin']//button"));
				break;
			case "batch":
				$i->click(array('xpath' => "//div[@id='toolbar-batch']//button"));
				break;
			case "rebuild":
				$i->click(array('xpath' => "//div[@id='toolbar-refresh']//button"));
				break;
			case "trash":
				$i->click(array('xpath' => "//div[@id='toolbar-trash']//button"));
				break;
			case "save":
				$i->click(array('xpath' => "//div[@id='toolbar-apply']//button"));
				break;
			case "save & close":
				$i->click(array('xpath' => "//div[@id='toolbar-save']//button"));
				break;
			case "save & new":
				$i->click(array('xpath' => "//div[@id='toolbar-save-new']//button"));
				break;
			case "cancel":
				$i->click(array('xpath' => "//div[@id='toolbar-cancel']//button"));
				break;
			case "options":
				$i->click(array('xpath' => "//div[@id='toolbar-options']//button"));
				break;
			case "empty trash":
				$i->click(array('xpath' => "//div[@id='toolbar-delete']//button"));
				break;
		}
	}

	/**
	 * Creates a menu item with the Joomla menu manager, only working for menu items without additional required fields
	 *
	 * @param   string  $menuTitle     The menu item title
	 * @param   string  $menuCategory  The category of the menu type (for example Weblinks)
	 * @param   string  $menuItem      The menu item type / link text (for example List all Web Link Categories)
	 * @param   string  $menu          The menu where the item should be created
	 * @param   string  $language      If you are using Multilingual feature, the language for the menu
	 *
	 * @return  void
	 */
	public function createMenuItem($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
	{
		$i = $this;

		$i->debug("I open the menus page");
		$i->amOnPage('administrator/index.php?option=com_menus&view=menus');
		$i->waitForText('Menus', '60', array('css' => 'H1'));
		$this->checkForPhpNoticesOrWarnings();

		$i->debug("I click in the menu: $menu");
		$i->click(array('link' => $menu));
		$i->waitForText('Menus: Items', '60', array('css' => 'H1'));
		$this->checkForPhpNoticesOrWarnings();

		$i->debug("I click new");
		$i->click("New");
		$i->waitForText('Menus: New Item', '60', array('css' => 'h1'));
		$this->checkForPhpNoticesOrWarnings();
		$i->fillField(array('id' => 'jform_title'), $menuTitle);

		$i->debug("Open the menu types iframe");
		$i->click(array('link' => "Select"));
		$i->waitForElement(array('id' => 'menuTypeModal'), '60');
		$i->wait(1);
		$i->switchToIFrame("Menu Item Type");

		$i->debug("Open the menu category: $menuCategory");

		// Open the category
		$i->wait(1);
		$i->waitForElement(array('link' => $menuCategory), '60');
		$i->click(array('link' => $menuCategory));

		$i->debug("Choose the menu item type: $menuItem");
		$i->wait(1);
		$i->waitForElement(array('xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"), '60');
		$i->click(array('xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"));
		$i->debug('I switch back to the main window');
		$i->switchToIFrame();
		$i->debug('I leave time to the iframe to close');
		$i->wait(2);
		$i->selectOptionInChosen('Language', $language);
		$i->waitForText('Menus: New Item', '30', array('css' => 'h1'));
		$i->debug('I save the menu');
		$i->click("Save");

		$i->waitForText('Menu item successfully saved', '60', array('id' => 'system-message-container'));
	}

	/**
	 * Function to filter results in Joomla! Administrator.
	 *
	 * @param   string  $label  Label of the Filter you want to use.
	 * @param   string  $value  Value you want to set in the filters.
	 *
	 * @return void
	 */
	public function setFilter($label, $value)
	{
		$label = strtolower($label);
		$filters = array(
			"select status" 	=> "filter_published",
			"select access"		=> "filter_access",
			"select language" 	=> "filter_language",
			"select tag"		=> "filter_tag",
			"select max levels"	=> "filter_level"
		);

		$i = $this;
		$i->click(array('xpath' => "//button[@data-original-title='Filter the list items.']"));
		$i->debug('I try to select the filters');

		foreach ($filters as $fieldName => $id)
		{
			if ($fieldName == $label)
			{
				$i->selectOptionInChosenByIdUsingJs($id, $value);
			}
		}

		$i->debug('Applied filters');
	}

	/**
	 * Function to Verify the Tabs on a Joomla! screen
	 *
	 * @param   Array  $expectedTabs  Expected Tabs on the Page
	 * @param   Mixed  $tabsLocator   Locator for the Tabs in Edit View
	 *
	 * @return void
	 */
	public function verifyAvailableTabs($expectedTabs, $tabsLocator = array('xpath' => "//ul[@id='myTabTabs']/li/a"))
	{
		$i = $this;
		$actualArrayOfTabs = $i->grabMultiple($tabsLocator);
		$i->debug("Fetch the current list of Tabs in the edit view which is: " . implode(", ", $actualArrayOfTabs));
		$url = $i->grabFromCurrentUrl();
		$i->assertEquals($expectedTabs, $actualArrayOfTabs, "Tab Labels do not match on edit view of" . $url);
		$i->debug('Verify the Tabs');
	}

	/**
	 * Hide the statistics info message
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return void
	 */
	public function disableStatistics()
	{
		$i = $this;
		$this->debug('I click on never');
		$i->waitForElement(array('link' => 'Never'), 60);
		$i->click(array('link' => 'Never'));
	}
}
