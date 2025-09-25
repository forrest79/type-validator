<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Tests;

use Forrest79\PHPStanNarrowTypes\NarrowTypes;

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
		assert(NarrowTypes::isType($x, 'int'));
		self::checkTypeDescription($x);
	}


	private static function testCheck(mixed $x): void
	{
		NarrowTypes::checkType($x, 'int');
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
