<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use PhpParser;

class FullyQualifiedClassNameResolver
{
	/** @var array<string, PhpParser\NameContext|null> */
	private static array $nameContextsCache = [];


	public static function resolve(string $filename, string $class): string
	{
		if (str_starts_with($class, '\\')) {
			return $class;
		}

		if (!isset(self::$nameContextsCache[$filename])) {
			self::$nameContextsCache[$filename] = self::createNameContext($filename);
		}

		$nameContext = self::$nameContextsCache[$filename];
		if ($nameContext === null) {
			return $class;
		}

		return $nameContext->getResolvedClassName(new PhpParser\Node\Name($class))->toString();
	}


	private static function createNameContext(string $filename): PhpParser\NameContext|null
	{
		$parser = (new PhpParser\ParserFactory())->createForHostVersion();
		$traverser = new PhpParser\NodeTraverser();
		$nameResolver = new PhpParser\NodeVisitor\NameResolver();
		$traverser->addVisitor($nameResolver);
		$nameContext = $nameResolver->getNameContext();

		try {
			$code = @file_get_contents($filename); // intentionally @ - file may not exist
			if ($code === false) {
				return null;
			}

			$stmt = $parser->parse($code);
			if ($stmt === null) {
				return null;
			}

			$traverser->traverse($stmt);

			return $nameContext;
		} catch (\Throwable) {
			return null;
		}
	}

}
