<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Helpers;

use PHPStan\Analyser;
use PhpParser;

class NameScopeFactory
{
	/** @var array<string, Analyser\NameScope> */
	private static array $cache = [];


	public static function create(string $filename): Analyser\NameScope
	{
		if (!isset(self::$cache[$filename])) {
			$parser = (new PhpParser\ParserFactory())->createForHostVersion();
			$traverser = new PhpParser\NodeTraverser();
			$namespaceResolver = new PhpParserNamespaceResolver();
			$traverser->addVisitor($namespaceResolver);

			try {
				$code = file_get_contents($filename);
				if ($code === false) {
					return new Analyser\NameScope(null, []);
				}

				$stmt = $parser->parse($code);
				if ($stmt === null) {
					return new Analyser\NameScope(null, []);
				}

				$traverser->traverse($stmt);
			} catch (\Throwable) {
				// ignore errors
			}

			self::$cache[$filename] = $namespaceResolver->createNameScope();
		}

		return self::$cache[$filename];
	}

}
