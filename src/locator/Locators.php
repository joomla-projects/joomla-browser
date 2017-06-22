<?php
namespace Joomla\Browser\Locators;

class Locators {

	public $loginUserName = ['id' => 'username'];

	public $loginPassword = ['id' => 'password'];

	public $loginButton = ['xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button"];

	public $frontEndLoginSuccess = ['xpath' => "//form[@id='login-form']/div[@class='logout-button']"];

	public $frontEndLogoutButton = ['xpath' => "//div[@class='logout']//button[contains(text(), 'Log out')]"];

	public $frontEndLoginForm = ['xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]"];

	public $adminLoginPageUrl = '/administrator/index.php';

	public $adminLoginUserName = ['id' => 'mod-login-username'];

	public $adminLoginPassword = ['id' => 'mod-login-password'];

	public $adminLoginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];

	public $controlPanelLocator = ['css' => 'h1.page-title'];

	public $frontEndLoginUrl = '/index.php?option=com_users&view=login';

}