<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Tests;

use Forrest79\PHPStanNarrowTypes\Helpers;
use Forrest79\PHPStanNarrowTypes\NarrowTypes;

class TypeDescriptionsTest
{

	public static function test(): void
	{
		//$handle = fopen('php://memory', 'rw'); fclose($handle);

		/*
		assert(NarrowTypes::isType(1, '1|2|3'));
		assert(NarrowTypes::isType(null, '?int'));
		assert(NarrowTypes::isType(1, 'int'));
		assert(NarrowTypes::isType('string', 'string'));
		assert(NarrowTypes::isType(1.0, 'float'));
		assert(NarrowTypes::isType(1.0, 'double'));
		assert(NarrowTypes::isType(true, 'bool'));
		//assert(NarrowTypes::isType(1, 'xyz'));
		//assert(NarrowTypes::isType(1, '$this'));
		//assert(NarrowTypes::isType(1, 'array<'));
		assert(NarrowTypes::isType(['a' => 1, '2' => 3], 'array'));

		assert(NarrowTypes::isType(0, 'array-key'));
		assert(NarrowTypes::isType('', 'array-key'));
		assert(!NarrowTypes::isType(false, 'array-key'));
		assert(!NarrowTypes::isType([], 'array-key'));

		assert(NarrowTypes::isType([1, 3], 'int[]'));
		assert(NarrowTypes::isType(1, 'positive-int'));
		//assert(NarrowTypes::isType(1, 'int<0, 100>'));

		assert(NarrowTypes::isType(['foo' => 1, 'bar' => 'test'], 'array{\'foo\': int, "bar": string}'));
		assert(NarrowTypes::isType(['foo' => 1, 'bar' => 'test'], 'array{\'foo\': int, "bar"?: string}'));
		assert(NarrowTypes::isType(['foo' => 1], 'array{\'foo\': int, "bar"?: string}'));
		assert(NarrowTypes::isType(['foo' => 1, 'bar' => 'test'], 'array{foo: int, bar: string}'));
		assert(NarrowTypes::isType([1, 3], 'array{int, int}'));

		assert(NarrowTypes::isType(0, 'int<-1, max>'));
		assert(NarrowTypes::isType(new \DateTimeImmutable(), '(\DateTimeInterface&\DateTimeImmutable)|null'));
		assert(NarrowTypes::isType(2, 'FILE_APPEND|FILE_IGNORE_NEW_LINES'));
		assert(NarrowTypes::isType(\DateTime::class, 'class-string<\Exception|\DateTimeInterface>'));
		assert(NarrowTypes::isType('100', 'numeric-string'));
		assert(NarrowTypes::isType('01', 'non-falsy-string'));
		assert(NarrowTypes::isType('abc', 'lowercase-string'));

		assert(NarrowTypes::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{\'foo\': int, "bar": string}'));
		assert(NarrowTypes::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{\'foo\': int, "bar"?: string}'));
		assert(NarrowTypes::isType((object) ['foo' => 1], 'object{\'foo\': int, "bar"?: string}'));
		assert(NarrowTypes::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{foo: int, bar: string}'));
		assert(NarrowTypes::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{foo: int, bar?: string}&\stdClass'));

		assert(NarrowTypes::isType(1.1, '1.1'));
		assert(NarrowTypes::isType('bar', '\'foo\'|\'bar\''));

		assert(NarrowTypes::isType(1, 'int-mask<1, 2, 4>'));
		assert(NarrowTypes::isType(2, 'int-mask<1|2|4>'));
		assert(NarrowTypes::isType(3, 'int-mask-of<Foo::INT_*>'));
		*/

		// list<int>
		self::testIsListInt([1, 2, 3]);

		// list<bool>
		self::testIsListBool([true, false]);

		// list<\DateTimeImmutable>
		self::testIsListObject([new \DateTimeImmutable()]);

		// list<Helpers\FullyQualifiedClassNameResolver>
		self::testIsListFqnObject([new Helpers\FullyQualifiedClassNameResolver()]);

		// list<int|string>
		self::testIsListIntString([1, 'test', 3]);

		// list<array<int, float>>
		self::testIsListArray([[1 => 1.1], [2 => 1.2]]);

		// list<string>|list<null>
		self::testIsListStringOrListNull(['a', 'b']);

		// list<string>|list<null>
		self::testIsListStringOrListNull([null, null]);

		// array<int, string|bool>
		self::testIsArrayIntStringBool([1 => 'A', 2 => true, 3 => 'C']);

		// array<int, string|bool>|null
		self::testIsArrayIntStringBoolNullable([1 => 'A', 2 => true, 3 => 'C']);
		self::testIsArrayIntStringBoolNullable(null);

		// array{foo: string, bar: int}
		self::testIsArrayShape(['foo' => 'A', 'bar' => 1]);
	}


	private static function testIsListInt(mixed $intList): void
	{
		assert(NarrowTypes::isType($intList, 'list<int>'));
		self::checkIsListIntType($intList);
	}


	/**
	 * @param list<int> $intList
	 */
	private static function checkIsListIntType(array $intList): void
	{
		Helper::dump($intList);
	}


	private static function testIsListBool(mixed $boolList): void
	{
		assert(NarrowTypes::isType($boolList, 'list<bool>'));
		self::checkIsListBoolType($boolList);
	}


	/**
	 * @param list<bool> $boolList
	 */
	private static function checkIsListBoolType(array $boolList): void
	{
		Helper::dump($boolList);
	}


	private static function testIsListObject(mixed $objectList): void
	{
		assert(NarrowTypes::isType($objectList, 'list<\DateTimeImmutable>'));
		self::checkIsListObjectType($objectList);
	}


	/**
	 * @param list<\DateTimeImmutable> $objectList
	 */
	private static function checkIsListObjectType(array $objectList): void
	{
		Helper::dump($objectList);
	}


	private static function testIsListFqnObject(mixed $objectList): void
	{
		assert(NarrowTypes::isType($objectList, 'list<Helpers\FullyQualifiedClassNameResolver>'));
		self::checkIsListFqnObjectType($objectList);
	}


	/**
	 * @param list<Helpers\FullyQualifiedClassNameResolver> $objectList
	 */
	private static function checkIsListFqnObjectType(array $objectList): void
	{
		Helper::dump($objectList);
	}


	private static function testIsListIntString(mixed $intStringList): void
	{
		assert(NarrowTypes::isType($intStringList, 'list<int|string>'));
		self::checkIsListIntStringType($intStringList);
	}


	/**
	 * @param list<int|string> $intStringList
	 */
	private static function checkIsListIntStringType(array $intStringList): void
	{
		Helper::dump($intStringList);
	}


	private static function testIsListArray(mixed $arrayList): void
	{
		assert(NarrowTypes::isType($arrayList, 'list<array<int, float>>'));
		self::checkIsListArrayType($arrayList);
	}


	/**
	 * @param list<array<int, float>> $arrayList
	 */
	private static function checkIsListArrayType(array $arrayList): void
	{
		Helper::dump($arrayList);
	}


	private static function testIsListStringOrListNull(mixed $arrayList): void
	{
		assert(NarrowTypes::isType($arrayList, 'list<string>|list<null>'));
		self::checkIsListStringOrListNull($arrayList);
	}


	/**
	 * @param list<string>|list<null> $arrayList
	 */
	private static function checkIsListStringOrListNull(array $arrayList): void
	{
		Helper::dump($arrayList);
	}


	private static function testIsArrayIntStringBool(mixed $arrayIntStringBool): void
	{
		assert(NarrowTypes::isType($arrayIntStringBool, 'array<int, string|bool>'));
		self::checkIsArrayIntStringBoolType($arrayIntStringBool);
	}


	/**
	 * @param array<int, string|bool> $arrayIntStringBool
	 */
	private static function checkIsArrayIntStringBoolType(array $arrayIntStringBool): void
	{
		Helper::dump($arrayIntStringBool);
	}


	private static function testIsArrayIntStringBoolNullable(mixed $arrayIntStringBoolNullable): void
	{
		assert(NarrowTypes::isType($arrayIntStringBoolNullable, 'array<int, string|bool>|null'));
		self::checkIsArrayIntStringBoolTypeNullable($arrayIntStringBoolNullable);
	}


	/**
	 * @param array<int, string|bool>|null $arrayIntStringBoolNullable
	 */
	private static function checkIsArrayIntStringBoolTypeNullable(array|null $arrayIntStringBoolNullable): void
	{
		Helper::dump($arrayIntStringBoolNullable);
	}


	private static function testIsArrayShape(mixed $arrayShape): void
	{
		assert(NarrowTypes::isType($arrayShape, 'array{foo: string, bar: int}'));
		self::checkIsArrayShape($arrayShape);
	}


	/**
	 * @param array{foo: string, bar: int} $arrayIntStringBoolNullable
	 */
	private static function checkIsArrayShape(array $arrayIntStringBoolNullable): void
	{
		Helper::dump($arrayIntStringBoolNullable);
	}

}
