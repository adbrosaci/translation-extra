<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\Extractor;

use Latte\Compiler\Node;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Engine;
use Latte\Essential\Nodes\PrintNode;
use Latte\Essential\TranslatorExtension;
use Latte\Tools\Linter;
use LogicException;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Nette\Bridges\FormsLatte\FormsExtension;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use const PATHINFO_EXTENSION;

class LatteExtractor extends AbstractFileExtractor implements ExtractorInterface
{

	private string $prefix = '';

	private Engine $engine;

	public function __construct(
		?LatteFactory $latteFactory = null
	)
	{
		if ($latteFactory !== null) {
			$this->engine = $latteFactory->create();
			$this->engine->addExtension(new TranslatorExtension(null));
			$this->engine->addExtension(new UIExtension(null));
			$this->engine->addExtension(new FormsExtension());
		} else {
			$this->engine = (new Linter())->getEngine();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function extract($resource, MessageCatalogue $catalogue)
	{
		$files = $this->extractFiles($resource);

		foreach ($files as $file) {
			$this->extractFile((string) $file, $catalogue);
		}
	}

	public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
	}

	/**
	 * @inheritDoc
	 */
	protected function canBeExtracted(string $file)
	{
		return $this->isFile($file) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['latte', 'phtml'], true);
	}

	/**
	 * @inheritDoc
	 */
	protected function extractFromDirectory($directory)
	{
		if (!class_exists(Finder::class)) {
			throw new LogicException(sprintf('You cannot use "%s" as the "symfony/finder" package is not installed. Try running "composer require symfony/finder".', static::class));
		}

		$finder = new Finder();

		return $finder->files()->name(['*.latte', '*.phtml'])->in($directory);
	}

	protected function extractFile(string $file, MessageCatalogue $catalogue): void
	{
		$templateNode = $this->engine->parse(file_get_contents($file));

		foreach ($templateNode->main as $node) {
			$this->extractNode($node, $catalogue);
		}
	}

	protected function extractNode(Node $node, MessageCatalogue $catalogue): void
	{
		if ($node instanceof PrintNode) {
			if ($node->modifier->hasFilter('translate')) {
				if ($node->expression instanceof StringNode) {
					$message = $node->expression->value;
					$catalogue->set(($this->prefix !== '' ? $this->prefix . '.' : '') . $message, $message);
				}
			}
		} else {
			if (property_exists($node, 'content') && $node->content instanceof FragmentNode) {
				foreach ($node->content->children as $child) {
					$this->extractNode($child, $catalogue);
				}
			}

			if (property_exists($node, 'attributes') && $node->attributes instanceof FragmentNode) {
				foreach ($node->attributes->children as $child) {
					$this->extractNode($child, $catalogue);
				}
			}

			if (property_exists($node, 'value') && $node->value instanceof FragmentNode) {
				foreach ($node->value->children as $child) {
					$this->extractNode($child, $catalogue);
				}
			}

			if (property_exists($node, 'then') && $node->then instanceof FragmentNode) {
				foreach ($node->then->children as $child) {
					$this->extractNode($child, $catalogue);
				}
			}

			if (property_exists($node, 'else') && $node->else instanceof FragmentNode) {
				foreach ($node->else->children as $child) {
					$this->extractNode($child, $catalogue);
				}
			}
		}
	}

}
