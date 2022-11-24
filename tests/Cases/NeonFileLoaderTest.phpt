<?php declare(strict_types = 1);

namespace Tests\Cases;

use Adbros\TranslationExtra\Loader\NeonFileLoader;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class NeonFileLoaderTest extends TestCase
{

	public function testLoad(): void
	{
		$filename = TEMP_DIR . '/test.messages.neon';

		file_put_contents($filename, "first: First\nsecond: Second");

		$loader = new NeonFileLoader();
		$catalogue = $loader->load($filename, 'cs');

		Assert::same([
			'messages' => [
				'first' => 'First',
				'second' => 'Second',
			],
		], $catalogue->all());
	}

}

(new NeonFileLoaderTest())
	->run();
