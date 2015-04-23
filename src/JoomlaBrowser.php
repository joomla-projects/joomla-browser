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
        'password'
    );

    /**
     * Function to Do Admin Login In Joomla!
     *
     * @return void
     */
    public function doAdminLogin()
    {
        $I = $this;
        $I->amOnPage('/administrator/index.php');
        $I->fillField('username', $this->config['username']);
        $I->fillField('Password', $this->config['password']);
        $I->click('Log in');
        $I->waitForText('Control Panel', 10, 'H1');
    }

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
}
