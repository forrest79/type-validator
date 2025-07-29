<?php declare(strict_types=1);

namespace Forrest79\NarrowTypes;

use PhpParser;

/**
 * @phpstan-import-type Type from TypeParser
 */
final class FullyQualifiedClassNameResolver
{
	/** @var array<string, PhpParser\NameContext|false> */
	private static array $nameContextsCache = [];


	public static function resolve(string $filename, string $class): string
	{
		if (str_starts_with($class, '\\')) {
			return $class;
		}

		if (!isset(self::$nameContextsCache[$filename])) {
			self::$nameContextsCache[$filename] = self::createNameContext($filename) ?? false;
		}

		$nameContext = self::$nameContextsCache[$filename];
		if ($nameContext === false) {
			return $class;
		}

		return $nameContext->getResolvedClassName(new PhpParser\Node\Name($class))->toString();
	}


	private static function createNameContext(string|null $filename): PhpParser\NameContext|null
	{
		if ($filename === null) {
			return null;
		}

		$parserFactory = new PhpParser\ParserFactory();
		$parser = method_exists($parserFactory, 'createForVersion')
			? (new PhpParser\ParserFactory())->createForVersion(PhpParser\PhpVersion::fromComponents(8, 1)) // v5
			: (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7); // compatibility for Nikic\PhpParser shipped with PhpStan (v4)
		$traverser = new PhpParser\NodeTraverser();
		$nameResolver = new PhpParser\NodeVisitor\NameResolver();
		$traverser->addVisitor($nameResolver);
		$nameContext = $nameResolver->getNameContext();

		assert($parser instanceof PhpParser\Parser);

		try {
			$code = file_get_contents($filename);
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
			// ignore errors
		}

		return null;
	}

}
