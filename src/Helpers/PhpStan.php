<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Helpers;

use PHPStan\Analyser;
use PHPStan\PhpDoc;
use PHPStan\Type;
use PhpParser;

class PhpStan
{
	/** @var array<string, Analyser\NameScope> */
	private static array $nameScopeCache = [];

	private PhpDoc\TypeNodeResolver $typeNodeResolver;

	private string $filename;

	private string $typeDescription;


	public function __construct(PhpDoc\TypeNodeResolver $typeNodeResolver, string $filename, string $typeDescription)
	{
		$this->typeNodeResolver = $typeNodeResolver;
		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	public function convertToType(): Type\Type
	{
		return $this->typeNodeResolver->resolve(PhpDocParser::parseType($this->typeDescription), self::createNameScope($this->filename));
	}


	private static function createNameScope(string $filename): Analyser\NameScope
	{
		if (!isset(self::$nameScopeCache[$filename])) {
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

			self::$nameScopeCache[$filename] = $namespaceResolver->createNameScope();
		}

		return self::$nameScopeCache[$filename];
	}

}
