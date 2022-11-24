<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\Loader;

use Nette\Neon\Exception;
use Nette\Neon\Neon;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\FileLoader;

/**
 * NeonFileLoader loads translations from neon file.
 */
class NeonFileLoader extends FileLoader
{

	/**
	 * @inheritDoc
	 */
	protected function loadResource(string $resource): array
	{
		try {
			/** @var string $content */
			$content = file_get_contents($resource);

			$messages = Neon::decode($content);
		} catch (Exception $e) {
			throw new InvalidResourceException(sprintf('Error parsing Neon: %s', $e->getMessage()));
		}

		if ($messages === null) {
			$messages = [];
		}

		if (!is_array($messages)) {
			throw new InvalidResourceException(sprintf('The file "%s" must contain a Neon array.', $resource));
		}

		return $messages;
	}

}
