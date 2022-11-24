<?php declare(strict_types = 1);

namespace Tests\Cases;

use Adbros\TranslationExtra\Dumper\NeonFileDumper;
use Nette\Neon\Neon;
use Symfony\Component\Translation\MessageCatalogue;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class NeonFileDumperTest extends TestCase
{

	public function testDump(): void
	{
		$catalogue = new MessageCatalogue('cs', [
			'messages' => [
				'first' => 'First',
				'last' => 'Last',
			],
		]);

		$dumper = new NeonFileDumper();
		$dumper->dump($catalogue, ['path' => TEMP_DIR]);

		$filename = TEMP_DIR . '/messages.cs.neon';

		Assert::true(file_exists($filename));
		Assert::same([
			'first' => 'First',
			'last' => 'Last',
		], Neon::decode(file_get_contents($filename)));
	}

}

(new NeonFileDumperTest())
	->run();
