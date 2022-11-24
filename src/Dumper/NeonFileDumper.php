<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\Dumper;

use Nette\Neon\Neon;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * NeonFileDumper generates an neon formatted string representation of a message catalogue.
 */
class NeonFileDumper extends FileDumper
{

	/**
	 * @inheritDoc
	 */
	public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
	{
		return Neon::encode($messages->all($domain), Neon::BLOCK);
	}

	/**
	 * @inheritDoc
	 */
	protected function getExtension(): string
	{
		return 'neon';
	}

}
