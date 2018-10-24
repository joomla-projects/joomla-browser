<?php
/**
 * @package    JoomlaBrowser
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Codeception\Module;

use Codeception\Module\Locators\Locators;
use Codeception\Lib\ModuleContainer;

const TIMEOUT = 60;

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
		'language'
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
	 * Locator getter
	 *
	 * @param   string  $path  Locator to get
	 *
	 * @return mixed|false
	 * @since  3.8.11
	 */
	public function getLocatorPath($path)
	{
		if (!isset($this->locator->$path))
		{
			return false;
		}

		return $this->locator->$path;
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
	 * @throws  \Exception
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

		$this->debug('I open Joomla Administrator Login Page');
		$this->amOnPage($this->locator->adminLoginPageUrl);

		if ($useSnapshot && $this->loadSessionSnapshot($user))
		{
			return;
		}

		$this->waitForElement($this->locator->adminLoginUserName, TIMEOUT);
		$this->debug('Fill Username Text Field');
		$this->fillField($this->locator->adminLoginUserName, $user);
		$this->debug('Fill Password Text Field');
		$this->fillField($this->locator->adminLoginPassword, $password);

		// @todo: update login button in joomla login screen to make this xPath more friendly
		$this->debug('I click Login button');
		$this->click($this->locator->adminLoginButton);
		$this->debug('I wait to see Administrator Control Panel');
		$this->waitForText($this->locator->adminControlPanelText, 4, $this->locator->controlPanelLocator);

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
	 * @throws  \Exception
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

		$this->waitForElement($this->locator->frontEndLoginSuccess, TIMEOUT);
	}

	/**
	 * Function to Do frontend Logout in Joomla!
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
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
	 * @param   string|null  $databaseName    Optional Database Name. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $databasePrefix  Optional Database Prefix. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function installJoomla($databaseName = null, $databasePrefix = null)
	{
		if (is_null($databaseName))
		{
			$databaseName = $this->config['database name'];
		}

		if (is_null($databasePrefix))
		{
			$databasePrefix = $this->config['database prefix'];
		}

		$this->debug('I open Joomla Installation Configuration Page');
		$this->amOnPage('/installation/index.php');
		$this->debug(
			'I check that FTP tab is not present in installation. Otherwise it means that I have not enough '
			. 'permissions to install joomla and execution will be stopped'
		);
		$this->dontSeeElement(array('id' => 'ftp'));

		// I Wait for the text Main Configuration, meaning that the page is loaded
		$this->debug('I wait for Main Configuration');
		$this->waitForElement('#jform_language', 10);
		$this->debug('Wait for chosen to render the Languages list field');
		$this->wait(2);
		$this->debug('I select es-ES as installation language');

		// Select a random language to force reloading of the lang strings after selecting English
		$this->selectOptionInChosenWithTextField('#jform_language', 'Español (España)');
		$this->waitForText('Configuración principal', TIMEOUT, 'h3');

		// Wait for chosen to render the field
		$this->debug('I select en-GB as installation language');
		$this->debug('Wait for chosen to render the Languages list field');
		$this->wait(2);
		$this->selectOptionInChosenWithTextField('#jform_language', 'English (United Kingdom)');
		$this->waitForText('Main Configuration', TIMEOUT, 'h3');
		$this->debug('I fill Site Name');
		$this->fillField(array('id' => 'jform_site_name'), 'Joomla CMS test');
		$this->debug('I fill Site Description');
		$this->fillField(array('id' => 'jform_site_metadesc'), 'Site for testing Joomla CMS');

		// I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
		$this->debug('I fill Admin Email');
		$this->fillField(array('id' => 'jform_admin_email'), $this->config['admin email']);
		$this->debug('I fill Admin Username');
		$this->fillField(array('id' => 'jform_admin_user'), $this->config['username']);
		$this->debug('I fill Admin Password');
		$this->fillField(array('id' => 'jform_admin_password'), $this->config['password']);
		$this->debug('I fill Admin Password Confirmation');
		$this->fillField(array('id' => 'jform_admin_password2'), $this->config['password']);
		$this->debug('I click Site Offline: no');

		// ['No Site Offline']
		$this->click(array('xpath' => "//fieldset[@id='jform_site_offline']/label[@for='jform_site_offline1']"));
		$this->debug('I click Next');
		$this->click(array('link' => 'Next'));

		$this->debug('I Fill the form for creating the Joomla site Database');
		$this->waitForText('Database Configuration', TIMEOUT, array('css' => 'h3'));

		$this->debug('I select MySQLi');
		$this->selectOption(array('id' => 'jform_db_type'), $this->config['database type']);
		$this->debug('I fill Database Host');
		$this->fillField(array('id' => 'jform_db_host'), $this->config['database host']);
		$this->debug('I fill Database User');
		$this->fillField(array('id' => 'jform_db_user'), $this->config['database user']);
		$this->debug('I fill Database Password');
		$this->fillField(array('id' => 'jform_db_pass'), $this->config['database password']);
		$this->debug('I fill Database Name');
		$this->fillField(array('id' => 'jform_db_name'), $databaseName);
		$this->debug('I fill Database Prefix');
		$this->fillField(array('id' => 'jform_db_prefix'), $databasePrefix);
		$this->debug('I click Remove Old Database ');
		$this->selectOptionInRadioField('Old Database Process', 'Remove');
		$this->debug('I click Next');
		$this->click(array('link' => 'Next'));
		$this->debug('I wait Joomla to remove the old database if exist');
		$this->wait(1);
		$this->waitForElementVisible(array('id' => 'jform_sample_file-lbl'), 30);

		$this->debug('I install joomla with or without sample data');
		$this->waitForText('Finalisation', TIMEOUT, array('xpath' => '//h3'));

		// @todo: installation of sample data needs to be created

		// No sample data
		$this->selectOption(array('id' => 'jform_sample_file'), array('id' => 'jform_sample_file0'));
		$this->click(array('link' => 'Install'));

		// Wait while Joomla gets installed
		$this->debug('I wait for Joomla being installed');
		$this->waitForText('Congratulations! Joomla! is now installed.', TIMEOUT, array('xpath' => '//h3'));
	}

	/**
	 * Install Joomla removing the Installation folder at the end of the execution
	 *
	 * @param   string|null  $databaseName    Optional Database Name. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $databasePrefix  Optional Database Prefix. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function installJoomlaRemovingInstallationFolder($databaseName = null, $databasePrefix = null)
	{
		$this->installJoomla($databaseName, $databasePrefix);

		$this->debug('Removing Installation Folder');
		$this->click(array('xpath' => "//input[@value='Remove \"installation\" folder']"));

		$this->debug('I wait for Removing Installation Folder button to become disabled');
		$this->waitForJS("return jQuery('form#adminForm input[name=instDefault]').attr('disabled') == 'disabled';", TIMEOUT);

		$this->debug('Joomla is now installed');
		$this->see('Congratulations! Joomla! is now installed.', '//h3');
	}

	/**
	 * Installs Joomla with Multilingual Feature active
	 *
	 * @param   array        $languages       Array containing the language names to be installed
	 * @param   string|null  $databaseName    Optional Database Name. If not passed the one in acceptance.suite.yml will be used
	 * @param   string|null  $databasePrefix  Optional Database Prefix. If not passed the one in acceptance.suite.yml will be used
	 *
	 * @example: $this->installJoomlaMultilingualSite(['Spanish', 'French']);
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function installJoomlaMultilingualSite($languages = array(), $databaseName = null, $databasePrefix = null)
	{
		if (!$languages)
		{
			// If no language is passed French will be installed by default
			$languages[] = 'French';
		}

		$this->installJoomla($databaseName, $databasePrefix);

		$this->debug('I go to Install Languages page');
		$this->click(array('id' => 'instLangs'));
		$this->waitForText('Install Language packages', TIMEOUT, array('xpath' => '//h3'));

		foreach ($languages as $language)
		{
			$this->debug('I mark the checkbox of the language: ' . $language);
			$this->click(array('xpath' => "//label[contains(text()[normalize-space()], '$language')]"));
		}

		$this->click(array('link' => 'Next'));
		$this->waitForText('Multilingual', TIMEOUT, array('xpath' => '//h3'));
		$this->selectOptionInRadioField('Activate the multilingual feature', 'Yes');
		$this->waitForElementVisible(array('id' => 'jform_activatePluginLanguageCode-lbl'));
		$this->selectOptionInRadioField('Install localised content', 'Yes');
		$this->selectOptionInRadioField('Enable the language code plugin', 'Yes');
		$this->click(array('link' => 'Next'));

		$this->waitForText('Congratulations! Joomla! is now installed.', TIMEOUT, array('xpath' => '//h3'));
		$this->debug('Removing Installation Folder');
		$this->click(array('xpath' => "//input[@value='Remove \"installation\" folder']"));

		// @todo https://github.com/joomla-projects/joomla-browser/issues/45
		$this->wait(2);

		$this->debug('Joomla is now installed');
		$this->see('Congratulations! Joomla! is now installed.', '//h3');
	}

	/**
	 * Sets in Administrator->Global Configuration the Error reporting to Development
	 * {@internal doAdminLogin() before}
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function setErrorReportingToDevelopment()
	{
		$this->debug('I open Joomla Global Configuration Page');
		$this->amOnPage('/administrator/index.php?option=com_config');
		$this->debug('I wait for Global Configuration title');
		$this->waitForText('Global Configuration', TIMEOUT, array('css' => '.page-title'));
		$this->debug('I open the Server Tab');
		$this->click(array('link' => 'Server'));
		$this->debug('I wait for error reporting dropdown');
		$this->selectOptionInChosen('Error Reporting', 'Development');
		$this->debug('I click on save');
		$this->click(array('xpath' => "//div[@id='toolbar-apply']//button"));
		$this->debug('I wait for global configuration being saved');
		$this->waitForText('Global Configuration', TIMEOUT, array('css' => '.page-title'));
		$this->see('Configuration saved.', '#system-message-container');
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
	 * @throws  \Exception
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
	 * @throws  \Exception
	 */
	public function installExtensionFromFolder($path, $type = 'Extension')
	{
		$this->amOnPage('/administrator/index.php?option=com_installer');
		$this->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$this->click(array('link' => 'Install from Folder'));
		$this->debug('I enter the Path');
		$this->fillField(array('id' => 'install_directory'), $path);
		$this->click(array('id' => 'installbutton_directory'));
		$this->waitForText('was successful', TIMEOUT, array('id' => 'system-message-container'));
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
	 * @throws  \Exception
	 */
	public function installExtensionFromUrl($url, $type = 'Extension')
	{
		$this->amOnPage('/administrator/index.php?option=com_installer');
		$this->waitForText('Extensions: Install', '30', array('css' => 'H1'));
		$this->click(array('link' => 'Install from URL'));
		$this->debug('I enter the url');
		$this->fillField(array('id' => 'install_url'), $url);
		$this->click(array('id' => 'installbutton_url'));
		$this->waitForText('was successful', '30', array('id' => 'system-message-container'));

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
	 * @return  void
	 * @throws  \Exception
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
		$label = $this->findField(array('xpath' => "//label[contains(normalize-space(string(.)), '$label')]"));
		$radioId = $label->getAttribute('for');

		$this->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
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
		$this->click(array('xpath' => "//div[@id='$chosenSelectID']/a/div/b"));
		$this->debug("I select $option");
		$this->click(array('xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"));

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
	 */
	public function selectOptionInChosenWithTextField($label, $option)
	{
		$select = $this->findField($label);
		$selectID = $select->getAttribute('id');
		$chosenSelectID = $selectID . '_chzn';

		$this->debug("I open the $label chosen selector");
		$this->click(array('css' => 'div#' . $chosenSelectID));
		$this->debug("I select $option");
		$this->fillField(array('xpath' => "//div[@id='$chosenSelectID']/div/div/input"), $option);
		$this->click(array('xpath' => "//div[@id='$chosenSelectID']/div/ul/li[1]"));

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
		$this->click(array('xpath' => "//div[@id='$chosenSelectID']/a/div/b"));
		$this->debug("I select $option");
		$this->click(array('xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"));

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
			$this->click(array('xpath' => "//div[@id='$chosenSelectID']/ul"));
			$this->debug("I select $option");
			$this->click(array('xpath' => "//div[@id='$chosenSelectID']//li[contains(text()[normalize-space()], '$option')]"));

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
	 * @throws  \Exception
	 */
	public function doAdministratorLogout()
	{
		$this->click(array('xpath' => "//ul[@class='nav nav-user pull-right']//li//a[@class='dropdown-toggle']"));
		$this->debug("I click on Top Right corner toggle to Logout from Admin");
		$this->waitForElement(array('xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"), TIMEOUT);
		$this->click(array('xpath' => "//li[@class='dropdown open']/ul[@class='dropdown-menu']//a[text() = 'Logout']"));
		$this->waitForElement(array('id' => 'mod-login-username'), TIMEOUT);
		$this->waitForText('Log in', TIMEOUT, array('xpath' => "//fieldset[@class='loginform']//button"));
	}

	/**
	 * Function to Enable a Plugin
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function enablePlugin($pluginName)
	{
		$this->amOnPage('/administrator/index.php?option=com_plugins');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->searchForItem($pluginName);
		$this->waitForElement($this->searchResultPluginName($pluginName), 30);
		$this->checkExistenceOf($pluginName);
		$this->click(array('xpath' => "//input[@id='cb0']"));
		$this->click(array('xpath' => "//div[@id='toolbar-publish']/button"));
		$this->see(' enabled', '#system-message-container');
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
	 * Function to Disable a Plugin
	 *
	 * @param   String  $pluginName  Name of the Plugin
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function disablePlugin($pluginName)
	{
		$this->amOnPage('/administrator/index.php?option=com_plugins');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->searchForItem($pluginName);
		$this->waitForElement($this->searchResultPluginName($pluginName), 30);
		$this->checkExistenceOf($pluginName);
		$this->click(array('xpath' => "//input[@id='cb0']"));
		$this->click(array('xpath' => "//div[@id='toolbar-unpublish']/button"));
		$this->see(' disabled', '#system-message-container');
	}

	/**
	 * Uninstall Extension based on a name
	 *
	 * @param   string  $extensionName  Is important to use a specific
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function uninstallExtension($extensionName)
	{
		$this->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$this->waitForText('Extensions: Manage', '30', array('css' => 'H1'));
		$this->searchForItem($extensionName);
		$this->waitForElement(array('id' => 'manageList'), '30');
		$this->click(array('xpath' => "//input[@id='cb0']"));
		$this->click(array('xpath' => "//div[@id='toolbar-delete']/button"));
		$this->acceptPopup();
		$this->waitForText('was successful', '30', array('id' => 'system-message-container'));
		$this->see('was successful', '#system-message-container');
		$this->searchForItem($extensionName);
		$this->waitForText(
			'There are no extensions installed matching your query.',
			TIMEOUT,
			array('class' => 'alert-no-items')
		);
		$this->see('There are no extensions installed matching your query.', '.alert-no-items');
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
			$this->fillField(array('id' => "filter_search"), $name);
			$this->click(array('xpath' => "//button[@type='submit' and @data-original-title='Search']"));

			return;
		}

		$this->debug('clearing search filter');
		$this->click(array('xpath' => "//button[@type='button' and @data-original-title='Clear']"));
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
		$this->seeElement(array('xpath' => "//form[@id='adminForm']/div/table/tbody"));
		$this->see($name, "//form[@id='adminForm']/div/table/tbody");
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
		$this->click(array('xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"));
	}

	/**
	 * Function to install a language through the interface
	 *
	 * @param   string  $languageName  Name of the language you want to install
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function installLanguage($languageName)
	{
		$this->amOnPage('administrator/index.php?option=com_installer&view=languages');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$this->debug('Refreshing languages');
		$this->click(array('xpath' => "//div[@id='toolbar-refresh']/button"));
		$this->waitForElement(array('id' => 'j-main-container'), 30);
		$this->searchForItem($languageName);
		$this->waitForElement($this->searchResultLanguageName($languageName), 30);
		$this->click(array('id' => "cb0"));
		$this->click(array('xpath' => "//div[@id='toolbar-upload']/button"));
		$this->waitForText('was successful.', TIMEOUT, array('id' => 'system-message-container'));
		$this->see('No Matching Results', '.alert-no-items');
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
	 * @throws  \Exception
	 */
	public function setModulePosition($module, $position = 'position-7')
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->click(array('link' => $module));
		$this->waitForText("Modules: $module", 30, array('css' => 'h1.page-title'));
		$this->click(array('link' => 'Module'));
		$this->waitForElement(array('id' => 'general'), 30);
		$this->selectOptionInChosen('Position', $position);
		$this->click(array('xpath' => "//div[@id='toolbar-apply']/button"));
		$this->waitForText('Module saved', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Publishes a module on all frontend pages
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function publishModule($module)
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->checkAllResults();
		$this->click(array('xpath' => "//div[@id='toolbar-publish']/button"));
		$this->waitForText(' published.', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Changes the module Menu assignment to be shown on all the pages of the website
	 *
	 * @param   string  $module  The full name of the module
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 * @throws  \Exception
	 */
	public function displayModuleOnAllPages($module)
	{
		$this->amOnPage('administrator/index.php?option=com_modules');
		$this->searchForItem($module);
		$this->click(array('link' => $module));
		$this->waitForElement(array('link' => 'Menu Assignment'), 30);
		$this->click(array('link' => 'Menu Assignment'));
		$this->waitForElement(array('id' => 'jform_menus-lbl'), 30);
		$this->click(array('id' => 'jform_assignment_chzn'));
		$this->click(array('xpath' => "//li[@data-option-array-index='0']"));
		$this->click(array('xpath' => "//div[@id='toolbar-apply']/button"));
		$this->waitForText('Module saved', 30, array('id' => 'system-message-container'));
	}

	/**
	 * Function to select Toolbar buttons in Joomla! Admin Toolbar Panel
	 *
	 * @param   string  $button  The full name of the button
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function clickToolbarButton($button)
	{
		$input = strtolower($button);

		$screenSize = explode("x", $this->config['window_size']);

		if ($screenSize[0] <= 480)
		{
			$this->click('Toolbar');
		}

		switch ($input)
		{
			case "new":
				$this->click(array('xpath' => "//div[@id='toolbar-new']//button"));
				break;
			case "edit":
				$this->click(array('xpath' => "//div[@id='toolbar-edit']//button"));
				break;
			case "publish":
				$this->click(array('xpath' => "//div[@id='toolbar-publish']//button"));
				break;
			case "unpublish":
				$this->click(array('xpath' => "//div[@id='toolbar-unpublish']//button"));
				break;
			case "archive":
				$this->click(array('xpath' => "//div[@id='toolbar-archive']//button"));
				break;
			case "check-in":
				$this->click(array('xpath' => "//div[@id='toolbar-checkin']//button"));
				break;
			case "batch":
				$this->click(array('xpath' => "//div[@id='toolbar-batch']//button"));
				break;
			case "rebuild":
				$this->click(array('xpath' => "//div[@id='toolbar-refresh']//button"));
				break;
			case "trash":
				$this->click(array('xpath' => "//div[@id='toolbar-trash']//button"));
				break;
			case "save":
				$this->click(array('xpath' => "//div[@id='toolbar-apply']//button"));
				break;
			case "save & close":
				$this->click(array('xpath' => "//div[@id='toolbar-save']//button"));
				break;
			case "save & new":
				$this->click(array('xpath' => "//div[@id='toolbar-save-new']//button"));
				break;
			case "cancel":
				$this->click(array('xpath' => "//div[@id='toolbar-cancel']//button"));
				break;
			case "options":
				$this->click(array('xpath' => "//div[@id='toolbar-options']//button"));
				break;
			case "empty trash":
				$this->click(array('xpath' => "//div[@id='toolbar-delete']//button"));
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
	 * @throws  \Exception
	 */
	public function createMenuItem($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu', $language = 'All')
	{
		$this->debug("I open the menus page");
		$this->amOnPage('administrator/index.php?option=com_menus&view=menus');
		$this->waitForText('Menus', TIMEOUT, array('css' => 'H1'));
		$this->checkForPhpNoticesOrWarnings();

		$this->debug("I click in the menu: $menu");
		$this->click(array('link' => $menu));
		$this->waitForText('Menus: Items', TIMEOUT, array('css' => 'H1'));
		$this->checkForPhpNoticesOrWarnings();

		$this->debug("I click new");
		$this->click("New");
		$this->waitForText('Menus: New Item', TIMEOUT, array('css' => 'h1'));
		$this->checkForPhpNoticesOrWarnings();
		$this->fillField(array('id' => 'jform_title'), $menuTitle);

		$this->debug("Open the menu types iframe");
		$this->click(array('link' => "Select"));
		$this->waitForElement(array('id' => 'menuTypeModal'), TIMEOUT);
		$this->wait(1);
		$this->switchToIFrame("Menu Item Type");

		$this->debug("Open the menu category: $menuCategory");

		// Open the category
		$this->wait(1);
		$this->waitForElement(array('link' => $menuCategory), TIMEOUT);
		$this->click(array('link' => $menuCategory));

		$this->debug("Choose the menu item type: $menuItem");
		$this->wait(1);
		$this->waitForElement(array('xpath' => "//a[contains(text()[normalize-space()], '$menuItem')]"), TIMEOUT);
		$this->click(array('xpath' => "//div[@id='collapseTypes']//a[contains(text()[normalize-space()], '$menuItem')]"));
		$this->debug('I switch back to the main window');
		$this->switchToIFrame();
		$this->debug('I leave time to the iframe to close');
		$this->wait(2);
		$this->selectOptionInChosen('Language', $language);
		$this->waitForText('Menus: New Item', '30', array('css' => 'h1'));
		$this->debug('I save the menu');
		$this->click("Save");

		$this->waitForText('Menu item saved', TIMEOUT, array('id' => 'system-message-container'));
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

		$this->click(array('xpath' => "//button[@data-original-title='Filter the list items.']"));
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
	public function verifyAvailableTabs($expectedTabs, $tabsLocator = array('xpath' => "//ul[@id='myTabTabs']/li/a"))
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
	 * @throws  \Exception
	 */
	public function disableStatistics()
	{
		$this->debug('I click on never');
		$this->wait(1);
		$this->waitForElement(array('link' => 'Never'), TIMEOUT);
		$this->click(array('link' => 'Never'));
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
	 * @throws  \Exception
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

		$this->waitForElement(array('class' => 'page-title'));
		$this->checkForPhpNoticesOrWarnings();

		$this->debug('Click new category button');
		$this->click($this->locator->adminToolbarButtonNew);

		$this->waitForElement(array('class' => 'page-title'));

		$this->checkForPhpNoticesOrWarnings();
		$this->fillField(array('id' => 'jform_title'), $title);

		$this->debug('Click new category apply button');
		$this->click($this->locator->adminToolbarButtonApply);

		$this->debug('see a success message after saving the category');

		$this->see('Category saved', '#system-message-container');
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
		$this->fillField(array('id' => 'jform_name'), $name);
		$this->fillField(array('id' => 'jform_username'), $username);
		$this->fillField(array('id' => 'jform_password'), $password);
		$this->fillField(array('id' => 'jform_password2'), $password);
		$this->fillField(array('id' => 'jform_email'), $email);

		$this->debug('I open the Assigned User Groups Tab and assign the user group');
		$this->click(array('link' => 'Assigned User Groups'));
		$this->click(array('xpath' => "//label[contains(text()[normalize-space()], '$userGroup')]"));

		$this->debug('Click new user apply button');
		$this->click($this->locator->adminToolbarButtonApply);

		$this->debug('see a success message after saving the user');

		$this->see('User saved', '#system-message-container');
		$this->checkForPhpNoticesOrWarnings();
	}
}
