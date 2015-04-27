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
        $I->amOnPage('/administrator/index.php');
        $I->fillField('username', $this->config['username']);
        $I->fillField('Password', $this->config['password']);
        $I->click('Log in');
        $I->waitForText('Control Panel', 10, 'H1');
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
        $I->amOnPage('/installation/index.php');

        // I Wait for the text Main Configuration, meaning that the page is loaded
        $I->waitForText('Main Configuration', 10, 'h3');

        $I->click("//div[@id='jform_language_chzn']/a"); // Language Selector
        $I->click("//li[text()='English (United Kingdom)']"); // English (United Kingdom)
        sleep(1);
        $I->fillField('Site Name', 'Joomla CMS test');
        $I->fillField('Description', 'Site for testing Joomla CMS');

        // I get the configuration from acceptance.suite.yml (see: tests/_support/acceptancehelper.php)
        $I->fillField('Admin Email', $this->config['admin email']);
        $I->fillField('Admin Username', $this->config['username']);
        $I->fillField('Admin Password', $this->config['password']);
        $I->fillField('Confirm Admin Password', $this->config['password']);
        $I->click("//fieldset[@id='jform_site_offline']/label[2]"); // ['No Site Offline']
        $I->click('Next');
        // I Fill the form for creating the Joomla site Database'
        $I->waitForText('Database Configuration', 10, 'h3');

        $I->selectOption('#jform_db_type', $this->config['database type']);
        $I->fillField('Host Name', $this->config['database host']);
        $I->fillField('Username', $this->config['database user']);
        $I->fillField('Password', $this->config['database password']);
        $I->fillField('Database Name', $this->config['database name']);
        $I->fillField('Table Prefix', $this->config['database prefix']);
        $I->click("//label[@for='jform_db_old1']"); // Remove Old Database button
        $I->click('Next');

        // I Fill the form for creating the Joomla site database
        $I->waitForText('Finalisation', 10, 'h3');

        if ($this->config['install sample data']) :
            $I->selectOption('#jform_sample_file', $this->config['sample data']);
        else :
            $I->selectOption('#jform_sample_file', '#jform_sample_file0'); // No sample data
        endif;
        $I->click('Install');

        // Wait while Joomla gets installed
        $I->waitForText('Congratulations! Joomla! is now installed.', 30, 'h3');
    }

    /**
     * Sets in Adminitrator->Global Configuration the Error reporting to Development
     *
     * @note: doAdminLogin() before
     */
    public function setErrorReportingToDevelopment()
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_config');
        $I->waitForText('Global Configuration',10,'.page-title');
        $I->click('Server');
        $I->waitForElementVisible("//div[@id='jform_error_reporting_chzn']/a"); // Error reporting Dropdown
        $I->click("//div[@id='jform_error_reporting_chzn']/a");
        $I->click("//div[@id='jform_error_reporting_chzn']/div/ul/li[contains(text(), 'Development')]"); // Development
        $I->click('Save');
        $I->waitForText('Global Configuration',10,'.page-title');
        $I->see('Configuration successfully saved.','#system-message-container');
    }

    /**
     * Sets in Adminitrator->Global Configuration the Error reporting to Development
     *
     * @note: doAdminLogin() before
     */
    public function installExtensionFromDirectory($path)
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php?option=com_installer');
        $I->click('Install from Directory');
        $I->fillField('#install_directory', $path);
        // @todo: we need to find a better locator for the following Install button
        $I->click("//input[contains(@onclick,'Joomla.submitbutton3()')]"); // Install button
        $I->waitForText('was successful', 10, '#system-message-container');
    }
}
