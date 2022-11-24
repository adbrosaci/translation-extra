<?php declare(strict_types = 1);

namespace Tests\Cases;

use Adbros\TranslationExtra\Extractor\LatteExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class LatteExtractorTest extends TestCase
{

	public function testExtractDirectory(): void
	{
		$extractor = new LatteExtractor();
		$catalogue = new MessageCatalogue('cs');

		$extractor->extract(__DIR__ . '/../data/extract', $catalogue);

		Assert::same([
			'messages' => [
				'First' => 'First',
				'Second' => 'Second',
				'Third' => 'Third',
				'Fourth' => 'Fourth',
				'Long with spaces' => 'Long with spaces',
				'Another long with spaces' => 'Another long with spaces',
			],
		], $catalogue->all());
	}

}

(new LatteExtractorTest())
	->run();
