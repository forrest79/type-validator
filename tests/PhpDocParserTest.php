<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use PHPStan\PhpDocParser;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class PhpDocParserTest
{

	public static function test(): void
	{
		assert(TypeValidator\Helpers\PhpDocParser::parseType('array<int, mixed') instanceof PhpDocParser\Ast\Type\InvalidTypeNode);
	}

}

Assert::noError(static function (): void {
	PhpDocParserTest::test();
});
