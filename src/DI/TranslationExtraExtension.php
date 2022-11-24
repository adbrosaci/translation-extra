<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\DI;

use Adbros\TranslationExtra\Command\ExtractCommand;
use Adbros\TranslationExtra\Dumper\NeonFileDumper;
use Adbros\TranslationExtra\Extractor\LatteExtractor;
use Adbros\TranslationExtra\Extractor\NetteExtractor;
use Adbros\TranslationExtra\Loader\NeonFileLoader;
use Exception;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;
use stdClass;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Reader\TranslationReader;
use Symfony\Component\Translation\Writer\TranslationWriter;

/**
 * @property stdClass $config
 */
class TranslationExtraExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		$builder = $this->getContainerBuilder();

		return Expect::structure([
			'extractor' => Expect::structure([
				'defaultScanDir' => Expect::arrayOf('string')->default([
					$builder->parameters['appDir'],
				]),
				'defaultOutputDir' => Expect::string($builder->parameters['appDir'] . '/locale'),
				'defaultFormat' => Expect::string('neon'),
				'extractors' => Expect::arrayOf('string')->default([
					'nette' => NetteExtractor::class,
					'latte' => LatteExtractor::class,
				]),
				'loaders' => Expect::arrayOf('string')->default([
					'neon' => NeonFileLoader::class,
				]),
				'dumpers' => Expect::arrayOf('string')->default([
					'neon' => NeonFileDumper::class,
				]),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$extractor = $builder->addDefinition($this->prefix('extractor'))
			->setFactory(ChainExtractor::class);

		foreach ($this->config->extractor->extractors as $key => $value) {
			$reflection = new ReflectionClass($value);

			if (!$reflection->implementsInterface(ExtractorInterface::class)) {
				throw new Exception(sprintf('Extractor must implement interface "%s".', ExtractorInterface::class));
			}

			$def = $builder->addDefinition($this->prefix('extractor' . $key))
				->setFactory($value);

			$extractor->addSetup('addExtractor', [$key, $def]);
		}

		$reader = $builder->addDefinition($this->prefix('reader'))
			->setFactory(TranslationReader::class);

		foreach ($this->config->extractor->loaders as $key => $value) {
			$reflection = new ReflectionClass($value);

			if (!$reflection->implementsInterface(LoaderInterface::class)) {
				throw new Exception(sprintf('Loader must implement interface "%s".', ExtractorInterface::class));
			}

			$def = $builder->addDefinition($this->prefix('loader' . $key))
				->setFactory($value);

			$reader->addSetup('addLoader', [$key, $def]);
		}

		$writer = $builder->addDefinition($this->prefix('writer'))
			->setFactory(TranslationWriter::class);

		foreach ($this->config->extractor->dumpers as $key => $value) {
			$reflection = new ReflectionClass($value);

			if (!$reflection->implementsInterface(DumperInterface::class)) {
				throw new Exception(sprintf('Dumper must implement interface "%s".', ExtractorInterface::class));
			}

			$def = $builder->addDefinition($this->prefix('dumper' . $key))
				->setFactory($value);

			$writer->addSetup('addDumper', [$key, $def]);
		}

		$builder->addDefinition($this->prefix('extractCommand'))
			->setFactory(ExtractCommand::class, [
				'defaultScanDir' => $this->config->extractor->defaultScanDir,
				'defaultOutputDir' => $this->config->extractor->defaultOutputDir,
				'defaultFormat' => $this->config->extractor->defaultFormat,
			]);
	}

}
