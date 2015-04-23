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
	protected $requiredFields = array('username', 'password', 'joomlaurl');

	/**
	 * The admin absolute URL.
	 *
	 * @var [type]
	 */
	protected $adminLoginUrl;

	/**
	 * Initializes the module setting the properties values.
	 *
	 * @return void
	 */
	public function _initialize()
	{
		parent::_initialize();
		$this->adminLoginUrl = $this->config['joomlaurl'] . '/administrator/index.php';
	}

	/**
	 * Function to Do Admin Login In Joomla!
	 *
	 * @return void
	 */
	public function doAdminLogin()
	{
		$this->amOnUrl($this->adminLoginUrl);
		$this->fillField('username', $this->config['username']);
		$this->fillField('Password', $this->config['password']);
		$this->click('Log in');
	}
}
