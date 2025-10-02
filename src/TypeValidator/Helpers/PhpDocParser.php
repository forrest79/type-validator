<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser;
use PHPStan\PhpDocParser\ParserConfig;

class PhpDocParser
{
	private static Parser\TypeParser|null $typeParser = null;

	private static ParserConfig|null $parserConfig = null;


	public static function parseType(string $typeDescription): Ast\Type\TypeNode
	{
		$tokens = new Parser\TokenIterator((new Lexer(self::getParserConfig()))->tokenize($typeDescription));

		try {
			return self::getTypeParser()->parse($tokens);
		} catch (Parser\ParserException $e) {
			return new Ast\Type\InvalidTypeNode($e);
		}
	}


	private static function getTypeParser(): Parser\TypeParser
	{
		if (self::$typeParser === null) {
			$constantExpressionParser = new Parser\ConstExprParser(self::getParserConfig());
			self::$typeParser = new Parser\TypeParser(self::getParserConfig(), $constantExpressionParser);
		}

		return self::$typeParser;
	}


	private static function getParserConfig(): ParserConfig
	{
		if (self::$parserConfig === null) {
			self::$parserConfig = new ParserConfig([]);
		}

		return self::$parserConfig;
	}

}
