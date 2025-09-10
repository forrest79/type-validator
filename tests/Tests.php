<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Tests;

use Forrest79\NarrowTypes;

final class Tests
{

	public static function testAll(): void
	{
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
		assert(NarrowTypes::isType(1, 'int<0, 100>'));
exit;
		// list<int>
		self::classIsListInt([1, 2, 3]);
		self::functionIsListInt([1, 2, 3]);

		// list<bool>
		self::classIsListBool([true, false]);
		self::functionIsListBool([true, false]);

		// list<\DateTimeImmutable>
		self::classIsListObject([new \DateTimeImmutable()]);
		self::functionIsListObject([new \DateTimeImmutable()]);

		// list<NarrowTypes\FullyQualifiedClassNameResolver>
		self::classIsListFqnObject([new NarrowTypes\FullyQualifiedClassNameResolver()]);
		self::functionIsListFqnObject([new NarrowTypes\FullyQualifiedClassNameResolver()]);

		// list<int|string>
		self::classIsListIntString([1, 'test', 3]);
		self::functionIsListIntString([1, 'test', 3]);

		// list<array<int, float>>
		self::classIsListArray([[1 => 1.1], [2 => 1.2]]);
		self::functionIsListArray([[1 => 1.1], [2 => 1.2]]);

		// list<string>|list<null>
		self::classIsListStringOrListNull(['a', 'b']);
		self::functionIsListStringOrListNull(['a', 'b']);

		// list<string>|list<null>
		self::classIsListStringOrListNull([null, null]);
		self::functionIsListStringOrListNull([null, null]);

		// Arrays

		// array<int, string|bool>
		self::classIsArrayIntStringBool([1 => 'A', 2 => true, 3 => 'C']);
		self::aunctionIsArrayIntStringBool([1 => 'A', 2 => true, 3 => 'C']);

		// array<int, string|bool>|null
		self::classIsArrayIntStringBoolNullable([1 => 'A', 2 => true, 3 => 'C']);
		self::aunctionIsArrayIntStringBoolNullable([1 => 'A', 2 => true, 3 => 'C']);
		self::classIsArrayIntStringBoolNullable(null);
		self::aunctionIsArrayIntStringBoolNullable(null);
	}


	private static function classIsListInt(mixed $intList): void
	{
		assert(NarrowTypes::isType($intList, 'list<int>'));
		self::arrayIsListIntType($intList);
	}


	private static function functionIsListInt(mixed $intList): void
	{
		assert(is_type($intList, 'list<int>'));
		self::arrayIsListIntType($intList);
	}


	/**
	 * @param list<int> $intList
	 */
	private static function arrayIsListIntType(array|int $intList): void
	{
		var_dump($intList);
	}


	private static function classIsListBool(mixed $boolList): void
	{
		assert(NarrowTypes::isType($boolList, 'list<bool>'));
		self::arrayIsListBoolType($boolList);
	}


	private static function functionIsListBool(mixed $boolList): void
	{
		assert(is_type($boolList, 'list<bool>'));
		self::arrayIsListBoolType($boolList);
	}


	/**
	 * @param list<bool> $boolList
	 */
	private static function arrayIsListBoolType(array $boolList): void
	{
		var_dump($boolList);
	}


	private static function classIsListObject(mixed $objectList): void
	{
		assert(NarrowTypes::isType($objectList, 'list<\DateTimeImmutable>'));
		self::arrayIsListObjectType($objectList);
	}


	private static function functionIsListObject(mixed $objectList): void
	{
		assert(is_type($objectList, 'list<\DateTimeImmutable>'));
		self::arrayIsListObjectType($objectList);
	}


	/**
	 * @param list<\DateTimeImmutable> $objectList
	 */
	private static function arrayIsListObjectType(array $objectList): void
	{
		var_dump($objectList);
	}


	private static function classIsListFqnObject(mixed $objectList): void
	{
		assert(NarrowTypes::isType($objectList, 'list<NarrowTypes\FullyQualifiedClassNameResolver>'));
		self::arrayIsListFqnObjectType($objectList);
	}


	private static function functionIsListFqnObject(mixed $objectList): void
	{
		assert(is_type($objectList, 'list<NarrowTypes\FullyQualifiedClassNameResolver>'));
		self::arrayIsListFqnObjectType($objectList);
	}


	/**
	 * @param list<NarrowTypes\FullyQualifiedClassNameResolver> $objectList
	 */
	private static function arrayIsListFqnObjectType(array $objectList): void
	{
		var_dump($objectList);
	}


	private static function classIsListIntString(mixed $intStringList): void
	{
		assert(NarrowTypes::isType($intStringList, 'list<int|string>'));
		self::arrayIsListIntStringType($intStringList);
	}


	private static function functionIsListIntString(mixed $intStringList): void
	{
		assert(is_type($intStringList, 'list<int|string>'));
		self::arrayIsListIntStringType($intStringList);
	}


	/**
	 * @param list<int|string> $intStringList
	 */
	private static function arrayIsListIntStringType(array $intStringList): void
	{
		var_dump($intStringList);
	}


	private static function classIsListArray(mixed $arrayList): void
	{
		assert(NarrowTypes::isType($arrayList, 'list<array<int, float>>'));
		self::arrayIsListArrayType($arrayList);
	}


	private static function functionIsListArray(mixed $arrayList): void
	{
		assert(is_type($arrayList, 'list<array<int, float>>'));
		self::arrayIsListArrayType($arrayList);
	}


	/**
	 * @param list<array<int, float>> $arrayList
	 */
	private static function arrayIsListArrayType(array $arrayList): void
	{
		var_dump($arrayList);
	}


	private static function classIsListStringOrListNull(mixed $arrayList): void
	{
		assert(NarrowTypes::isType($arrayList, 'list<string>|list<null>'));
		self::arrayIsListStringOrListNull($arrayList);
	}


	private static function functionIsListStringOrListNull(mixed $arrayList): void
	{
		assert(is_type($arrayList, 'list<string>|list<null>'));
		self::arrayIsListStringOrListNull($arrayList);
	}


	/**
	 * @param list<string>|list<null> $arrayList
	 */
	private static function arrayIsListStringOrListNull(array $arrayList): void
	{
		var_dump($arrayList);
	}


	private static function classIsArrayIntStringBool(mixed $arrayIntStringBool): void
	{
		assert(NarrowTypes::isType($arrayIntStringBool, 'array<int, string|bool>'));
		self::arrayIsArrayIntStringBoolType($arrayIntStringBool);
	}


	private static function aunctionIsArrayIntStringBool(mixed $arrayIntStringBool): void
	{
		assert(is_type($arrayIntStringBool, 'array<int, string|bool>'));
		self::arrayIsArrayIntStringBoolType($arrayIntStringBool);
	}


	/**
	 * @param array<int, string|bool> $arrayIntStringBool
	 */
	private static function arrayIsArrayIntStringBoolType(array $arrayIntStringBool): void
	{
		var_dump($arrayIntStringBool);
	}


	private static function classIsArrayIntStringBoolNullable(mixed $arrayIntStringBoolNullable): void
	{
		assert(NarrowTypes::isType($arrayIntStringBoolNullable, 'array<int, string|bool>|null'));
		self::arrayIsArrayIntStringBoolTypeNullable($arrayIntStringBoolNullable);
	}


	private static function aunctionIsArrayIntStringBoolNullable(mixed $arrayIntStringBoolNullable): void
	{
		assert(is_type($arrayIntStringBoolNullable, 'array<int, string|bool>|null'));
		self::arrayIsArrayIntStringBoolTypeNullable($arrayIntStringBoolNullable);
	}


	/**
	 * @param array<int, string|bool>|null $arrayIntStringBoolNullable
	 */
	private static function arrayIsArrayIntStringBoolTypeNullable(array|null $arrayIntStringBoolNullable): void
	{
		var_dump($arrayIntStringBoolNullable);
	}

}
