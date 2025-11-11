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
		self::testAssertType(4);
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


	private static function testAssertType(mixed $x): void
	{
		self::checkTypeDescription(as_type($x, 'int'));
	}


	private static function checkTypeDescription(int $x): void
	{
		var_dump($x);
	}

}

Assert::noError(static function (): void {
	PHPStanExtensionTest::test();
});
