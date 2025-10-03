<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Forrest79\TypeValidator\Helpers;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class SupportedTypesTest
{

	public static function test(): void
	{
		self::assertUnsupportedType('pure-callable');
		self::assertUnsupportedType('pure-closure');
		self::assertUnsupportedType('void');
		self::assertUnsupportedType('never');
		self::assertUnsupportedType('never-return');
		self::assertUnsupportedType('never-returns');
		self::assertUnsupportedType('no-return');
		self::assertUnsupportedType('literal-string');
		self::assertUnsupportedType('non-empty-literal-string');
		self::assertUnsupportedType('self');
		self::assertUnsupportedType('static');
		self::assertUnsupportedType('parent');

		self::assertUnsupportedType('class-string<1>');
		self::assertUnsupportedType('callable(int): int');

		self::assertUnsupportedType('key-of<1>');
		self::assertUnsupportedType('value-of<1>');
		self::assertUnsupportedType('int-mask-of<1>');
		self::assertUnsupportedType('iterable<1>');
		self::assertUnsupportedType('enum-string<1>');
		self::assertUnsupportedType('template-type<1>');
		self::assertUnsupportedType('Collection<1>');
		self::assertUnsupportedType('new<1>');
		self::assertUnsupportedType('static<1>');
		self::assertUnsupportedType('Collection|Type<1>');
		self::assertUnsupportedType('__benevolent<1>');

		self::assertBadDescription('array<int, string, float>');
		self::assertBadDescription('non-empty-array<int, string, float>');

		self::assertBadDescription('list<int, string>');
		self::assertBadDescription('non-empty-list<int, string>');

		self::assertBadDescription('int<1>');
		self::assertBadDescription('int<1, 2, 3>');
		self::assertBadDescription('int<1, low>');

		self::assertBadDescription('int-mask<1, a>');
		self::assertBadDescription('int-mask<1|a>');

		self::assertBadDescription('class-string<\DateTime, \DateTimeImmutable>');
		self::assertBadDescription('interface-string<\Stringagble, \JsonSerializable>');
	}


	private static function assertUnsupportedType(string $type): void
	{
		Assert::exception(static function () use ($type): void {
			self::supportedTypes($type);
		}, TypeValidator\Exceptions\UnsupportedTypeException::class);
	}


	private static function assertBadDescription(string $type): void
	{
		Assert::exception(static function () use ($type): void {
			self::supportedTypes($type);
		}, TypeValidator\Exceptions\BadDescriptionException::class);
	}


	private static function supportedTypes(string $typeDescription): void
	{
		(new Helpers\SupportedTypes(__FILE__, $typeDescription))->checkTypeDescription();
	}

}

Assert::noError(static function (): void {
	SupportedTypesTest::test();
});
