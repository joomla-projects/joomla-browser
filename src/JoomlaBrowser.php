<?php

namespace Codeception\Module;

use Codeception\Module\WebDriver;

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
        'language',

    );

    /**
     * Function to Do Admin Login In Joomla!
     */
    public function doAdministratorLogin()
    {
        $I = $this;
        $this->debug('I open Joomla Administrator Login Page');
        $I->amOnPage('/administrator/index.php');
        $this->debug('Fill Username Text Field');
        $I->fillField(['id' => 'mod-login-username'], $this->config['username']);
        $this->debug('Fill Password Text Field');
        $I->fillField(['id' => 'mod-login-password'], $this->config['password']);
        // @todo: update login button in joomla login screen to make this xPath more friendly
        $this->debug('I click Login button');
        $I->click(['xpath' => "//button[contains(normalize-space(), 'Log in')]"]);
        $this->debug('I wait to see Administrator Control Panel');
        $I->waitForText('Control Panel', 4, ['css' => 'h1.page-title']);
    }

    /**
     * Function to Do frontend Login in Joomla!
     */
    public function doFrontEndLogin()
    {
        $I = $this;
        $this->debug('I open Joomla Frontend Login Page');
        $I->amOnPage('/index.php?option=com_users&view=login');
        $this->debug('Fill Username Text Field');
        $I->fillField(['id' => 'username'], $this->config['username']);
        $this->debug('Fill Password Text Field');
        $I->fillField(['id' => 'password'], $this->config['password']);
        // @todo: update login button in joomla login screen to make this xPath more friendly
        $this->debug('I click Login button');
        $I->click(['xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button"]);
        $this->debug('I wait to see Frontend Member Profile Form');
        $I->waitForElement(['xpath' => "//input[@value='Log out']"], 10);
    }

    /**
     * Installs Joomla
     */
    public function installJoomla()
    {
        $I = $this;

        // Install Joomla CMS');

        // @todo: activate the filesystem module
        //$I->expect('no configuration.php is in the Joomla CMS folder');
        //$I->dontSeeFileFound('configuration.php', $this->config['Joomla folder')];
        $this->debug('I open Joomla Installation Configuration Page');
        $I->amOnPage('/installation/index.php');
        $this->debug('I check that FTP tab is not present in installation. Otherwise it means that I have not enough permissions to install joomla and execution will be stoped');
        $I->dontSeeElement(['id' => 'ftp']);
        // I Wait for the text Main Configuration, meaning that the page is loaded
        $this->debug('I wait for Main Configuration');
        $I->waitForElement('#jform_language', 10);

        $I->debug('I select en-GB as installation language');
        $I->selectOptionInChosen('#jform_language', 'English (United Kingdom)');
        $this->debug('I fill Site Name');
        $I->fillField(['id' => 'jform_site_name'], 'Joomla CMS test');
        $this->debug('I fill Site Description');
        $I->fillField(['id' => 'jform_site_metadesc'], 'Site for testing Joomla CMS');

        // I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
        $this->debug('I fill Admin Email');
        $I->fillField(['id' => 'jform_admin_email'], $this->config['admin email']);
        $this->debug('I fill Admin Username');
        $I->fillField(['id' => 'jform_admin_user'], $this->config['username']);
        $this->debug('I fill Admin Password');
        $I->fillField(['id' => 'jform_admin_password'], $this->config['password']);
        $this->debug('I fill Admin Password Confirmation');
        $I->fillField(['id' => 'jform_admin_password2'], $this->config['password']);
        $this->debug('I click Site Offline: no');
        $I->click(['xpath' => "//fieldset[@id='jform_site_offline']/label[2]"]); // ['No Site Offline']
        $this->debug('I click Next');
        $I->click(['link' => 'Next']);

        $this->debug('I Fill the form for creating the Joomla site Database');
        $I->waitForText('Database Configuration', 10,['css' => 'h3']);

        $this->debug('I select MySQLi');
        $I->selectOption(['id' => 'jform_db_type'], $this->config['database type']);
        $this->debug('I fill Database Host');
        $I->fillField(['id' => 'jform_db_host'], $this->config['database host']);
        $this->debug('I fill Database User');
        $I->fillField(['id' => 'jform_db_user'], $this->config['database user']);
        $this->debug('I fill Database Password');
        $I->fillField(['id' => 'jform_db_pass'], $this->config['database password']);
        $this->debug('I fill Database Name');
        $I->fillField(['id' => 'jform_db_name'], $this->config['database name']);
        $this->debug('I fill Database Prefix');
        $I->fillField(['id' => 'jform_db_prefix'], $this->config['database prefix']);
        $this->debug('I click Remove Old Database ');
        $I->selectOptionInRadioField('Old Database Process', 'Remove');
        $this->debug('I click Next');
        $I->click(['link' => 'Next']);
        $this->debug('I wait Joomla to remove the old database if exist');
        $I->waitForElementVisible(['id' => 'jform_sample_file-lbl'],30);

        $this->debug('I install joomla with or without sample data');
        $I->waitForText('Finalisation', 10, ['xpath' => '//h3']);
        // @todo: installation of sample data needs to be created
        //if ($this->config['install sample data']) :
        //    $this->debug('I install Sample Data:' . $this->config['sample data']);
        //    $I->selectOption('#jform_sample_file', $this->config['sample data']);
        //else :
        //    $this->debug('I install Joomla without Sample Data');
        //    $I->selectOption('#jform_sample_file', '#jform_sample_file0'); // No sample data
        //endif;
        $I->selectOption(['id' => 'jform_sample_file'], ['id' => 'jform_sample_file0']); // No sample data
        $I->click(['link' => 'Install']);

        // Wait while Joomla gets installed
        $this->debug('I wait for Joomla being installed');
        $I->waitForText('Congratulations! Joomla! is now installed.', 60, ['xpath' => '//h3']);
    }

    /**
     * Install Joomla removing the Installation folder at the end of the execution
     */
    public function installJoomlaRemovingInstallationFolder()
    {
        $I = $this;

        $I->installJoomla();

        $this->debug('Removing Installation Folder');
        $I->click(['xpath' => "//input[@value='Remove installation folder']"]);
        $I->waitForElementVisible(['xpath' => "//input[@value='Installation folder successfully removed']"]);
        $this->debug('Joomla is now installed');
        $I->see('Congratulations! Joomla! is now installed.',['xpath' => '//h3']);
    }

    /**
     * Installs Joomla with Multilingual Feature active
     *
     * @param   array  $languages  Array containing the language names to be installed
     *
     * @example: $I->installJoomlaMultilingualSite(['Spanish', 'French']);
     */
    public function installJoomlaMultilingualSite($languages = array())
    {
        if (!$languages)
        {
            // If no language is passed French will be installed by default
            $languages[] = 'French';
        }

        $I = $this;

        $I->installJoomla();

        $this->debug('I go to Install Languages page');
        $I->click(['id' => 'instLangs']);
        $I->waitForText('Install Language packages', 60, ['xpath' => '//h3']);

        foreach ($languages as $language) :
            $I->debug('I mark the checkbox of the language: ' . $language);
            $I->click(['xpath' => "//label[contains(text()[normalize-space()], '$language')]/input"]);
        endforeach;

        $I->click(['link' => 'Next']);
        $I->waitForText('Multilingual', 60, ['xpath' => '//h3']);
        $I->selectOptionInRadioField('Activate the multilingual feature', 'Yes');
        $I->waitForElementVisible(['id' => 'jform_activatePluginLanguageCode-lbl']);
        $I->selectOptionInRadioField('Install localised content', 'Yes');
        $I->selectOptionInRadioField('Enable the language code plugin', 'Yes');
        $I->click(['link' => 'Next']);

        $I->waitForText('Congratulations! Joomla! is now installed.', 60, ['xpath' => '//h3']);
        $this->debug('Removing Installation Folder');
        $I->click(['xpath' => "//input[@value='Remove installation folder']"]);
        $I->waitForElementVisible(['xpath' => "//input[@value='Installation folder successfully removed']"]);
        $this->debug('Joomla is now installed');
        $I->see('Congratulations! Joomla! is now installed.',['xpath' => '//h3']);
    }

    /**
     * Sets in Adminitrator->Global Configuration the Error reporting to Development
     *
     * @note: doAdminLogin() before
     */
    public function setErrorReportingToDevelopment()
    {
        $I = $this;
        $this->debug('I open Joomla Global Configuration Page');
        $I->amOnPage('/administrator/index.php?option=com_config');
        $this->debug('I wait for Global Configuration title');
        $I->waitForText('Global Configuration',10,['css' => '.page-title']);
        $this->debug('I open the Server Tab');
        $I->click(['link' => 'Server']);
        $this->debug('I wait for error reporting dropdown');
        $I->selectOptionInChosen('Error Reporting', 'Development');
        $this->debug('I click on save');
        $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('config.save.application.apply')\"]"]);
        $this->debug('I wait for global configuration being saved');
        $I->waitForText('Global Configuration',10,['css' => '.page-title']);
        $I->see('Configuration successfully saved.',['id' => 'system-message-container']);
    }

	/**
	 * Installs a Extension in Joomla that is located in a folder inside the server
	 *
	 * @param   String  $path  Path for the Extension
	 * @param   string  $type  Type of Extension
	 *
	 * @note: doAdminLogin() before
	 */
	public function installExtensionFromDirectory($path, $type = 'Extension')
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->waitForText('Extensions: Install','30', ['css' => 'H1']);
        $I->click(['link' => 'Install from Directory']);
        $this->debug('I enter the Path');
        $I->fillField(['id' => 'install_directory'], $path);
        // @todo: we need to find a better locator for the following Install button
        $I->click(['xpath' => "//input[contains(@onclick,'Joomla.submitbutton3()')]"]); // Install button
        $I->waitForText('was successful','30', ['id' => 'system-message-container']);
        if ($type == 'Extension')
        {
            $this->debug('Extension successfully installed from ' . $path);
        }
        if ($type == 'Plugin')
        {
            $this->debug('Installing plugin was successful.' . $path);
        }
    }

    /**
     * Installs a Extension in Joomla that is located in a url
     *
     * @param   String  $url   Url address to the .zip file
     * @param   string  $type  Type of Extension
     *
     * @note: doAdminLogin() before
     */
    public function installExtensionFromUrl($url, $type = 'Extension')
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->waitForText('Extensions: Install','30', ['css' => 'H1']);
        $I->click(['link' => 'Install from URL']);
        $this->debug('I enter the url');
        $I->fillField(['id' => 'install_url'], $url);
        // @todo: we need to find a better locator for the following Install button
        $I->click(['xpath' => "//input[contains(@onclick,'Joomla.submitbutton4()')]"]); // Install button
        $I->waitForText('was successful','30', ['id' => 'system-message-container']);
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
     * Function to check for PHP Notices or Warnings
     *
     * @param string $page Optional, if not given checks will be done in the current page
     *
     * @note: doAdminLogin() before
     */
    public function checkForPhpNoticesOrWarnings($page = null)
    {
        $I = $this;

        if($page)
        {
            $I->amOnPage($page);
        }

        $I->dontSeeInPageSource('Notice:');
        $I->dontSeeInPageSource('Warning:');
    }

    /**
     * Selects an option in a Joomla Radio Field based on its label
     *
     * @return void
     */
    public function selectOptionInRadioField($label, $option)
    {
        $I = $this;
        $I->debug("Trying to select the $option from the $label");
        $label = $I->findField(['xpath' => "//label[contains(normalize-space(string(.)), '$label')]"]);
        $radioId = $label->getAttribute('for');

        $I->click("//fieldset[@id='$radioId']/label[contains(normalize-space(string(.)), '$option')]");
    }

    /**
     * Selects an option in a Chosen Selector based on its label
     *
     * @return void
     */
    public function selectOptionInChosen($label, $option)
    {
        $select = $this->findField($label);
        $selectID = $select->getAttribute('id');
        $chosenSelectID = $selectID . '_chzn';
        $I = $this;
        $this->debug("I open the $label chosen selector");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
        $this->debug("I select $option");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);
        $I->wait(1); // Gives time to chosen to close
    }

    /**
     * Function to Logout from Administrator Panel in Joomla!
     *
     * @return void
     */
    public function doAdministratorLogout()
    {
        $I = $this;
        $I->click(['xpath' => "//ul[@class='nav nav-user pull-right']//li//a[@class='dropdown-toggle']"]);
        $this->debug("I click on Top Right corner toggle to Logout from Admin");
        $I->waitForElement(['xpath' => "//a[text() = 'Logout']"], 10);
        $I->click(['xpath' => "//a[text() = 'Logout']"]);
        $I->waitForText('Log in', 20);
        $I->waitForElement(['id' => 'mod-login-username'], 10);
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
		$I = $this;
		$I->amOnPage('/administrator/index.php?option=com_plugins');
		$this->debug('I check for Notices and Warnings');
		$this->checkForPhpNoticesOrWarnings();
		$I->searchForItem($pluginName);
		$I->waitForElement($this->searchResultPluginName($pluginName), 30);
		$I->checkExistenceOf($pluginName);
		$I->click(['xpath' => "//input[@id='cb0']"]);
		$I->click(['xpath' => "//div[@id='toolbar-publish']/button"]);
		$I->see('successfully enabled', ['id' => 'system-message-container']);
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
	 */
	public function uninstallExtension($extensionName)
	{
		$I = $this;
		$I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$I->waitForText('Extensions: Manage','30', ['css' => 'H1']);
		$I->searchForItem($extensionName);
		$I->waitForElement(['id' => 'manageList'],'30');
		$I->click(['xpath' => "//input[@id='cb0']"]);
		$I->click(['xpath' => "//div[@id='toolbar-delete']/button"]);
		$I->waitForText('was successful','30', ['id' => 'system-message-container']);
		$I->see('was successful', ['id' => 'system-message-container']);
		$I->searchForItem($extensionName);
		$I->waitForText('There are no extensions installed matching your query.', 30, ['class' => 'alert-no-items']);
		$I->see('There are no extensions installed matching your query.', ['class' => 'alert-no-items']);
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
		$I = $this;
		if($name)
		{
			$I->debug("Searching for $name");
			$I->fillField(['id' => "filter_search"],$name);
			$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
		}
		else
		{
			$I->debug('clearing search filter');
			$I->click(['xpath' => "//button[@type='button' and @data-original-title='Clear']"]);
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
		$I = $this;
		$I->debug("Verifying if $name exist in search result");
		$I->seeElement(['xpath' => "//form[@id='adminForm']/div/table/tbody"]);
		$I->see($name,['xpath' => "//form[@id='adminForm']/div/table/tbody"]);
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
		$I = $this;
		$this->debug("Selecting Checkall button");
		$I->click(['xpath' => "//thead//input[@name='checkall-toggle' or @name='toggle']"]);
	}

    /**
     * Function to install a language through the interface
     *
     * @param string $languageName Name of the language you want to install
     *
     * @return void
     */
    public function installLanguage($languageName)
    {
        $I = $this;
        $I->amOnPage('administrator/index.php?option=com_installer&view=languages');
        $this->debug('I check for Notices and Warnings');
        $this->checkForPhpNoticesOrWarnings();
        $this->debug('Refreshing languages');
        $I->click(['xpath' => "//*[@id=\"toolbar-refresh\"]/button"]);
        $I->waitForElement(['id' => 'j-main-container'], 30);
        $I->searchForItem($languageName);
        $I->waitForElement($this->searchResultLanguageName($languageName), 30);
        $I->click(['id' => "cb0"]);
        $I->click(['xpath' => "//*[@id='toolbar-upload']/button"]);
        $I->waitForText('was successful.', 30, ['id' => 'system-message-container']);
        $I->see('No Matching Results',['class' => 'alert-no-items']);
        $this->debug($languageName . ' successfully installed');
    }

    /**
     * Function to return Path for the Language Name to be searched for
     *
     * @param   String $languageName Name of the language
     *
     * @return string
     */
    private function searchResultLanguageName($languageName)
    {
        $xpath = "//form[@id='adminForm']/div/table/tbody/tr[1]/td[2]/label[contains(text(),'" . $languageName . "')]";

        return $xpath;
    }
}
