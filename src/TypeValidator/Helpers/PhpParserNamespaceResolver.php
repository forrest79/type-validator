<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use PHPStan\Analyser;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

class PhpParserNamespaceResolver extends NodeVisitorAbstract
{
	private string|null $namespace = null;

	/** @var array<string, string> */
	private array $aliases = [];


	/**
	 * @param array<mixed> $nodes
	 */
	public function beforeTraverse(array $nodes): null
	{
		return null;
	}


	public function enterNode(Node $node): null
	{
		if ($node instanceof Stmt\Namespace_) {
			$this->namespace = $node->name !== null ? (string) $node->name : null;
		} elseif ($node instanceof Stmt\Use_) {
			foreach ($node->uses as $use) {
				$this->addAlias($use, $node->type);
			}
		} elseif ($node instanceof Stmt\GroupUse) {
			foreach ($node->uses as $use) {
				$this->addAlias($use, $node->type, $node->prefix);
			}
		}

		return null;
	}


	private function addAlias(Node\UseItem $use, int $type, Name|null $prefix = null): void
	{
		// Add prefix for group uses
		$name = $prefix !== null ? Name::concat($prefix, $use->name) : $use->name;

		assert($name !== null);

		// Type is determined either by individual element or whole use declaration
		$type |= $use->type;

		if ($type === Stmt\Use_::TYPE_NORMAL) {
			$this->aliases[strtolower($use->getAlias()->name)] = $name->name;
		}
	}


	public function createNameScope(): Analyser\NameScope
	{
		assert($this->namespace === null || $this->namespace !== '');
		return new Analyser\NameScope($this->namespace, $this->aliases);
	}

}
