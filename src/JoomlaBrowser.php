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
        $I->click(['xpath' => "//form[@id='form-login']/fieldset/div[3]/div/div/button"]);
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
        $I->click(['xpath' => "//label[@for='jform_db_old1']"]); // Remove Old Database button
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
        $I->waitForText('Extension Manager: Install','30', ['css' => 'H1']);
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
		$I->fillField(['xpath' => "//input[@id='filter_search']"], $pluginName);
		$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
		$I->waitForElement($this->searchResultPluginName($pluginName), 30);
		$I->seeElement(['xpath' => "//form[@id='adminForm']/div/table/tbody/tr[1]"]);
		$I->see($pluginName, ['xpath' => "//form[@id='adminForm']/div/table/tbody/tr[1]"]);
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
	 * Function to uninstall Extension
	 *
	 * @param   String  $extensionName  Name of the Extension
	 * @param   bool    $selectAll      Do we need to delete all the search Result Items
	 *
	 * @return void
	 */
	public function uninstallExtension($extensionName, $selectAll = false)
	{
		$I = $this;
		$I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$I->waitForText('Extension Manager: Manage','30', ['css' => 'H1']);
		$I->fillField(['id' => 'filter_search'], $extensionName);
		$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
		$I->waitForElement(['id' => 'manageList'],'30');
		if($selectAll == true)
		{
			$I->click(['xpath' => "//input[@onclick='Joomla.checkAll(this)']"]);
		}
		else
		{
			$I->click(['xpath' => "//input[@id='cb0']"]);
		}
		$I->click(['xpath' => "//div[@id='toolbar-delete']/button"]);
		$I->waitForText('was successful','30', ['id' => 'system-message-container']);
		$I->see('was successful', ['id' => 'system-message-container']);
		$I->fillField(['id' => 'filter_search'], $extensionName);
		$I->click(['xpath' => "//button[@type='submit' and @data-original-title='Search']"]);
		$I->waitForText('There are no extensions installed matching your query.', 30, ['class' => 'alert-no-items']);
		$I->see('There are no extensions installed matching your query.', ['class' => 'alert-no-items']);
		$this->debug('Extension successfully uninstalled');
	}
}
