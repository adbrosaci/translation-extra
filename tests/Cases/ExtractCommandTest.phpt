<?php declare(strict_types = 1);

namespace Tests\Cases;

use Adbros\TranslationExtra\Command\ExtractCommand;
use Adbros\TranslationExtra\Dumper\NeonFileDumper;
use Adbros\TranslationExtra\Extractor\LatteExtractor;
use Adbros\TranslationExtra\Loader\NeonFileLoader;
use Nette\Neon\Neon;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Tester\Assert;
use Tester\Helpers;

require __DIR__ . '/../bootstrap.php';

class ExtractCommandTest extends TestCase
{

	public function testTemplateWithCompileErrorIsReported(): void
	{
		$outputDir = TEMP_DIR . '/report';
		Helpers::purge($outputDir);

		$tester = $this->createCommandTester($outputDir);
		$tester->execute([
			'locale' => 'cs',
			'--scan-dir' => [__DIR__ . '/../data/compile-error'],
		]);

		Assert::same(0, $tester->getStatusCode());
		Assert::contains('[WARNING]', $tester->getDisplay());
		Assert::contains('compile-error/Broken.latte"wasskipped', (string) preg_replace('~\s+~', '', $tester->getDisplay()));
		Assert::same([
			'Valid title' => 'Valid title',
			'Valid content' => 'Valid content',
		], Neon::decode((string) file_get_contents($outputDir . '/messages.cs.neon')));
	}

	public function testCleanIsSkippedWhenTemplateWasSkipped(): void
	{
		$outputDir = TEMP_DIR . '/clean';
		Helpers::purge($outputDir);

		file_put_contents($outputDir . '/messages.cs.neon', "'Message from a broken template': 'Translated broken message'\n'Not used anywhere': 'Translated unused message'\n");

		$tester = $this->createCommandTester($outputDir);
		$tester->execute([
			'locale' => 'cs',
			'--scan-dir' => [__DIR__ . '/../data/compile-error'],
			'--clean' => true,
		]);

		Assert::same(0, $tester->getStatusCode());
		Assert::contains('Cleaningwasskipped', (string) preg_replace('~\s+~', '', $tester->getDisplay()));
		Assert::same([
			'Message from a broken template' => 'Translated broken message',
			'Not used anywhere' => 'Translated unused message',
			'Valid title' => 'Valid title',
			'Valid content' => 'Valid content',
		], Neon::decode((string) file_get_contents($outputDir . '/messages.cs.neon')));
	}

	private function createCommandTester(string $outputDir): CommandTester
	{
		$latteExtractor = new LatteExtractor();

		$extractor = new ChainExtractor();
		$extractor->addExtractor('latte', $latteExtractor);

		$reader = new TranslationReader();
		$reader->addLoader('neon', new NeonFileLoader());

		$writer = new TranslationWriter();
		$writer->addDumper('neon', new NeonFileDumper());

		return new CommandTester(new ExtractCommand($extractor, $reader, $writer, null, $outputDir, 'neon', [$latteExtractor]));
	}

}

(new ExtractCommandTest())
	->run();
