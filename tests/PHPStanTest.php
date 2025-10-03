<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Forrest79\TypeValidator\Helpers;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class PHPStanTest
{

	public static function test(): void
	{
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

		// object
		self::testIsObject(new \stdClass());

		// object{foo: int, bar?: string}
		self::testIsObjectShape((object) ['foo' => 1]);
		self::testIsObjectShape((object) ['foo' => 1, 'bar' => 'test']);

		// open-resource
		$resource = fopen('php://memory', 'rw');
		assert($resource !== false);

		self::testIsOpenResource($resource);

		// close-resource
		fclose($resource);
		self::testIsCloseResource($resource);
	}


	private static function testIsListInt(mixed $intList): void
	{
		assert(TypeValidator::isType($intList, 'list<int>'));
		self::checkIsListIntType($intList);
	}


	/**
	 * @param list<int> $intList
	 */
	private static function checkIsListIntType(array $intList): void
	{
		var_dump($intList);
	}


	private static function testIsListBool(mixed $boolList): void
	{
		assert(TypeValidator::isType($boolList, 'list<bool>'));
		self::checkIsListBoolType($boolList);
	}


	/**
	 * @param list<bool> $boolList
	 */
	private static function checkIsListBoolType(array $boolList): void
	{
		var_dump($boolList);
	}


	private static function testIsListObject(mixed $objectList): void
	{
		assert(TypeValidator::isType($objectList, 'list<\DateTimeImmutable>'));
		self::checkIsListObjectType($objectList);
	}


	/**
	 * @param list<\DateTimeImmutable> $objectList
	 */
	private static function checkIsListObjectType(array $objectList): void
	{
		var_dump($objectList);
	}


	private static function testIsListFqnObject(mixed $objectList): void
	{
		assert(TypeValidator::isType($objectList, 'list<Helpers\FullyQualifiedClassNameResolver>'));
		self::checkIsListFqnObjectType($objectList);
	}


	/**
	 * @param list<Helpers\FullyQualifiedClassNameResolver> $objectList
	 */
	private static function checkIsListFqnObjectType(array $objectList): void
	{
		var_dump($objectList);
	}


	private static function testIsListIntString(mixed $intStringList): void
	{
		assert(TypeValidator::isType($intStringList, 'list<int|string>'));
		self::checkIsListIntStringType($intStringList);
	}


	/**
	 * @param list<int|string> $intStringList
	 */
	private static function checkIsListIntStringType(array $intStringList): void
	{
		var_dump($intStringList);
	}


	private static function testIsListArray(mixed $arrayList): void
	{
		assert(TypeValidator::isType($arrayList, 'list<array<int, float>>'));
		self::checkIsListArrayType($arrayList);
	}


	/**
	 * @param list<array<int, float>> $arrayList
	 */
	private static function checkIsListArrayType(array $arrayList): void
	{
		var_dump($arrayList);
	}


	private static function testIsListStringOrListNull(mixed $arrayList): void
	{
		assert(TypeValidator::isType($arrayList, 'list<string>|list<null>'));
		self::checkIsListStringOrListNull($arrayList);
	}


	/**
	 * @param list<string>|list<null> $arrayList
	 */
	private static function checkIsListStringOrListNull(array $arrayList): void
	{
		var_dump($arrayList);
	}


	private static function testIsArrayIntStringBool(mixed $arrayIntStringBool): void
	{
		assert(TypeValidator::isType($arrayIntStringBool, 'array<int, string|bool>'));
		self::checkIsArrayIntStringBoolType($arrayIntStringBool);
	}


	/**
	 * @param array<int, string|bool> $arrayIntStringBool
	 */
	private static function checkIsArrayIntStringBoolType(array $arrayIntStringBool): void
	{
		var_dump($arrayIntStringBool);
	}


	private static function testIsArrayIntStringBoolNullable(mixed $arrayIntStringBoolNullable): void
	{
		assert(TypeValidator::isType($arrayIntStringBoolNullable, 'array<int, string|bool>|null'));
		self::checkIsArrayIntStringBoolTypeNullable($arrayIntStringBoolNullable);
	}


	/**
	 * @param array<int, string|bool>|null $arrayIntStringBoolNullable
	 */
	private static function checkIsArrayIntStringBoolTypeNullable(array|null $arrayIntStringBoolNullable): void
	{
		var_dump($arrayIntStringBoolNullable);
	}


	private static function testIsArrayShape(mixed $arrayShape): void
	{
		assert(TypeValidator::isType($arrayShape, 'array{foo: string, bar: int}'));
		self::checkIsArrayShape($arrayShape);
	}


	/**
	 * @param array{foo: string, bar: int} $arrayShape
	 */
	private static function checkIsArrayShape(array $arrayShape): void
	{
		var_dump($arrayShape);
	}


	private static function testIsObject(mixed $object): void
	{
		assert(TypeValidator::isType($object, 'object'));
		self::checkIsObject($object);
	}


	private static function checkIsObject(object $object): void
	{
		var_dump($object);
	}


	private static function testIsObjectShape(mixed $objectShape): void
	{
		assert(TypeValidator::isType($objectShape, 'object{foo: int, bar?: string}'));
		self::checkIsObjectShape($objectShape);
	}


	/**
	 * @param object{foo: int, bar?: string} $objectShape
	 */
	private static function checkIsObjectShape(object $objectShape): void
	{
		var_dump($objectShape);
	}


	private static function testIsOpenResource(mixed $openResource): void
	{
		assert(TypeValidator::isType($openResource, 'open-resource'));
		self::checkIsOpenResource($openResource);
	}


	/**
	 * @param open-resource $openResource
	 */
	private static function checkIsOpenResource(mixed $openResource): void
	{
		var_dump($openResource);
	}


	private static function testIsCloseResource(mixed $closedResource): void
	{
		assert(TypeValidator::isType($closedResource, 'closed-resource'));
		self::checkIsCloseResource($closedResource);
	}


	/**
	 * @param closed-resource $closedResource
	 */
	private static function checkIsCloseResource(mixed $closedResource): void
	{
		var_dump($closedResource);
	}

}

Assert::noError(static function (): void {
	PHPStanTest::test();
});
