<?php
/**
 * @package    JoomlaBrowser
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Browser;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

/**
 * Trait ElementIsVisibleTrait Based on the blog post below
 *
 * @see    https://maslosoft.com/blog/2018/04/04/codeception-acceptance-check-if-element-is-visible/
 * @since  4.0
 */
trait ElementIsVisibleTrait
{
	/**
	 * Checks if an element is visible on the page
	 *
	 * @param   string  $element  The element to check if it's visible
	 *
	 * @return  boolean
	 */
	public function haveVisible($element)
	{
		// phpcs:disable
		/** @var \Codeception\Actor $I */
		$I = $this;
		$value = false;
		$I->executeInSelenium(function (RemoteWebDriver $webDriver) use ($element, &$value)
			{
				try
				{
					$element = $webDriver->findElement(WebDriverBy::cssSelector($element));
					$value = $element instanceof RemoteWebElement;
				}
				catch (\Exception $e)
				{
					// Swallow exception silently
				}
			}
		);
		// phpcs:enable

		return $value;
	}
}
