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
        $I->waitForText('Main Configuration', 10,['xpath' => '//h3']);

        $I->debug('I select en-GB as installation language');
        $I->selectOptionInChosen('Select Language', 'English (United Kingdom)');
        $I->wait(1);
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
        $I->waitForText('Congratulations! Joomla! is now installed.', 10, ['xpath' => '//h3']);
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
        $I->waitForElementVisible(['xpath' => "//div[@id='jform_error_reporting_chzn']/a"]); // Error reporting Dropdown
        $this->debug('I click on error reporting dropdown');
        $I->click(['xpath' => "//div[@id='jform_error_reporting_chzn']/a"]);
        $this->debug('I click on development option');
        $I->click(['xpath' => "//div[@id='jform_error_reporting_chzn']/div/ul/li[contains(text(), 'Development')]"]); // Development
        $I->wait(1);
        $this->debug('I click on save');
        $I->click(['xpath' => "//button[@onclick=\"Joomla.submitbutton('config.save.application.apply')\"]"]);
        $this->debug('I wait for global configuration being saved');
        $I->waitForText('Global Configuration',10,['css' => '.page-title']);
        $I->see('Configuration successfully saved.',['id' => 'system-message-container']);
    }

    /**
     * Installs a Extension in Joomla that is located in a folder inside the server
     *
     * @param $path
     *
     * @note: doAdminLogin() before
     */
    public function installExtensionFromDirectory($path)
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->click(['link' => 'Install from Directory']);
        $this->debug('I enter the Path for extension');
        $I->fillField(['id' => 'install_directory'], $path);
        // @todo: we need to find a better locator for the following Install button
        $I->click(['xpath' => "//input[contains(@onclick,'Joomla.submitbutton3()')]"]); // Install button
        $I->waitForText('was successful', 10, ['id' => 'system-message-container']);
        $this->debug('Extension successfully installed from' . $path);
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
        $this->debug($select);
        $selectID = $select->getAttribute('id');
        $this->debug($selectID);
        $chosenSelectID = $selectID . '_chzn';
        $this->debug($chosenSelectID);
        $I = $this;
        $this->debug("I open the $label chosen selector");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']/a/div/b"]);
        $this->debug("I select $option");
        $I->click(['xpath' => "//div[@id='$chosenSelectID']//li[text()='$option']"]);
    }
}
