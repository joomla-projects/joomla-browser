<?php

class Locators{

	public static $loginUserName = ['id' => 'username'];

	public static $loginPassword = ['id' => 'password'];

	public static $loginButton = ['xpath' => "//div[@class='login']/form/fieldset/div[4]/div/button"];

	public static $frontEndLoginSuccess = ['xpath' => "//form[@id='login-form']/div[@class='logout-button']"];

	public static $frontEndLogoutButton = ['xpath' => "//div[@class='logout']//button[contains(text(), 'Log out')]"];

	public static $frontEndLoginForm = ['xpath' => "//div[@class='login']//button[contains(text(), 'Log in')]"];

	public static $adminLoginPageUrl = '/administrator/index.php';

	public static $adminLoginUserName = ['id' => 'mod-login-username'];

	public static $adminLoginPassword = ['id' => 'mod-login-password'];

	public static $adminLoginButton = ['xpath' => "//button[contains(normalize-space(), 'Log in')]"];

	public static $controlPanelLocator = ['css' => 'h1.page-title'];

	public static $frontEndLoginUrl = '/index.php?option=com_users&view=login';

}