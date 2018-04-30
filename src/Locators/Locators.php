<?php
/**
 * @package    JoomlaBrowser
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Codeception\Module\Locators;

/**
 * Joomla Browser Locator class to hold selector value for the default theme
 *
 * Class Locators
 *
 * @since  3.7.4.2
 */
class Locators
{
	/**
	 * Locator for the username field
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $loginUserName = array('id' => 'username');

	/**
	 * Locator for the Password field
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $loginPassword = array('id' => 'password');

	/**
	 * Locator for the Login Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $loginButton = array('xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button");

	/**
	 * Locator for the Logout Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLoginSuccess = array('xpath' => "//form[@id='login-form']/div[@class='logout-button']");

	/**
	 * Locator for the Logout Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLogoutButton = array('xpath' => "//div[@class='logout-button']//input[@value='Log out']");

	/**
	 * Locator for the Login Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLoginForm = array('xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]");

	/**
	 * Locator for the Login Page Url
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginPageUrl = '/administrator/index.php';

	/**
	 * Locator for the administrator username field
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginUserName = array('id' => 'mod-login-username');

	/**
	 * Locator for the admin password field
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginPassword = array('id' => 'mod-login-password');

	/**
	 * Locator for the Login Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginButton = array('xpath' => "//button[contains(normalize-space(), 'Log in')]");

	/**
	 * Locator for the Control Panel
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $controlPanelLocator = array('css' => 'h1.page-title');

	/**
	 * Locator for the Login URL
	 *
	 * @var    string
	 * @since  3.7.4.2
	 */
	public $frontEndLoginUrl = '/index.php?option=com_users&view=login';

	/**
	 * New Button in the Admin toolbar
	 *
	 * @var    array
	 * @since  3.7.5
	 */
	public $adminToolbarButtonNew = array('class' => 'button-new');

	/**
	 * Apply Button in the Admin toolbar
	 *
	 * @var    array
	 * @since  3.7.5
	 */
	public $adminToolbarButtonApply = array('class' => 'button-apply');

	/**
	 * Admin Control Panel Text
	 *
	 * @var    string
	 * @since  3.7.5
	 */
	public $adminControlPanelText = 'Control Panel';
}
