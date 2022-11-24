<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Writer\TranslationWriter;

class ExtractCommand extends Command
{

	/** @inheritDoc */
	protected static $defaultName = 'translation:extract';

	/** @inheritDoc */
	protected static $defaultDescription = 'Extract missing translations keys from code to translation files.';

	private ChainExtractor $extractor;

	private TranslationReader $reader;

	private TranslationWriter $writer;

	/** @var string[]|null */
	private ?array $defaultScanDir;

	private ?string $defaultOutputDir;

	private ?string $defaultFormat;

	/**
	 * @param array<string> $defaultScanDir
	 */
	public function __construct(
		ChainExtractor $extractor,
		TranslationReader $reader,
		TranslationWriter $writer,
		?array $defaultScanDir = null,
		?string $defaultOutputDir = null,
		?string $defaultFormat = null
	)
	{
		parent::__construct();

		$this->defaultScanDir = $defaultScanDir;
		$this->defaultOutputDir = $defaultOutputDir;
		$this->defaultFormat = $defaultFormat;
		$this->extractor = $extractor;
		$this->reader = $reader;
		$this->writer = $writer;
	}

	protected function configure(): void
	{
		$this
			->setDefinition([
				new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
				new InputOption('scan-dir', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory where to load the messages.'),
				new InputOption('output-dir', 'o', InputOption::VALUE_OPTIONAL, 'Directory to write the messages to.'),
				new InputOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Override the default output format'),
				new InputOption('clean', null, InputOption::VALUE_NONE, 'Should clean not found messages'),
			])
			->setName(self::$defaultName)
			->setDescription(self::$defaultDescription);
	}

	/**
	 * @inheritDoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$scanDirectory = count($tmp = $input->getOption('scan-dir')) > 0 ? $tmp : $this->defaultScanDir;
		$outputDirectory = $input->getOption('output-dir') ?? $this->defaultOutputDir;
		$locale = $input->getArgument('locale');

		$currentCatalogue = new MessageCatalogue($locale);
		$this->reader->read($outputDirectory, $currentCatalogue);

		$extractedCatalogue = new MessageCatalogue($locale);

		foreach ($scanDirectory as $directory) {
			$this->extractor->extract($directory, $extractedCatalogue);
		}

		$operation = $input->getOption('clean') === true
			? new TargetOperation($currentCatalogue, $extractedCatalogue)
			: new MergeOperation($currentCatalogue, $extractedCatalogue);

		$format = $input->getOption('format') ?? $this->defaultFormat;

		/** @var MessageCatalogue $result */
		$result = $operation->getResult();
		$this->writer->write($result, $format, ['path' => $outputDirectory]);

		return 0;
	}

}
