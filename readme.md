# PHPStan narrowing complex array/list types with assert

[![Latest Stable Version](https://poser.pugx.org/forrest79/phpstan-narrow-types/v)](//packagist.org/packages/forrest79/phpstan-narrow-types)
[![Monthly Downloads](https://poser.pugx.org/forrest79/phpstan-narrow-types/d/monthly)](//packagist.org/packages/forrest79/phpstan-narrow-types)
[![License](https://poser.pugx.org/forrest79/phpstan-narrow-types/license)](//packagist.org/packages/forrest79/phpstan-narrow-types)
[![Build](https://github.com/forrest79/phpstan-narrow-types/actions/workflows/build.yml/badge.svg?branch=master)](https://github.com/forrest79/phpstan-narrow-types/actions/workflows/build.yml)

## Introduction

Check complex array/list types in runtime (via `assert`) and narrow variables for [PHPStan](https://phpstan.org/).

> This library is "in proof of concept" state. It could be changed or canceled anytime. Source code and tests are written in "it is working just fine" way. Parsing types is very naive and is not well tested.

The goal of this library is to get rid of `@var` annotations. You can check complex array/list types with `assert` PHP function in the same way as for the simple types (`assert(is_int($var));`).

> Array/object shapes are not supported. Arrays must be defined with `<...>`, `[]` syntax is not supported.

There is one global function `is_type(mixed $var, string $type)` or static method `Forrest79\NarrowTypes::isType(mixed $var, string $type)`. That really check data in `$var` against the type description and there is corresponding PHPStan extension so PHPStan will understand, that `$var` is in described type. 

Example:

```php
$arr = [3.14, 5, 10];
assert(is_type($arr, 'list<float|int>'));
```

In common the `assert` function is disabled on production, so check is performed only on devel/test environments and there is no need to distribute this library on the production environment. 

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev forrest79/phpstan-narrow-types
```

> You probably want this extension only for dev.

## Using

All simple types with correspondent `is_...` function are supported `null`, `int`, `float`, `string`, `bool`, `callable` and `object`. Arrays `array` and lists `list` can be defined with specified types with classic `<...>` syntax (known from PHPStan).

You can also use `|` operator like `int|string`.

> All strings that are not matched as an internal simple types are resolved as object name. Object name can be defined with whole namespace (when starting with `\`) or is completed from actual class namespace/use in a classic way (thanks to `nikic/php-parser`). 

Simple example:

```php
assert(is_type($var, 'int'));
assert(is_type($var, 'int|string'));
```

> For simple types is recommended to use internal PHP functions `is_...`.

The main goal is to replace something like this:

```php
/** @var array<string|int, list<Db\Row>> $arr
$arr = json_decode($data);
```

With:

```php
$arr = json_decode($data);
assert(is_type($arr, 'array<string|int, list<Db\Row>>'));
```

With the benefit variable `$arr` is checked for defined type.

To use this library as PHPStan extension include `extension.neon` in your project's PHPStan config:

```yaml
includes:
    - vendor/forrest79/phpstan-narrow-types/extension.neon
```

### FQN

To don't have unused `use` statement you can concat type string with `::class`.

### Supported PHPStan - PHPDoc Types

According to https://github.com/phpstan/phpstan/blob/2.1.x/website/src/writing-php-code/phpdoc-types.md

✅ supported
🚫 not supported - doesn't make sense for variables
❌ not supported

#### Basic types ✅/🚫/❌

- `int`, `integer` ✅
- `string` ✅
- `array-key` ✅
- `bool`, `boolean` ✅
- `true` ✅
- `false` ✅
- `null` ✅
- `float` ✅
- `double` ✅
- `number` ✅
- `scalar` ✅
- `array` ✅
- `iterable` ✅
- `callable` ✅, `pure-callable` ❌
- `resource` ✅, `closed-resource` ✅, `open-resource` ✅
- `void` 🚫
- `object` ✅

#### Mixed ✅

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

#### static and $this 🚫

- `static` or `$this` 🚫

#### Generics ✅/🚫/❌ (some yes, some no, some doesn't make sense - concrete info can be found in the other types description) 

#### Conditional return types 🚫

#### Utility types for generics ❌

- `template-type` ❌
- `new` ❌

#### class-string ✅

- `class-string<Foo>` ✅


#### Other advanced string types ✅/❌

- `callable-string` ✅
- `numeric-string` ✅
- `non-empty-string` ✅
- `non-falsy-string` ✅
- `literal-string` ❌
- `lowercase-string` ✅
- `uppercase-string` ✅

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
- `pure-Closure(int, int): string` ❌

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
