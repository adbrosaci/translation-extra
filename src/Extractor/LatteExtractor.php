<?php declare(strict_types = 1);

namespace Adbros\TranslationExtra\Extractor;

use Latte\CompileException;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Html\ExpressionAttributeNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\PrintNode;
use Latte\Compiler\NodeTraverser;
use Latte\Engine;
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

	/** @var array<string, string> */
	private array $errors = [];

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
			try {
				$this->extractFile((string) $file, $catalogue);
			} catch (CompileException $e) {
				$this->errors[(string) $file] = $e->getMessage();
			}
		}
	}

	public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return array<string, string> path of the skipped template => compile error
	 */
	public function getErrors(): array
	{
		return $this->errors;
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

		(new NodeTraverser())->traverse($templateNode, function (Node $node) use ($catalogue): void {
			$this->extractNode($node, $catalogue);
		});
	}

	protected function extractNode(Node $node, MessageCatalogue $catalogue): void
	{
		if ($node instanceof PrintNode) {
			$modifier = $node->modifier;
			$expression = $node->expression;
		} elseif ($node instanceof ExpressionAttributeNode) {
			// Latte 3.1 turns an attribute value consisting of a single print into this node
			$modifier = $node->modifier;
			$expression = $node->value;
		} else {
			return;
		}

		if (!$modifier->hasFilter('translate') || !$expression instanceof StringNode) {
			return;
		}

		$message = $expression->value;
		$catalogue->set(($this->prefix !== '' ? $this->prefix . '.' : '') . $message, $message);
	}

}
