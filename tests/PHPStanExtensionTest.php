<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class PHPStanExtensionTest
{

	public static function test(): void
	{
		self::testIsType(1);
		self::testCheck(2);
		self::testIsTypeGlobalFunction(3);
	}


	private static function testIsType(mixed $x): void
	{
		assert(TypeValidator::isType($x, 'int'));
		self::checkTypeDescription($x);
	}


	private static function testCheck(mixed $x): void
	{
		TypeValidator::checkType($x, 'int');
		self::checkTypeDescription($x);
	}


	private static function testIsTypeGlobalFunction(mixed $x): void
	{
		assert(is_type($x, 'int'));
		self::checkTypeDescription($x);
	}


	private static function checkTypeDescription(int $x): void
	{
		Helper::dump($x);
	}

}

Assert::noError(function (): void {
	PHPStanExtensionTest::test();
});
