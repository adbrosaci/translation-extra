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

	public function testExtractHtmlAttributes(): void
	{
		Assert::same([
			'Close' => 'Close',
			'Close dialog' => 'Close dialog',
			'Search' => 'Search',
			'of the company' => 'of the company',
			'Submit' => 'Submit',
		], $this->extractMessages(__DIR__ . '/../data/latte/attributes.latte'));
	}

	public function testExtractElementsWithNetteAttributes(): void
	{
		Assert::same([
			'Shown title' => 'Shown title',
			'Shown content' => 'Shown content',
			'Item title' => 'Item title',
			'Item' => 'Item',
			'Snippet inside condition' => 'Snippet inside condition',
			'Go home' => 'Go home',
			'Home' => 'Home',
			'Optional label' => 'Optional label',
		], $this->extractMessages(__DIR__ . '/../data/latte/nAttributes.latte'));
	}

	public function testExtractConditionBranches(): void
	{
		Assert::same([
			'If branch' => 'If branch',
			'First elseif branch' => 'First elseif branch',
			'Second elseif title' => 'Second elseif title',
			'Second elseif branch' => 'Second elseif branch',
			'Else branch' => 'Else branch',
			'Inline if' => 'Inline if',
			'Inline else' => 'Inline else',
		], $this->extractMessages(__DIR__ . '/../data/latte/conditions.latte'));
	}

	public function testExtractForeach(): void
	{
		Assert::same([
			'Row title' => 'Row title',
			'Row' => 'Row',
			'No rows' => 'No rows',
			'Foreach item' => 'Foreach item',
		], $this->extractMessages(__DIR__ . '/../data/latte/foreach.latte'));
	}

	public function testExtractNestedBlocks(): void
	{
		Assert::same([
			'Block heading' => 'Block heading',
			'Nested title' => 'Nested title',
			'Nested content' => 'Nested content',
			'Nested elseif' => 'Nested elseif',
			'Captured' => 'Captured',
			'Defined row' => 'Defined row',
		], $this->extractMessages(__DIR__ . '/../data/latte/blocks.latte'));
	}

	public function testCompileErrorDoesNotStopExtraction(): void
	{
		$extractor = new LatteExtractor();
		$catalogue = new MessageCatalogue('cs');

		$extractor->extract(__DIR__ . '/../data/compile-error', $catalogue);

		Assert::same([
			'Valid title' => 'Valid title',
			'Valid content' => 'Valid content',
		], $catalogue->all('messages'));

		$errors = $extractor->getErrors();

		Assert::same(['Broken.latte'], array_map('basename', array_keys($errors)));
		Assert::contains('{unknownTag}', (string) reset($errors));
	}

	/**
	 * @return array<string, string>
	 */
	private function extractMessages(string $file): array
	{
		$extractor = new LatteExtractor();
		$catalogue = new MessageCatalogue('cs');

		$extractor->extract($file, $catalogue);

		Assert::same([], $extractor->getErrors());

		return $catalogue->all('messages');
	}

}

(new LatteExtractorTest())
	->run();
