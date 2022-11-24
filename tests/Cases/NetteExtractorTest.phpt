<?php declare(strict_types = 1);

namespace Tests\Cases;

use Adbros\TranslationExtra\Extractor\NetteExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class NetteExtractorTest extends TestCase
{

	public function testExtractDirectory(): void
	{
		$extractor = new NetteExtractor();
		$catalogue = new MessageCatalogue('cs');

		$extractor->extract(__DIR__ . '/../data/extract', $catalogue);

		Assert::same([
			'messages' => [
				'Xxx' => 'Xxx',
				'Invalid token' => 'Invalid token',
				'Error!' => 'Error!',
			],
		], $catalogue->all());
	}

}

(new NetteExtractorTest())
	->run();
