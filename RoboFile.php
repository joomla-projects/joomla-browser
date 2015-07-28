<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
	public function generateApiDocumentation()
	{
		$this->taskGenDoc('docs/api.md')
			->docClass('Codeception\Module\JoomlaBrowser')
			->filterMethods(function(\ReflectionMethod $r) {return ($r->isPublic() && $r->class == 'Codeception\Module\JoomlaBrowser');})
			->processClass(false)
			->processClassSignature(false)
			->processClassDocBlock(false)
			->processMethod(true)
			->processMethodSignature(function(\ReflectionMethod $r, $text) {return "#### ". substr($text, 13);})
			->processMethodDocBlock(function(\ReflectionMethod $r, $text) {var_dump($r);var_dump($text);return $text;})
			->processProperty(false)
			->processPropertySignature(false)
			->processPropertyDocBlock(false)
			//->reorder(false)
			//->reorderMethods(false)
			//->prepend(false)
			//->append('test')
			->run();
	}
}