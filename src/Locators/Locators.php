<?php
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
	public $loginUserName = ['id' => 'username'];

	/**
	 * Locator for the Password field
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $loginPassword = ['id' => 'password'];

	/**
	 * Locator for the Login Button
	 *
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $loginButton = ['xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button"];

	/**
	 * Locator for the Logout Button
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLoginSuccess = ['xpath' => "//form[@id='login-form']/div[@class='logout-button']"];

	/**
	 * Locator for the Logout Button
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLogoutButton = ['xpath' => "//div[@class='logout-button']//input[@value='Log out']"];

	/**
	 * Locator for the Login Button
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLoginForm = ['xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]"];

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
	public $adminLoginUserName = ['id' => 'mod-login-username'];

	/**
	 * Locator for the admin password field
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginPassword = ['id' => 'mod-login-password'];

	/**
	 * Locator for the Login Button
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $adminLoginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];

	/**
	 * Locator for the Control Panel
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $controlPanelLocator = ['css' => 'h1.page-title'];

	/**
	 * Locator for the Login URL
	 * 
	 * @var    array
	 * @since  3.7.4.2
	 */
	public $frontEndLoginUrl = '/index.php?option=com_users&view=login';

	/**
	 * New Button in the Admin toolbar
	 *
	 * @var    array
	 * @since  3.7.5
	 */
	public $adminToolbarButtonNew = ['class' => 'button-new'];

	/**
	 * Apply Button in the Admin toolbar
	 *
	 * @var    array
	 * @since  3.7.5
	 */
	public $adminToolbarButtonApply = ['class' => 'button-apply'];

	/**
	 * Admin Control Panel Text
	 *
	 * @var    array
	 * @since  3.7.5
	 */
	public $adminControlPanelText = 'Control Panel';

	/**
	 * Global Configuration URL
	 *
	 * @var    string
	 * @since  4.0.0
	 */	
	public static $globalConfiguratinUrl = '/administrator/index.php?option=com_config';

	/**
	 * Admin Module URL
	 *
	 * @var    string
	 * @since  4.0.0
	 */	
	public static $moduleUrl = '/administrator/index.php?option=com_modules';
	/**
	 * Select Module Title
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $moduleTitle = '#jform_title';
	/**
	 * Select Filter Options
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public static $filterOptions = ['link' => 'Filtering Options'];
	/**
	 * Select Filter Options
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $selectModuleCategory = '#jform_params_catid';
	/**
	 * Drop Down Toggle Element.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $dropDownToggle = "//button[contains(@class, 'dropdown-toggle')]";
	/**
	 * Fill Category
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $fillModuleCategory = '//div[@id="jform_params_catid_chzn"]/ul/li/input';
	/**
	 * Select
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $moduleSelect = '#jform_params_parent_select';
	/**
	 * Select Module Position
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $modulePosition = '//div[@id="jform_position_chzn"]/div/div/input';
	/**
	 * Select Module category
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public static $moduleCategory = 'jform_params_catid_chzn';
}
