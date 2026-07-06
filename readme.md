# TypeValidator

[![Latest Stable Version](https://poser.pugx.org/forrest79/type-validator/v)](//packagist.org/packages/forrest79/type-validator)
[![Monthly Downloads](https://poser.pugx.org/forrest79/type-validator/d/monthly)](//packagist.org/packages/forrest79/type-validator)
[![License](https://poser.pugx.org/forrest79/type-validator/license)](//packagist.org/packages/forrest79/type-validator)
[![Build](https://github.com/forrest79/type-validator/actions/workflows/build.yml/badge.svg?branch=master)](https://github.com/forrest79/type-validator/actions/workflows/build.yml)
[![codecov](https://codecov.io/gh/forrest79/type-validator/graph/badge.svg?token=SYJCJXCZ6I)](https://codecov.io/gh/forrest79/type-validator)

## Introduction

Validates types using PHP Doc descriptions and narrows types for [PHPStan](https://phpstan.org/).

Imagine you're loading data from some external source. For PHP, this is mostly `mixed` (or some other common type like `array`/`object`), and PHPStan is unhappy with this. If data is some simple type, most of us will add something like:

```php
assert(is_int($data)); // if we know, there will be always an int
if (!is_int($data)) throw new InvalidDataException(); // if we want to check this also in runtime
```

Both make PHPStan happy, and you code is also tested (the first example mostly in dev environment, where the assertion is on).

But when the loaded data is a complex type like `list<array{type: int, dates?: array<string, \DateTime>, validator: class-string<IValidator>}>`.

Checking this at runtime and making PHPStan happy is now harder. The goal of this library is to make this as simple as `assert(is_int($data))`.

Use `assert(is_type($data, 'list<array{type: int, dates?: array<string, \DateTime>, validator: class-string<IValidator>}>'))` and the variable is really checked for the correct type at runtime, and the type is also narrowed for PHPStan.

> Code coverage is computed without PHPStan extension - only the PHP runtime part.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev forrest79/type-validator
```

> You probably only want this extension for development, but it can also be used in production (omit `--dev`).

## Using

There is one global function `is_type(mixed $var, string $type)` and static methods `Forrest79\TypeValidator::isType(mixed $var, string $type): bool` or `Forrest79\TypeValidator::checkType(mixed $var, string $type): void`.

All of them really check the data in `$var` against the type description and there is corresponding PHPStan extension so PHPStan will understand, that `$var` is in described type. 

The function `is_type(mixed $var, string $type)` and method `Forrest79\TypeValidator::isType(mixed $var, string $type)` return a `bool` - true if `$var` matches the `$type`, and `false` otherwise.

The Method `Forrest79\TypeValidator::checkType(mixed $var, string $type)` has no return, but it throws a `CheckException`, if `$var` does not match the `$type`.

Example:

```php
$arr = [3.14, 5, 10];
assert(is_type($arr, 'list<float|int>'));
assert(Forrest79\TypeValidator::isType($arr, 'list<float|int>'));
Forrest79\TypeValidator::checkType($arr, 'list<float|int>'));
```

With this you can replace your `@var` annotations:

```php
/** @var array<string|int, list<Db\Row>> $arr
$arr = json_decode($data);
```

With:

```php
$arr = json_decode($data);
assert(is_type($arr, 'array<string|int, list<Db\Row>>'));
```

The benefit is that variable `$arr` is checked for defined type.

Almost all PHPDoc types from PHPStan are supported (more information about supported types is provided later in the docs).

To use this library as PHPStan extension include `extension.neon` in your project's PHPStan config:

```yaml
includes:
    - vendor/forrest79/type-validator/extension.neon
```

> Because of PHPStan, the type description must be a static string—nothing can be generated dynamically.

### Use in production

Typically, the `assert` function is disabled in production, so checks are only performed in development/test environments, and there is no need to distribute this library in a production environment.

But you can use this for validation also in your production code. Parsing PHPDoc types is not too performance-intensive. This library depends on `phpstan/phpdoc-parser` for parsing types and `nikic/php-parser` for detection fully qualified class names.   


### FQN (Fully qualified names)

Correct fully qualified names are computed from the current namespace and `use` statements, just like every other item in your PHP source files. However, if you use a `use` statement only for this library, your IDE and PHPCS may mark it as unused because they don't know about this library:

Example:

```php
namespace App;

use App\Presenter; // this use is marked as unused

assert($presenter, 'class-string<Presenter>'); // even though it is correctly used here
```

One solution is to concatenate the type string with `::class` such as `assert($presenter, 'class-string<\\' . Presenter::class . '>')`. However, this looks very ugly. I prefer to use an FQN in the type description and omit the `use` statement:

```php
namespace App;

assert($presenter, 'class-string<\App\Presenter>');
```


### Supported PHPStan - PHPDoc Types

According to https://github.com/phpstan/phpstan/blob/2.1.x/website/src/writing-php-code/phpdoc-types.md

✅ supported
🚫 not supported - doesn't make sense for variables
❌ not supported

#### Basic types ✅/🚫/❌

- `int`, `integer` ✅
- `string`, `non-empty-string`, `non-empty-lowercase-string`, `non-empty-uppercase-string`, `truthy-string`, `non-falsy-string`, `lowercase-string`, `uppercase-string` ✅
- `literal-string`, `non-empty-literal-string` ❌
- `numeric-string` ✅
- `__stringandstringable` (`string` or object implementing `Stringable` interface or object with `__toString()` method) ✅
- `array-key` ✅
- `bool`, `boolean`, `true`, `false` ✅
- `null` ✅
- `float`, `double` ✅
- `number`, `numeric` ✅
- `scalar`, `empty-scalar`, `non-empty-scalar` ✅
- `array`, `associative-array`, `non-empty-array` ✅
- `list`, `non-empty-list` ✅
- `iterable` ✅
- `callable`, `callable-string`, `callable-array`, `callable-object` ✅, `pure-callable` ❌
- `resource`, `open-resource`, `closed-resource` ✅
- `object` ✅
- `empty` ✅
- `mixed`, `non-empty-mixed` ✅
- `class-string`, `interface-string`, `trait-string`, `enum-string` ✅
- `void` 🚫

#### Classes and interfaces ✅

#### Integer ranges ✅

- `positive-int` ✅
- `negative-int` ✅
- `non-positive-int` ✅
- `non-negative-int` ✅
- `non-zero-int` ✅
- `int<0, 100>` ✅
- `int<min, 100>` ✅
- `int<50, max>` ✅

#### General arrays ✅

- `Type[]` ✅
- `array<Type>` ✅
- `array<int, Type>` ✅
- `non-empty-array<Type>` ✅
- `non-empty-array<int, Type>` ✅

#### Lists ✅

- `list<Type>` ✅
- `non-empty-list<Type>` ✅

#### Key and value types of arrays and iterables ❌

- `key-of<Type::ARRAY_CONST>` ❌
- `value-of<Type::ARRAY_CONST>` ❌
- `value-of<BackedEnum>` ❌

#### Iterables ❌ (there can be some side effect while iterate in runtime to check correct type)

- `iterable<Type>` ❌
- `Collection<Type>` ❌
- `Collection<int, Type>` ❌
- `Collection|Type[]` ❌

#### Union types ✅

- `Type1|Type2` ✅

#### Intersection types ✅

- `Type1&Type2` ✅

#### Parentheses ✅

- `(Type1&Type2)|Type3` ✅

#### self, static, parent and $this 🚫

- `self`, `static`, `parent` or `$this` 🚫

#### Generics ✅/🚫/❌ (some yes, some no, some doesn't make sense - concrete info can be found in the other types description) 

#### Conditional return types 🚫

#### Utility types for generics ❌

- `template-type` ❌
- `new` ❌

#### class-string, interface-string ✅

- `class-string<Foo>` ✅
- `interface-string<Interface>` ✅

#### Global type aliases ❌

#### Local type aliases ❌

#### Array shapes ✅

- `array{'foo': int, "bar": string}` ✅
- `array{'foo': int, "bar"?: string}` ✅
- `array{int, int}` ✅
- `array{0: int, 1?: int}` ✅
- `array{foo: int, bar: string}` ✅

#### Object shapes ✅

- `object{'foo': int, "bar": string}` ✅
- `object{'foo': int, "bar"?: string}` ✅
- `object{foo: int, bar?: string}` ✅
- `object{foo: int, bar?: string}&\stdClass` ✅

#### Literals and constants  ✅/❌

- `234` ✅
- `1.0` ✅
- `'foo'|'bar'` ✅
- `Foo::SOME_CONSTANT` ❌
- `Foo::SOME_CONSTANT|Bar::OTHER_CONSTANT` ❌
- `self::SOME_*` ❌
- `Foo::*` ❌

#### Global constants ✅

- `SOME_CONSTANT` ✅
- `SOME_CONSTANT|OTHER_CONSTANT` ✅

#### Callables ❌ (only simple callable is supported)

- `callable(int, int): string` ❌
- `callable(int, int=): string` ❌
- `callable(int $foo, string $bar): void` ❌
- `callable(string &$bar): mixed` ❌
- `callable(float ...$floats): (int|null)` ❌
- `callable(float...): (int|null)` ❌
- `\Closure(int, int): string` ❌
- `pure-callable(int, int): string` ❌
- `pure-closure(int, int): string` ❌
- `static-closure(int, int): string` ❌
- `static-pure-closure(int, int): string` ❌

#### Bottom type 🚫

- `never` 🚫
- `never-return` 🚫
- `never-returns` 🚫
- `no-return` 🚫

#### Integer masks ✅/❌

- `int-mask<1, 2, 4>` ✅
- `int-mask-of<1|2|4>` ✅
- `int-mask-of<Foo::INT_*>` ❌

#### Offset access ❌
