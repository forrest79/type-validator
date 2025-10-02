<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Forrest79\TypeValidator\Helpers;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class RuntimeTest
{

	public static function test(): void
	{
		// int / integer / float / double / number / numeric / positive-int / negative-int / non-positive-int / non-negative-int / non-zero-int
		assert(TypeValidator::isType(1, 'int'));
		assert(!TypeValidator::isType('a', 'int'));
		assert(TypeValidator::isType(2, 'integer'));
		assert(!TypeValidator::isType('b', 'integer'));
		assert(TypeValidator::isType(2.0, 'float'));
		assert(!TypeValidator::isType(2, 'float'));
		assert(TypeValidator::isType(3.1, 'double'));
		assert(!TypeValidator::isType(3, 'double'));
		assert(TypeValidator::isType(1, 'number'));
		assert(TypeValidator::isType(1.1, 'number'));
		assert(!TypeValidator::isType('a', 'number'));
		assert(TypeValidator::isType(2, 'numeric'));
		assert(TypeValidator::isType(2.2, 'numeric'));
		assert(TypeValidator::isType('3', 'numeric'));
		assert(TypeValidator::isType('3.3', 'numeric'));
		assert(!TypeValidator::isType('a', 'numeric'));
		assert(TypeValidator::isType(1, 'positive-int'));
		assert(!TypeValidator::isType(0, 'positive-int'));
		assert(!TypeValidator::isType(-1, 'positive-int'));
		assert(TypeValidator::isType(-1, 'negative-int'));
		assert(!TypeValidator::isType(0, 'negative-int'));
		assert(!TypeValidator::isType(1, 'negative-int'));
		assert(TypeValidator::isType(-1, 'non-positive-int'));
		assert(TypeValidator::isType(0, 'non-positive-int'));
		assert(!TypeValidator::isType(1, 'non-positive-int'));
		assert(TypeValidator::isType(1, 'non-negative-int'));
		assert(TypeValidator::isType(0, 'non-negative-int'));
		assert(!TypeValidator::isType(-1, 'non-negative-int'));
		assert(TypeValidator::isType(1, 'non-zero-int'));
		assert(TypeValidator::isType(-1, 'non-zero-int'));
		assert(!TypeValidator::isType(0, 'non-zero-int'));

		// string / non-empty-string / non-empty-lowercase-string / non-empty-uppercase-string / truthy-string' / non-falsy-string / lowercase-string / uppercase-string / numeric-string / __stringandstringable
		assert(TypeValidator::isType('A', 'string'));
		assert(!TypeValidator::isType(1, 'string'));
		assert(TypeValidator::isType('B', 'non-empty-string'));
		assert(!TypeValidator::isType('', 'non-empty-string'));
		assert(TypeValidator::isType('c', 'non-empty-lowercase-string'));
		assert(!TypeValidator::isType('C', 'non-empty-lowercase-string'));
		assert(!TypeValidator::isType('', 'non-empty-lowercase-string'));
		assert(TypeValidator::isType('D', 'non-empty-uppercase-string'));
		assert(!TypeValidator::isType('d', 'non-empty-uppercase-string'));
		assert(!TypeValidator::isType('', 'non-empty-uppercase-string'));
		assert(TypeValidator::isType('E', 'truthy-string'));
		assert(!TypeValidator::isType('0', 'truthy-string'));
		assert(TypeValidator::isType('F', 'non-falsy-string'));
		assert(!TypeValidator::isType('', 'non-falsy-string'));
		assert(TypeValidator::isType('g', 'lowercase-string'));
		assert(TypeValidator::isType('', 'lowercase-string'));
		assert(!TypeValidator::isType('G', 'lowercase-string'));
		assert(TypeValidator::isType('H', 'uppercase-string'));
		assert(TypeValidator::isType('', 'uppercase-string'));
		assert(!TypeValidator::isType('h', 'uppercase-string'));
		assert(TypeValidator::isType('1', 'numeric-string'));
		assert(TypeValidator::isType('1.8', 'numeric-string'));
		assert(!TypeValidator::isType('I', 'numeric-string'));
		assert(TypeValidator::isType('K', '__stringandstringable'));
		assert(TypeValidator::isType(new TestStringable(), '__stringandstringable'));
		assert(TypeValidator::isType(new TestToString(), '__stringandstringable'));
		assert(!TypeValidator::isType(1, '__stringandstringable'));

		// bool / true / false / null
		assert(TypeValidator::isType(true, 'bool'));
		assert(!TypeValidator::isType('true', 'bool'));
		assert(TypeValidator::isType(false, 'boolean'));
		assert(!TypeValidator::isType(null, 'boolean'));
		assert(TypeValidator::isType(true, 'true'));
		assert(!TypeValidator::isType(false, 'true'));
		assert(TypeValidator::isType(false, 'false'));
		assert(!TypeValidator::isType(true, 'false'));
		assert(TypeValidator::isType(null, 'null'));
		assert(!TypeValidator::isType(1, 'null'));

		// array / associative-array / non-empty-array / list / non-empty-list / array-key
		assert(TypeValidator::isType([1, 'key' => 2], 'array'));
		assert(!TypeValidator::isType('[1]', 'array'));
		assert(TypeValidator::isType(['key' => 1, 2], 'associative-array'));
		assert(!TypeValidator::isType('[2]', 'associative-array'));
		assert(TypeValidator::isType([1, 3], 'non-empty-array'));
		assert(!TypeValidator::isType([], 'non-empty-array'));
		assert(!TypeValidator::isType('[3]', 'non-empty-array'));
		assert(TypeValidator::isType([1, 'key'], 'list'));
		assert(TypeValidator::isType([], 'list'));
		assert(!TypeValidator::isType([1, 3 => 'key'], 'list'));
		assert(TypeValidator::isType([1, 'key'], 'non-empty-list'));
		assert(!TypeValidator::isType([], 'non-empty-list'));
		assert(!TypeValidator::isType([1, 3 => 'key'], 'non-empty-list'));
		assert(TypeValidator::isType(1, 'array-key'));
		assert(TypeValidator::isType('key', 'array-key'));
		assert(!TypeValidator::isType(null, 'array-key'));

		// enum-string
		assert(TypeValidator::isType('TestEnum', 'enum-string'));
		assert(!TypeValidator::isType(\DateTime::class, 'enum-string'));


		// global constant
		assert(TypeValidator::isType(FILE_APPEND, 'FILE_APPEND'));
		assert(!TypeValidator::isType(1, 'FILE_APPEND'));
/*
				//'scalar' => is_scalar($value), 'scalar' can be also class name, so this type is checked later
				'empty-scalar' => is_scalar($value) && (bool) $value === false,
				'non-empty-scalar' => is_scalar($value) && (bool) $value === true,
				'iterable' => is_iterable($value),
				'callable' => is_callable($value),
				'callable-string' => is_string($value) && is_callable($value),
				'callable-array' => is_array($value) && is_callable($value),
				'callable-object' => is_object($value) && is_callable($value),
				//'resource' =>  is_resource($value) || str_starts_with(get_debug_type($value), 'resource '), // 'resource' can be also class name, so this type is checked later
				'closed-resource' => str_starts_with(get_debug_type($value), 'resource (closed)'),
				'open-resource' => is_resource($value), // is_resource returns true only for open resource
				'object' => is_object($value),
				//'empty' => (bool) $value === false, // 'empty' can be also class name, so this type is checked later
				'mixed' => true,
				'non-empty-mixed' => (bool) $value === true,
				'class-string' => is_string($value) && class_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				'interface-string' => is_string($value) && interface_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				'trait-string' => is_string($value) && trait_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				default => $this->instanceOf($typeNode->name, $value),
			};

			if (!$result) {
				} else if ($typeNodeName === 'scalar') {
					return is_scalar($value);
				} else if ($typeNodeName === 'resource') {
					return is_resource($value) || str_starts_with(get_debug_type($value), 'resource ');
				} else if ($typeNodeName === 'empty') {
					return (bool) $value === false;
				}
			}

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
		assert(TypeValidator::isType($intList, 'list<int>'));
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
		assert(TypeValidator::isType($boolList, 'list<bool>'));
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
		assert(TypeValidator::isType($objectList, 'list<\DateTimeImmutable>'));
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
		assert(TypeValidator::isType($objectList, 'list<Helpers\FullyQualifiedClassNameResolver>'));
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
		assert(TypeValidator::isType($intStringList, 'list<int|string>'));
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
		assert(TypeValidator::isType($arrayList, 'list<array<int, float>>'));
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
		assert(TypeValidator::isType($arrayList, 'list<string>|list<null>'));
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
		assert(TypeValidator::isType($arrayIntStringBool, 'array<int, string|bool>'));
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
		assert(TypeValidator::isType($arrayIntStringBoolNullable, 'array<int, string|bool>|null'));
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
		assert(TypeValidator::isType($arrayShape, 'array{foo: string, bar: int}'));
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


enum TestEnum: int
{
	case A = 1;
}


class TestStringable implements \Stringable
{

	public function __toString(): string
	{
		return self::class;
	}

}


class TestToString
{

	public function __toString(): string
	{
		return self::class;
	}

}

Assert::noError(function (): void {
	RuntimeTest::test();
});
