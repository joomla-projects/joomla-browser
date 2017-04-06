<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use Joomla\Testing\Robo\Tasks\loadTasks;

	/**
	 * Check the code style of the project against a passed sniffers using PHP_CodeSniffer_CLI
	 *
	 * @param   string $sniffersPath Path to the sniffers. If not provided Joomla Coding Standards will be used.
	 */
	public function checkCodestyle($sniffersPath = null)
	{
		if (is_null($sniffersPath))
		{
			$sniffersPath = __DIR__ . '/.travis/phpcs/Joomla';
		}

		$this->taskCodeChecks()
			->setBaseRepositoryPath(__DIR__)
			->setCodeStyleStandardsFolder($sniffersPath)
			->setCodeStyleCheckFolders(['/src'])
			->checkCodeStyle()
			->run()
			->stopOnFail();
	}
}