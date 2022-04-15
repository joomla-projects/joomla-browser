<?php
/**
 * @package    JoomlaBrowser
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Browser;

use Codeception\Module\WebDriver;
use Codeception\Lib\ModuleContainer;
use Exception;
use Joomla\Browser\Locators\Locators;

/**
 * Joomla Browser class to perform test suits for Joomla.
 *
 * @since  1.0
 */
class JoomlaBrowser extends WebDriver
{
	use ElementIsVisibleTrait;

	/**
	 * The module required fields, to be set in the suite .yml configuration file.
	 *
	 * @var     array
	 * @since   3.0.0
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
		'language',
		'timeout'
	);

	/**
	 * The locator
	 *
	 * @var     Locators
	 * @since   3.7.4.2
	 */
	protected $locator;

	/**
	 * Module constructor.
	 *
	 * Requires module container (to provide access between modules of suite) and config.
	 *
	 * @param   ModuleContainer  $moduleContainer  The module container
	 * @param   array            $config           The optional config
	 *
	 * @since   3.7.4.2
	 */
	public function __construct(ModuleContainer $moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);

		// Instantiate the locator
		$this->instantiateLocator();
	}

	/**
	 * Function to instantiate the Locator Class, In case of a custom Template,
	 * path to the custom Template Locator could be passed in Acceptance.suite.yml file
	 *
	 * for example: If the Class is present at _support/Page/Acceptance folder, simple add a new Parameter in acceptance.suite.yml file
	 *
	 * locator class: 'Page\Acceptance\Bootstrap2TemplateLocators'
	 *
	 * Locator could be set to null like this
	 *
	 * locator class: null
	 *
	 * When set to null, Joomla Browser will use the custom Locators present inside Locators.php
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	protected function instantiateLocator()
	{
		if (empty($this->config['locator class']))
		{
			$this->locator = new Locators;

			return;
		}

		$class         = $this->config['locator class'];
		$this->locator = new $class;
	}

	/**
	 * Function to Do Admin Login In Joomla!
	 *
	 * @param   string|null  $user         Optional Username. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $password     Optional password. If not passed the one in acceptance.suite.yml will be used
	 * @param   bool         $useSnapshot  Whether or not you want to reuse the session from previous login. Enabled by default.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function doAdministratorLogin($user = null, $password = null, $useSnapshot = true)
	{
		if (is_null($user))
		{
			$user = $this->config['username'];
		}

		if (is_null($password))
		{
			$password = $this->config['password'];
		}

		if ($useSnapshot && $this->loadSessionSnapshot($user))
		{
			return;
		}

		$this->debug('I open Joomla Administrator Login Page');
		$this->amOnPage($this->locator->adminLoginPageUrl);
		$this->waitForElement($this->locator->adminLoginUserName, $this->config['timeout']);

		// Wait for CSS animations to finish (1 second in scss - give 1.5 to be safe)
		$this->wait(1.5);

		$this->debug('Fill Username Text Field');
		$this->fillField($this->locator->adminLoginUserName, $user);
		$this->debug('Fill Password Text Field');
		$this->fillField($this->locator->adminLoginPassword, $password);

		// Wait for JS to execute
		$this->wait(1.5);

		// @todo: update login button in joomla login screen to make this xPath more friendly
		$this->debug('I click Login button');
		$this->click($this->locator->adminLoginButton);
		$this->debug('I wait to see Administrator Control Panel');
		$this->waitForText($this->locator->adminControlPanelText, $this->config['timeout'], $this->locator->controlPanelLocator);

		if ($useSnapshot)
		{
			$this->saveSessionSnapshot($user);
		}
	}

	/**
	 * Function to Do Frontend Login In Joomla!
	 *
	 * @param   string|null  $user      Optional username. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $password  Optional password. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function doFrontEndLogin($user = null, $password = null)
	{
		if (is_null($user))
		{
			$user = $this->config['username'];
		}

		if (is_null($password))
		{
			$password = $this->config['password'];
		}

		$this->debug('I open Joomla Frontend Login Page');
		$this->amOnPage($this->locator->frontEndLoginUrl);
		$this->debug('Fill Username Text Field');
		$this->fillField($this->locator->loginUserName, $user);
		$this->debug('Fill Password Text Field');
		$this->fillField($this->locator->loginPassword, $password);

		// @todo: update login button in joomla login screen to make this xPath more friendly
		$this->debug('I click Login button');
		$this->click($this->locator->loginButton);
		$this->debug('I wait to see Frontend Member Profile Form with the Logout button in the module');

		$this->waitForElement($this->locator->frontEndLoginSuccess, $this->config['timeout']);
	}

	/**
	 * Function to Do frontend Logout in Joomla!
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function doFrontendLogout()
	{
		$this->debug('I open Joomla Frontend Login Page');
		$this->amOnPage($this->locator->frontEndLoginUrl);
		$this->debug('I click Logout button');
		$this->click($this->locator->frontEndLogoutButton);
		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->debug('I wait to see Login form');
		$this->waitForElement($this->locator->frontEndLoginForm, 30);
		$this->seeElement($this->locator->frontEndLoginForm);
	}

	/**
	 * Installs Joomla
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws Exception
	 */
	public function installJoomla()
	{
		$this->debug('I open Joomla Installation Configuration Page');
		$this->amOnPage('/installation/index.php');
		$this->debug('I check that FTP tab is not present in installation. Otherwise it means that I have not enough '
			. 'permissions to install joomla and execution will be stopped'
		);
		$this->dontSeeElement(['id' => 'ftp']);

		// I Wait for the text Main Configuration, meaning that the page is loaded
		$this->debug('I wait for Main Configuration');
		$this->waitForElement('#jform_language', 10);
		$this->debug('Wait for chosen to render the Languages list field');
		$this->wait(1);
		$this->debug('I select en-GB as installation language');
		$this->selectOption('#jform_language', 'English (United Kingdom)');
		$this->wait(2);
		$this->debug('Wait for the page to reload in the selected language');
		$this->debug('I fill Site Name');
		$this->fillField(['id' => 'jform_site_name'], 'Joomla CMS test');
		$this->click(['id' => 'step1']);

		// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
		$this->debug('I fill Admin Email');
		$this->fillField(['id' => 'jform_admin_email'], $this->config['admin email']);
		$this->debug('I fill Admin Name');
		$this->fillField(['id' => 'jform_admin_user'], $this->config['name']);
		$this->debug('I fill Admin Username');
		$this->fillField(['id' => 'jform_admin_username'], $this->config['username']);
		$this->debug('I fill Admin Password');
		$this->fillField(['id' => 'jform_admin_password'], $this->config['password']);
		$this->click(['id' => "step2"]);

		$this->debug('I Fill the form for creating the Joomla site Database');

		$this->debug('I select MySQLi');
		$this->selectOption(['id' => 'jform_db_type'], $this->config['database type']);
		$this->debug('I fill Database Host');
		$this->fillField(['id' => 'jform_db_host'], $this->config['database host']);
		$this->debug('I fill Database User');
		$this->fillField(['id' => 'jform_db_user'], $this->config['database user']);
		$this->debug('I fill Database Password');
		$this->fillField(['id' => 'jform_db_pass'], $this->config['database password']);
		$this->debug('I fill Database Name');
		$this->fillField(['id' => 'jform_db_name'], $this->config['database name']);
		$this->debug('I fill Database Prefix');
		$this->fillField(['id' => 'jform_db_prefix'], $this->config['database prefix']);
		$this->debug('I click Install Joomla Button');
		$this->click(['id' => 'setupButton']);
		$this->wait(1);
		$this->waitForText('Congratulations! Your Joomla site is ready.', $this->config['timeout'], ['xpath' => '//h2']);
	}

	/**
	 * Install Joomla removing the Installation folder at the end of the execution
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function installJoomlaRemovingInstallationFolder()
	{
		$this->installJoomla();

		if ($this->haveVisible('#removeInstallationFolder'))
		{
			$this->debug('Removing Installation Folder');
			$this->click(['id' => 'removeInstallationFolder']);

			// Accept the confirmation alert
			$this->seeInPopup('Are you sure you want to delete?');
			$this->acceptPopup();

			// Wait until the installation folder is gone and the "customize installation" box has been removed
			$this->waitForElementNotVisible(['id' => 'installAddFeatures']);
		}

		$this->debug('Joomla is now installed');
		$this->click('Open Administrator');
	}

	/**
	 * Installs Joomla with Multilingual Feature active
	 *
	 * @param   array  $languages  Array containing the language names to be installed
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 * @example : $this->installJoomlaMultilingualSite(['Spanish', 'French']);
	 *
	 */
	public function installJoomlaMultilingualSite($languages = array())
	{
		if (!$languages)
		{
			// If no language is passed French will be installed by default
			$languages[] = 'French';
		}

		$this->installJoomla();

		$this->debug('I go to Install Languages page');
		$this->click(['id' => 'instLangs']);
		$this->waitForText('Install Language packages', $this->config['timeout'], ['xpath' => '//h3']);

		foreach ($languages as $language)
		{
			$this->debug('I mark the checkbox of the language: ' . $language);
			$this->click(['xpath' => "//label[contains(text()[normalize-space()], '$language')]"]);
		}

		$this->click(['link' => 'Next']);
		$this->waitForText('Multilingual', $this->config['timeout'], ['xpath' => '//h3']);
		$this->selectOptionInRadioField('Activate the multilingual feature', 'Yes');
		$this->waitForElementVisible(['id' => 'jform_activatePluginLanguageCode-lbl']);
		$this->selectOptionInRadioField('Install localised content', 'Yes');
		$this->selectOptionInRadioField('Enable the language code plugin', 'Yes');
		$this->click(['link' => 'Next']);

		$this->waitForText('Congratulations! Joomla! is now installed.', $this->config['timeout'], ['xpath' => '//h2']);

		if ($this->haveVisible('#removeInstallationFolder'))
		{
			$this->debug('Removing Installation Folder');
			$this->click(['xpath' => "//input[@value='Remove \"installation\" folder']"]);

			// Wait until the installation folder is gone and the "customize installation" box has been removed
			$this->waitForElementNotVisible(['id' => 'installAddFeatures']);
		}

		$this->debug('Joomla is now installed');
		$this->see('Congratulations! Joomla! is now installed.', ['xpath' => '//h2']);
	}

	/**
	 * Sets in Administrator->Global Configuration the Error reporting to Maximum (formerly development)
	 * {@internal doAdminLogin() before}
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function setErrorReportingToDevelopment()
	{
		$this->debug('I open Joomla Global Configuration Page');
		$this->amOnPage('/administrator/index.php?option=com_config');
		$this->debug('I wait for Global Configuration title');
		$this->waitForText('Global Configuration', $this->config['timeout'], ['css' => '.page-title']);
		$this->debug('I open the Server Tab');

		// TODO improve
		$this->wait(1);
		$this->click($this->locator->adminConfigurationServerTab);
		$this->debug('I wait for error reporting dropdown');
		$this->selectOption('Error Reporting', 'Maximum');
		$this->debug('I click on save');
		$this->clickToolbarButton('save');
		$this->debug('I wait for global configuration being saved');
		$this->waitForText('Global Configuration', $this->config['timeout'], ['css' => '.page-title']);
		$this->waitForText('Configuration saved.', $this->config['timeout'], ['id' => 'system-message-container']);
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
	 * @return   void
	 *
	 * @since    3.0.0
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
	 *
	 * @since    3.0.0
	 * @throws Exception
	 */
	public function installExtensionFromFolder($path, $type = 'Extension')
	{
		$this->amOnPage('/administrator/index.php?option=com_installer');
		$this->waitForText('Extensions: Install', '30', ['css' => 'H1']);
		$this->click(['link' => 'Install from Folder']);
		$this->debug('I enter the Path');
		$this->fillField(['id' => 'install_directory'], $path);
		$this->click(['id' => 'installbutton_directory']);
		$this->waitForText('was successful', $this->config['timeout'], ['id' => 'system-message-container']);
		$this->debug("$type successfully installed from $path");
	}

	/**
	 * Installs a Extension in Joomla that is located in a url
	 *
	 * @param   String  $url   Url address to the .zip file
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 *
	 * @since    3.0.0
	 * @throws Exception
	 */
	public function installExtensionFromUrl($url, $type = 'Extension')
	{
		$this->amOnPage('/administrator/index.php?option=com_installer');
		$this->waitForText('Extensions: Install', '30', ['css' => 'H1']);
		$this->wait(1);
		$this->waitForElementNotVisible(['xpath' => '//joomla-core-loader']);
		$this->click(['link' => 'Install from URL']);
		$this->debug('I enter the url');
		$this->fillField(['id' => 'install_url'], $url);
		$this->click(['id' => 'installbutton_url']);
		$this->waitForText('was successful', '30', ['id' => 'system-message-container']);

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
	 * @param   string  $file  Path to the file in the _data folder
	 * @param   string  $type  Type of Extension
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return    void
	 * @throws Exception
	 */
	public function installExtensionFromFileUpload($file, $type = 'Extension')
	{
		$this->amOnPage('/administrator/index.php?option=com_installer');
		$this->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$this->click(array('link' => 'Upload Package File'));

		$this->debug('I make sure legacy uploader is visible');
		$this->executeJS('document.getElementById("legacy-uploader").style.display="block";');

		$this->debug('I enter the file input');
		$this->attachFile(array('id' => 'install_package'), $file);

		$this->waitForText('was successful', '30', array('id' => 'system-message-container'));

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
	 *
	 * @since    3.0.0
	 */
	public function checkForPhpNoticesOrWarnings($page = null)
	{
		if ($page)
		{
			$this->amOnPage($page);
		}

		$this->dontSeeInPageSource('Deprecated:');
		$this->dontSeeInPageSource('<b>Deprecated</b>:');
		$this->dontSeeInPageSource('Notice:');
		$this->dontSeeInPageSource('<b>Notice</b>:');
		$this->dontSeeInPageSource('Warning:');
		$this->dontSeeInPageSource('<b>Warning</b>:');
		$this->dontSeeInPageSource('Strict standards:');
		$this->dontSeeInPageSource('<b>Strict standards</b>:');
		$this->dontSeeInPageSource('The requested page can\'t be found');
	}

	/**
	 * Selects an option in a Joomla Radio Field based on its label
	 *
	 * @param   string  $label   The text in the <label> with for attribute that links to the radio element
	 * @param   string  $option  The text in the <option> to be selected in the chosen radio button
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function selectOptionInRadioField($label, $option)
	{
		$this->debug("Trying to select the $option from the $label");
		$label = $this->findField(['xpath' => "//label[contains(normalize-space(string(.)), '$label')]"]);
		$radioId = $label->getAttribute('for');

		$this->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
	}

	/**
	 * Selects an option of a switcher radio field with check if the field is already selected
	 *
	 * @param   string  $selector  Selector of the <input type="radio"/> field
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function selectOptionInSwitcherRadioField($selector)
	{
		$isChecked = $this->grabAttributeFrom($selector, 'checked');

		if ($isChecked == 'true')
		{
			$this->debug('The value seems to already be checked.');
		}
		else
		{
			$this->click($selector);
		}
	}

	/**
	 * Selects an option in a Chosen Selector based on its label
	 *
	 * @param   string  $label   The text in the <label> with for attribute that links to the <select> element
	 * @param   string  $option  The text in the <option> to be selected in the chosen selector
	 *
	 * @return  void
	 *
	 * @since    3.0.0
	 */
	public function selectOptionInChosen($label, $option)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';

		$this->debug("I open the $label chosen selector");
		$this->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
		$this->debug("I select $option");
		$this->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);

		// Gives time to chosen to close
		$this->wait(1);
	}

	/**
	 * Selects an option in a Chosen Selector based on its label with filling the textfield
	 *
	 * @param   string  $label   The text in the <label> with for attribute that links to the <select> element
	 * @param   string  $option  The text in the <option> to be selected in the chosen selector
	 *
	 * @return  void
	 *
	 * @since    3.0.0
	 *
	 * @deprecated  4.0.0
	 */
	public function selectOptionInChosenWithTextField($label, $option)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';

		$this->debug("I open the $label chosen selector");
		$this->click(['css' => 'div#' . $chosenSelectID]);
		$this->debug("I select $option");
		$this->fillField(['xpath' => "//div[@id='$chosenSelectID']/div/div/input"], $option);
		$this->click(['xpath' => "//div[@id='$chosenSelectID']/div/ul/li[1]"]);

		// Gives time to chosen to close
		$this->wait(1);
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

		$this->debug("I open the $chosenSelectID chosen selector");
		$this->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
		$this->debug("I select $option");
		$this->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);

		// Gives time to chosen to close
		$this->wait(1);
	}

	/**
	 * Selects an option in a Chosen Selector based on its id
	 *
	 * @param   string  $selectId  The id of the <select> element
	 * @param   string  $option    The text in the <option> to be selected in the chosen selector
	 *
	 * @return  void
	 *
	 * @since    3.0.0
	 */
	public function selectOptionInChosenByIdUsingJs($selectId, $option)
	{
		$option = trim($option);
		$this->executeJS("jQuery('#$selectId option').filter(function(){ return this.text.trim() === \"$option\" }).prop('selected', true);");
		$this->executeJS("jQuery('#$selectId').trigger('liszt:updated').trigger('chosen:updated');");
		$this->executeJS("jQuery('#$selectId').trigger('change');");

		// Give time to Chosen to update
		$this->wait(1);
	}

	/**
	 * Selects one or more options in a Chosen Multiple Select based on its label
	 *
	 * @param   string  $label    Label of the select field
	 * @param   array   $options  Array of options to be selected
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function selectMultipleOptionsInChosen($label, $options)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';

		foreach ($options as $option)
		{
			$this->debug("I open the $label chosen selector");
			$this->click(['xpath' => "//div[@id='$chosenSelectID']/ul"]);
			$this->debug("I select $option");
			$this->click(['xpath' => "//div[@id='$chosenSelectID']//li[contains(text()[normalize-space()], '$option')]"]);

			// Gives time to chosen to close
			$this->wait(1);
		}
	}

	/**
	 * Function to Logout from Administrator Panel in Joomla!
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function doAdministratorLogout()
	{
		$this->click($this->locator->adminLogoutDropdown);
		$this->debug("I click on Top Right corner toggle to Logout from Admin");
		$this->click($this->locator->adminLogoutText);
		$this->waitForElement($this->locator->adminLoginUserName, $this->config['timeout']);
		$this->waitForText($this->locator->adminLoginText, $this->config['timeout'], $this->locator->adminLoginSubmitButton);
	}

	/**
	 * Function to Enable a Plugin
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function enablePlugin($pluginName)
	{
		$this->amOnPage('/administrator/index.php?option=com_plugins');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->searchForItem($pluginName);
		$this->waitForElement($this->searchResultPluginName($pluginName), 30);
		$this->checkExistenceOf($pluginName);
		$this->click(['xpath' => "//input[@id='cb0']"]);
		$this->click(['xpath' => "//div[@id='toolbar-publish']/button"]);
		$this->waitForText(' enabled', $this->config['timeout'], ['id' => 'system-message-container']);
	}

	/**
	 * Function to return Path for the Plugin Name to be searched for
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return  string
	 *
	 * @since   3.0.0
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
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function uninstallExtension($extensionName)
	{
		$this->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$this->waitForText('Extensions: Manage', '30', ['css' => 'H1']);
		$this->searchForItem($extensionName);
		$this->waitForElement(['id' => 'manageList'], '30');
		$this->click(['xpath' => "//input[@id='cb0']"]);
		$this->click(['xpath' => "//joomla-toolbar-button[@id='toolbar-delete']/button"]);
		$this->acceptPopup();
		$this->waitForText('was successful', '30', ['id' => 'system-message-container']);
		$this->see('was successful', ['id' => 'system-message-container']);
		$this->debug('I check for warnings during the uninstall process');
		$this->dontSeeElement(['xpath' => "//joomla-alert[@type='warning']"]);
		$this->searchForItem($extensionName);
		$this->waitForText(
			'No Matching Results',
			$this->config['timeout'],
			['class' => 'alert-info']
		);
		$this->see('No Matching Results', ['class' => 'alert-info']);
		$this->debug('Extension successfully uninstalled');
	}

	/**
	 * Function to Search For an Item in Joomla! Administrator Lists views
	 *
	 * @param   String  $name  Name of the Item which we need to Search
	 *
	 * @return void
	 *
	 * @since   3.0.0
	 */
	public function searchForItem($name = null)
	{
		if ($name)
		{
			$this->debug("Searching for $name");
			$this->fillField(['id' => "filter_search"], $name);
			$this->click(['xpath' => "//button[@aria-label='Search']"]);

			return;
		}

		$this->debug('clearing search filter');
		$this->click('Clear', ['xpath' => "//button[@type='button']"]);
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
		$this->debug("Verifying if $name exist in search result");
		$this->seeElement(['xpath' => "//form[@id='adminForm']/div/table/tbody"]);
		$this->see($name, ['xpath' => "//form[@id='adminForm']/div/table/tbody"]);
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
		$this->debug("Selecting Checkall button");
		$this->click(['xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"]);
	}

	/**
	 * Function to install a language through the interface
	 *
	 * @param   string  $languageName  Name of the language you want to install
	 *
	 * @return void
	 * @throws Exception
	 */
	public function installLanguage($languageName)
	{
		$this->amOnPage('administrator/index.php?option=com_installer&view=languages');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->debug('Refreshing languages');
		$this->click(['xpath' => "//div[@id='toolbar-refresh']/button"]);
		$this->waitForElement(['id' => 'j-main-container'], 30);
		$this->searchForItem($languageName);
		$this->waitForElement($this->searchResultLanguageName($languageName), 30);
		$this->click(['id' => "cb0"]);
		$this->click(['xpath' => "//div[@id='toolbar-upload']/button"]);
		$this->waitForText('was successful.', $this->config['timeout'], ['id' => 'system-message-container']);
		$this->see('No Matching Results', ['class' => 'alert-no-items']);
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
	 * @throws Exception
	 */
	public function setModulePosition($module, $position = 'position-7')
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->click(['link' => $module]);
		$this->waitForText("Modules: $module", 30, ['css' => 'h1.page-title']);
		$this->click(['link' => 'Module']);
		$this->waitForElement(['id' => 'general'], 30);
		$this->selectOptionInChosen('Position', $position);
		$this->click(['xpath' => "//div[@id='save-group-children-apply']/button"]);
		$this->waitForText('Module saved', 30, ['id' => 'system-message-container']);
	}

	/**
	 * Publishes a module on all frontend pages
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function publishModule($module)
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->checkAllResults();
		$this->click(['xpath' => "//div[@id='toolbar-publish']/button"]);
		$this->waitForText(' published.', 30, ['id' => 'system-message-container']);
	}

	/**
	 * Changes the module Menu assignment to be shown on all the pages of the website
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function displayModuleOnAllPages($module)
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->click(['link' => $module]);
		$this->waitForElement(['link' => 'Menu Assignment'], 30);
		$this->click(['link' => 'Menu Assignment']);
		$this->waitForElement(['id' => 'jform_menus-lbl'], 30);
		$this->click(['id' => 'jform_assignment_chzn']);
		$this->click(['xpath' => "//li[@data-option-array-index='0']"]);
		$this->click(['xpath' => "//div[@id='save-group-children-apply']/button"]);
		$this->waitForText('Module saved', 30, ['id' => 'system-message-container']);
	}

	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar Panel
	 *
	 * @param   string  $button       The full name of the button
	 * @param   string  $subselector  Subselector to further define the button
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function clickToolbarButton($button, $subselector = null)
	{
		$input = strtolower($button);

		switch ($input)
		{
			case "new":
				$this->click("//button[contains(@class, 'button-new')]");
				break;
			case "publish":
				$this->click("#status-group-children-publish");
				break;
			case "unpublish":
				$this->click("#status-group-children-unpublish");
				break;
			case "archive":
				$this->click("#status-group-children-archive");
				break;
			case "check-in":
				$this->click("#status-group-children-checkin");
				break;
			case "batch":
				$this->click("#status-group-children-batch");
				break;
			case "rebuild":
				$this->click(['xpath' => "//div[@id='toolbar-refresh']//button"]);
				break;
			case "trash":
				$this->click("#status-group-children-trash");
				break;
			case "save":
				$this->click(['id' => "toolbar-apply"]);
				break;
			case "save & close":
				$this->click(['id' => "save-group-children-save"]);
				break;
			case "save & new":
				$this->click(['id' => "save-group-children-save-new"]);
				break;
			case "cancel":
				$this->click(['id' => "toolbar-cancel"]);
				break;
			case "options":
				$this->click(['id' => "toolbar-options"]);
				break;
			case "empty trash":
				$this->click(['id' => "toolbar-delete"]);
				break;
			case "feature":
				$this->click("#status-group-children-featured");
				break;
			case "unfeature":
				$this->click("#status-group-children-unfeatured");
				break;
			case "action":
				$this->click(['id' => "toolbar-status-group"]);
				break;
			case "transition":
				$this->click(".button-transition.transition-" . $subselector);
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
	 *
	 * @since   3.0.0
	 * @throws Exception
	 */
	public function createMenuItem($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
	{
		$this->debug("I open the menus page");
		$this->amOnPage('administrator/index.php?option=com_menus&view=menus');
		$this->waitForText('Menus', $this->config['timeout'], ['css' => 'H1']);
		$this->checkForPhpNoticesOrWarnings();

		$this->debug("I click in the menu: $menu");
		$this->click(['link' => $menu]);
		$this->waitForText('Menus: Items', $this->config['timeout'], ['css' => 'H1']);
		$this->checkForPhpNoticesOrWarnings();

		$this->debug("I click new");
		$this->click("New");
		$this->waitForText('Menus: New Item', $this->config['timeout'], ['css' => 'h1']);
		$this->checkForPhpNoticesOrWarnings();
		$this->fillField(['id' => 'jform_title'], $menuTitle);

		$this->debug("Open the menu types iframe");
		$this->click(['link' => "Select"]);
		$this->waitForElement(['id' => 'menuTypeModal'], $this->config['timeout']);
		$this->wait(1);
		$this->switchToIFrame("Menu Item Type");

		$this->debug("Open the menu category: $menuCategory");

		// Open the category
		$this->wait(1);
		$this->waitForElement(['link' => $menuCategory], $this->config['timeout']);
		$this->click(['link' => $menuCategory]);

		$this->debug("Choose the menu item type: $menuItem");
		$this->wait(1);
		$this->waitForElement(['xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"], $this->config['timeout']);
		$this->click(['xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"]);
		$this->debug('I switch back to the main window');
		$this->switchToIFrame();
		$this->debug('I leave time to the iframe to close');
		$this->wait(2);
		$this->selectOptionInChosen('Language', $language);
		$this->waitForText('Menus: New Item', '30', ['css' => 'h1']);
		$this->debug('I save the menu');
		$this->click("Save");

		$this->waitForText('Menu item successfully saved', $this->config['timeout'], ['id' => 'system-message-container']);
	}

	/**
	 * Function to filter results in Joomla! Administrator.
	 *
	 * @param   string  $label  Label of the Filter you want to use.
	 * @param   string  $value  Value you want to set in the filters.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
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

		$this->click(['xpath' => "//button[@data-original-title='Filter the list items.']"]);
		$this->debug('I try to select the filters');

		foreach ($filters as $fieldName => $id)
		{
			if ($fieldName == $label)
			{
				$this->selectOptionInChosenByIdUsingJs($id, $value);
			}
		}

		$this->debug('Applied filters');
	}

	/**
	 * Function to Verify the Tabs on a Joomla! screen
	 *
	 * @param   array  $expectedTabs  Expected Tabs on the Page
	 * @param   Mixed  $tabsLocator   Locator for the Tabs in Edit View
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function verifyAvailableTabs($expectedTabs, $tabsLocator = ['xpath' => "//ul[@id='myTabTabs']/li/a"])
	{
		$actualArrayOfTabs = $this->grabMultiple($tabsLocator);

		$this->debug("Fetch the current list of Tabs in the edit view which is: " . implode(", ", $actualArrayOfTabs));
		$url = $this->grabFromCurrentUrl();
		$this->assertEquals($expectedTabs, $actualArrayOfTabs, "Tab Labels do not match on edit view of" . $url);
		$this->debug('Verify the Tabs');
	}

	/**
	 * Hide the statistics info message
	 *
	 * {@internal doAdminLogin() before}
	 *
	 * @return  void
	 *
	 * @since   3.5.0
	 * @throws Exception
	 */
	public function disableStatistics()
	{
		$this->debug('I click on never');
		$this->wait(1);
		$this->waitForElement('.js-pstats-btn-allow-never', $this->config['timeout']);
		$this->click('.js-pstats-btn-allow-never');

		// TODO improve, this needs to wait until the ajax call is over
		$this->wait(1);
	}

	/**
	 * Create a new category
	 *
	 * @param   string  $title      Title of the new category
	 * @param   string  $extension  Optional extension to use
	 *
	 * @return  void
	 *
	 * @since   3.7.5
	 * @throws Exception
	 */
	public function createCategory($title, $extension = '')
	{
		$this->debug('Category creation in /administrator/');
		$this->doAdministratorLogin();

		if (!empty($extension))
		{
			$extension = '&extension=' . $extension;
		}

		$this->amOnPage('administrator/index.php?option=com_categories' . $extension);

		$this->waitForElement(['class' => 'page-title']);
		$this->checkForPhpNoticesOrWarnings();

		$this->debug('Click new category button');
		$this->click($this->locator->adminToolbarButtonNew);

		$this->waitForElement(['class' => 'page-title']);

		$this->checkForPhpNoticesOrWarnings();
		$this->fillField(['id' => 'jform_title'], $title);

		$this->debug('Click new category apply button');
		$this->click($this->locator->adminToolbarButtonApply);

		$this->debug('see a success message after saving the category');

		$this->waitForText('Category saved', $this->config['timeout'], ['id' => 'system-message-container']);
		$this->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Create a user in the administrator site
	 *
	 * @param   string  $name       Name
	 * @param   string  $username   User name (login)
	 * @param   string  $password   Password
	 * @param   string  $email      Email
	 * @param   string  $userGroup  Group id to attach to the user
	 *
	 * @return  void
	 *
	 * @since   3.8.11
	 * @throws  \Exception
	 */
	public function createUser($name, $username, $password, $email, $userGroup = 'Super Users')
	{
		$this->debug('User creation');
		$this->doAdministratorLogin();

		$this->amOnPage('administrator/index.php?option=com_users');

		$this->waitForElement(array('class' => 'page-title'));
		$this->checkForPhpNoticesOrWarnings();

		$this->debug('Click new user button');
		$this->click($this->locator->adminToolbarButtonNew);

		$this->checkForPhpNoticesOrWarnings();
		$this->debug('I fill up the new user information');

		$this->click($this->locator->adminManageUsersAccountDetailsTab);
		$this->fillField(array('id' => 'jform_name'), $name);
		$this->fillField(array('id' => 'jform_username'), $username);
		$this->fillField(array('id' => 'jform_password'), $password);
		$this->fillField(array('id' => 'jform_password2'), $password);
		$this->fillField(array('id' => 'jform_email'), $email);

		if (!empty($userGroup))
		{
			$this->debug('I open the Assigned User Groups Tab and assign the user group');
			$this->click($this->locator->adminManageUsersUserGroupAssignmentTab);
			$this->click($this->locator->adminManageUsersUserGroupAssignmentCheckbox($userGroup));
		}

		$this->debug('Click new user apply button');
		$this->click($this->locator->adminToolbarButtonApply);

		$this->debug('see a success message after saving the user');
		$this->waitForText('User saved', $this->config['timeout'], '#system-message-container');
		$this->see('User saved', '#system-message-container');
		$this->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Site Offline
	 *
	 * @param   boolean  $enable  To set Site Online/Offline
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setSiteOffline($enable = true)
	{
		$this->debug($enable ? 'I set the site to be offline' : 'I set the site to be online');
		$this->amOnPage(Locators::$globalConfigurationUrl);
		$this->click($this->locator->adminConfigurationSiteTab);
		$this->selectOptionInSwitcherRadioField($enable ? '#jform_offline1' : '#jform_offline0');

		$this->click("Save");
		$this->see('Configuration saved', '#system-message-container');
	}

	/**
	 * Search Engine Optimization
	 *
	 * @param   boolean  $enable  To set SEO true/false
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setSiteSearchEngineFriendly($enable = true)
	{
		$this->debug($enable ? 'I set SEF URLs to Yes' : 'I set SEF URLs to No');
		$this->amOnPage(Locators::$globalConfigurationUrl);
		$this->click($this->locator->adminConfigurationSiteTab);
		$this->scrollTo('#jform_sef');
		$this->selectOptionInSwitcherRadioField($enable ? '#jform_sef1' : '#jform_sef0');

		$this->click("Save");
		$this->see('Configuration saved', '#system-message-container');
	}

	/**
	 * Create Module
	 *
	 * @param   string             $moduleType  Type of module
	 * @param   string             $moduleName  Name of module
	 * @param   string             $category    category of module
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function createModule($moduleType, $moduleName, $category)
	{
		$this->amOnPage(Locators::$moduleUrl);

		// New Module
		$this->click('New');

		// Select Module Type
		$this->see('Select a Module Type');

		// Module Type
		$this->click($moduleType, '.new-modules');

		// Title
		$this->fillField(Locators::$moduleTitle, $moduleName);

		$this->click('#jform_position-lbl');
		$this->type('Main-top');
		$this->pressKey('.choices__input.choices__input--cloned', \Facebook\WebDriver\WebDriverKeys::ENTER);

		/**
		 * Module Types
		 * Articles - Archived
		 * Articles - Categories
		 * Articles - Category
		 * Banners
		 * Breadcrumbs
		 * Articles - Most Read
		 * Articles - Newsflash
		 * Articles - Related
		 * Custom
		 * Feed Display
		 * Latest Users
		 * Login
		 * Search
		 * Smart Search
		 * Language Switcher
		 * Menu
		 * Statistics
		 * Syndication Feeds
		 * Tags - Popular
		 */
		switch ($moduleType)
		{
			case 'Articles - Archived' :
				break;
			case 'Articles - Categories' :
				// Select category as 'Google Summer Of Codes'
				$this->click(['id' => 'jform_params_parent_select']);
				$this->switchToIFrame('Select or Change Category');
				$this->wait(1);
				$this->searchForItem($category);
				$this->wait(1);
				$this->see($category);
				$this->click(['link' => $category]);
				$this->switchToPreviousTab();
				$this->wait(1);
				break;

			case 'Articles - Category' :
				// Filter Options
				$this->click(['link' => 'Filtering Options']);
				$this->selectOption(Locators::$selectModuleCategory, $category);
				break;
			case 'Articles - Latest' :
				$this->scrollTo(Locators::$selectModuleCategory, $category);
				$this->selectOption(Locators::$selectModuleCategory, $category);
				break;
			case 'Articles - Most Read' :
				$this->scrollTo(Locators::$selectModuleCategory, $category);
				$this->selectOption(Locators::$selectModuleCategory, $category);
				break;
			case 'Articles - Newsflash' :
				$this->click(Locators::$moduleCategory);
				$this->fillField(Locators::$fillModuleCategory, $category);
				$this->pressKey(Locators::$fillModuleCategory, \Facebook\WebDriver\WebDriverKeys::ENTER);
				break;
			case 'Articles - Related' :
				break;
			case 'Banners' :
				// Select category 'Uncategorised'
				$this->scrollTo(['id' => 'jform_params_catid'], 'Uncategorised');
				$this->selectOption(['id' => 'jform_params_catid'], 'Uncategorised');
				$this->scrollTo(['id' => 'jform_params_header_text']);
				$this->fillField(['id' => 'jform_params_header_text'], 'This is text header for Module of Banners');
				$this->scrollTo(['id' => 'jform_params_footer_text']);
				$this->fillField(['id' => 'jform_params_footer_text'], 'This is text footer for Module of Banners');
				break;
			case 'Breadcrumbs' :
				$this->fillField(['id' => 'jform_params_homeText'], 'Home entry for Module of breadcrumbs');
				break;
			case 'Custom' :
				break;
			case 'Feed Display' :
				$this->fillField(['id' => 'jform_params_rssurl'], 'https://www.joomla.org');
				break;
			case 'Language Switcher' :
				$this->fillField(['id' => 'jform_params_header_text'], 'This is pre-text for Module');
				$this->fillField(['id' => 'jform_params_footer_text'], 'This is post-text for Module');
				$this->click(['id' => 'jform_params_dropdown1']);
				break;
			case 'Latest Users' :
				break;
			case 'Login' :
				$this->fillField(['id' => 'jform_params_pretext'], 'This is pre-text for Module');
				$this->fillField(['id' => 'jform_params_posttext'], 'This is post-text for Module');
				$this->fillField(['id' => 'jform_params_login'], 'http://localhost/joomlaMain/test-install/');
				$this->fillField(['id' => 'jform_params_logout'], 'https://www.joomla.org');
				$this->scrollTo(['id' => 'jform_params_name1']);
				$this->click(['id' => 'jform_params_name1']);
				break;
		}

		// Save It
		$this->clickToolbarButton('save & close');
		$this->see('Module saved', ['id' => 'system-message-container']);
		$this->searchForItem($moduleName);
	}
}
