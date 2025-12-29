<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class RuntimeTest
{

	public static function test(): void
	{
		self::testCheck();
		self::testBasicTypes();
		self::testNullableTypes();
		self::testConstTypes();
		self::testArrayType();
		self::testObjectType();
		self::testIntType();
		self::testClassStringInterfaceStringType();
		self::testComplexType();
	}


	private static function testCheck(): void
	{
		Assert::noError(static function (): void {
			TypeValidator::checkType(1, 'int');
		});

		Assert::error(static function (): void {
			TypeValidator::checkType(1, 'float');
		}, TypeValidator\Exceptions\CheckException::class);
	}


	private static function testBasicTypes(): void
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
		assert(TypeValidator::isType(new TestStringableAndJsonSerialize(), '__stringandstringable'));
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

		// scalar / empty-scalar / non-empty-scalar
		assert(TypeValidator::isType(1, 'scalar'));
		assert(TypeValidator::isType(1.1, 'scalar'));
		assert(TypeValidator::isType(true, 'scalar'));
		assert(TypeValidator::isType('text', 'scalar'));
		assert(!TypeValidator::isType(null, 'scalar'));
		assert(TypeValidator::isType(0, 'empty-scalar'));
		assert(TypeValidator::isType(0.0, 'empty-scalar'));
		assert(TypeValidator::isType(false, 'empty-scalar'));
		assert(TypeValidator::isType('', 'empty-scalar'));
		assert(!TypeValidator::isType(1, 'empty-scalar'));
		assert(TypeValidator::isType(1, 'non-empty-scalar'));
		assert(TypeValidator::isType(0.1, 'non-empty-scalar'));
		assert(TypeValidator::isType(true, 'non-empty-scalar'));
		assert(TypeValidator::isType('text', 'non-empty-scalar'));
		assert(!TypeValidator::isType(0, 'non-empty-scalar'));

		// class-string / interface-string / trait-string / enum-string
		assert(TypeValidator::isType(TestToString::class, 'class-string'));
		assert(!TypeValidator::isType('NonExistingClass', 'class-string'));
		assert(TypeValidator::isType('\Stringable', 'interface-string'));
		assert(!TypeValidator::isType('NonExistingInterface', 'interface-string'));
		assert(TypeValidator::isType(TestTrait::class, 'trait-string'));
		assert(!TypeValidator::isType('NonExistingTrait', 'trait-string'));
		assert(TypeValidator::isType(TestEnum::class, 'enum-string'));
		assert(!TypeValidator::isType('NonExistingEnum', 'enum-string'));

		// iterable
		assert(TypeValidator::isType([1, 2, 3], 'iterable'));
		assert(!TypeValidator::isType('text', 'iterable'));

		// callable / callable-string / callable-array / callable-object
		assert(TypeValidator::isType(static fn (): bool => true, 'callable'));
		assert(!TypeValidator::isType('text', 'callable'));
		assert(TypeValidator::isType('intval', 'callable-string'));
		assert(!TypeValidator::isType('text', 'callable-string'));
		assert(TypeValidator::isType([self::class, 'test'], 'callable-array'));
		assert(!TypeValidator::isType([1, 2], 'callable-array'));
		assert(TypeValidator::isType(\Closure::fromCallable(static fn (): bool => true), 'callable-object'));
		assert(!TypeValidator::isType(new \stdClass(), 'callable-object'));

		// resource / open-resource / closed-resource
		$openResource = fopen('php://memory', 'rw');
		assert($openResource !== false);

		$closedResource = fopen('php://memory', 'rw');
		assert($closedResource !== false);
		fclose($closedResource);

		assert(TypeValidator::isType($openResource, 'resource'));
		assert(TypeValidator::isType($closedResource, 'resource'));
		assert(!TypeValidator::isType(null, 'resource'));
		assert(TypeValidator::isType($openResource, 'open-resource'));
		assert(!TypeValidator::isType($closedResource, 'open-resource'));
		assert(TypeValidator::isType($closedResource, 'closed-resource'));
		assert(!TypeValidator::isType($openResource, 'closed-resource'));

		// object
		assert(TypeValidator::isType(new \stdClass(), 'object'));
		assert(!TypeValidator::isType([], 'object'));
		assert(TypeValidator::isType(new \stdClass(), '\stdClass'));
		assert(TypeValidator::isType(new self(), 'RuntimeTest'));

		// object with is_type() global function
		assert(is_type(new self(), 'RuntimeTest'));

		// mixed / non-empty-mixed
		assert(TypeValidator::isType('a', 'mixed'));
		assert(TypeValidator::isType(1, 'mixed'));
		assert(TypeValidator::isType(1.1, 'mixed'));
		assert(TypeValidator::isType(true, 'mixed'));
		assert(TypeValidator::isType(null, 'mixed'));
		assert(TypeValidator::isType(new \stdClass(), 'mixed'));
		assert(TypeValidator::isType([], 'mixed'));
		assert(TypeValidator::isType($openResource, 'mixed'));
		assert(TypeValidator::isType($closedResource, 'mixed'));
		assert(TypeValidator::isType([1], 'non-empty-mixed'));
		assert(!TypeValidator::isType([], 'non-empty-mixed'));

		// empty
		assert(TypeValidator::isType(0, 'empty'));
		assert(!TypeValidator::isType(1, 'empty'));

		// global constant
		assert(TypeValidator::isType(FILE_APPEND, 'FILE_APPEND'));
		assert(!TypeValidator::isType(1, 'FILE_APPEND'));
	}


	private static function testNullableTypes(): void
	{
		assert(TypeValidator::isType('A', 'string|null'));
		assert(TypeValidator::isType(null, 'string|null'));
		assert(!TypeValidator::isType(1, 'string|null'));
		assert(TypeValidator::isType('B', '?string'));
		assert(TypeValidator::isType(null, '?string'));
		assert(!TypeValidator::isType(2, '?string'));
	}


	private static function testConstTypes(): void
	{
		assert(TypeValidator::isType('A', "'A'"));
		assert(!TypeValidator::isType('B', "'A'"));
		assert(TypeValidator::isType(1, '1'));
		assert(!TypeValidator::isType(2, '1'));
		assert(TypeValidator::isType(1.1, '1.1'));
		assert(!TypeValidator::isType(1.2, '1.1'));
	}


	private static function testArrayType(): void
	{
		// []
		assert(TypeValidator::isType([1], 'int[]'));
		assert(!TypeValidator::isType(['1'], 'int[]'));
		assert(!TypeValidator::isType(1, 'int[]'));

		// Shape
		assert(TypeValidator::isType(['foo' => 1, 'bar' => 'test'], 'array{\'foo\': int, "bar": string}'));
		assert(TypeValidator::isType(['foo' => 1, 'bar' => 'test'], 'array{\'foo\': int, "bar"?: string}'));
		assert(TypeValidator::isType(['foo' => 1, 'bar' => 'test'], 'array{\'foo\': int, bar?: string}'));
		assert(TypeValidator::isType(['foo' => 1], 'array{\'foo\': int, "bar"?: string}'));
		assert(TypeValidator::isType(['foo' => 1, 'bar' => 'test'], 'array{foo: int, bar: string}'));
		assert(TypeValidator::isType([1, 3], 'array{int, int}'));
		assert(TypeValidator::isType([1, 3], 'array{0: int, 1: int}'));
		assert(!TypeValidator::isType(1, 'array{foo: int, bar: string}'));
		assert(!TypeValidator::isType(['foo' => 1], 'array{foo: int, bar: string}'));
		assert(TypeValidator::isType(['foo' => 1], 'array{foo: int, bar?: string}'));
		assert(!TypeValidator::isType(['foo' => '1'], 'array{foo: int}'));

		// Generic
		assert(TypeValidator::isType([1, 2], 'array<int>'));
		assert(TypeValidator::isType([1, 2], 'array<int, int>'));
		assert(!TypeValidator::isType([1, 2], 'array<string, int>'));
		assert(!TypeValidator::isType([1, 2], 'array<int, string>'));
		assert(!TypeValidator::isType([], 'non-empty-array<int>'));
		assert(!TypeValidator::isType(1, 'array<int>'));
		assert(TypeValidator::isType([1, 2], 'list<int>'));
		assert(TypeValidator::isType(['1', 2], 'list<int|string>'));
		assert(!TypeValidator::isType([1], 'list<string>'));
		assert(!TypeValidator::isType([1 => 1], 'list<int>'));
		assert(!TypeValidator::isType(1, 'list<int>'));
		assert(!TypeValidator::isType([], 'non-empty-list<int>'));
	}


	private static function testObjectType(): void
	{
		// Shape
		assert(TypeValidator::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{\'foo\': int, "bar": string}'));
		assert(TypeValidator::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{\'foo\': int, "bar"?: string}'));
		assert(TypeValidator::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{\'foo\': int, bar?: string}'));
		assert(TypeValidator::isType((object) ['foo' => 1], 'object{\'foo\': int, "bar"?: string}'));
		assert(TypeValidator::isType((object) ['foo' => 1, 'bar' => 'test'], 'object{foo: int, bar: string}'));
		assert(!TypeValidator::isType(1, 'object{foo: int, bar: string}'));
		assert(!TypeValidator::isType((object) ['foo' => 1], 'object{foo: int, bar: string}'));
		assert(!TypeValidator::isType((object) ['foo' => '1'], 'object{foo: int}'));

		// Intersection
		assert(TypeValidator::isType(TestEnum::A, '\BackedEnum&\UnitEnum'));
		assert(!TypeValidator::isType(TestEnum::A, '\BackedEnum&\DateTime'));
	}


	private static function testIntType(): void
	{
		// Generic
		assert(TypeValidator::isType(1, 'int<0, 2>'));
		assert(TypeValidator::isType(1, 'int<min, 2>'));
		assert(TypeValidator::isType(1, 'int<0, max>'));
		assert(!TypeValidator::isType(0, 'int<1, 2>'));

		// Mask
		assert(TypeValidator::isType(0, 'int-mask<1, 2>'));
		assert(TypeValidator::isType(0, 'int-mask<0, 1, 2>'));
		assert(TypeValidator::isType(1, 'int-mask<1, 2>'));
		assert(TypeValidator::isType(3, 'int-mask<1|2>'));
		assert(!TypeValidator::isType(4, 'int-mask<1|2>'));
	}


	private static function testClassStringInterfaceStringType(): void
	{
		// Generic
		assert(TypeValidator::isType(\DateTime::class, 'class-string<\DateTime>'));
		assert(!TypeValidator::isType(\DateTimeImmutable::class, 'class-string<\DateTime>'));
		assert(TypeValidator::isType(\DateTime::class, 'class-string<\DateTime|\DateTimeImmutable>'));
		assert(TypeValidator::isType(\DateTimeImmutable::class, 'class-string<\DateTime|\DateTimeImmutable>'));
		assert(!TypeValidator::isType(TestToString::class, 'class-string<\DateTime|\DateTimeImmutable>'));
		assert(!TypeValidator::isType(1, 'class-string<\DateTime>'));
		assert(TypeValidator::isType(TestStringableAndJsonSerialize::class, 'interface-string<\Stringable>'));
		assert(TypeValidator::isType(TestStringableAndJsonSerialize::class, 'interface-string<\Stringable&\JsonSerializable>'));
		assert(!TypeValidator::isType(self::class, 'interface-string<\JsonSerializable>'));
	}


	private static function testComplexType(): void
	{
		assert(TypeValidator::isType([['foo' => 1, 'bar' => 'test']], 'list<array{foo: int, bar: string}>'));
	}

}


enum TestEnum: int
{
	case A = 1;

}


class TestStringableAndJsonSerialize implements \Stringable, \JsonSerializable
{

	public function __toString(): string
	{
		return self::class;
	}


	public function jsonSerialize(): mixed
	{
		return [];
	}

}


class TestToString
{

	public function __toString(): string
	{
		return self::class;
	}

}


trait TestTrait
{

}

RuntimeTest::test();
