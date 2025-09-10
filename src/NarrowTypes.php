<?php declare(strict_types=1);

namespace Forrest79;

use Forrest79\NarrowTypes\TypeParser;
use PHPStan\PhpDocParser;
use PHPStan\PhpDocParser\Ast\Type;

/**
 * @phpstan-type Type array{type: string, key?: string, value?: string, class?: string}
 */
final class NarrowTypes
{
	private const HAS_IS_FUNCTION_TYPES = [
		TypeParser::NULL,
		TypeParser::INT,
		TypeParser::FLOAT,
		TypeParser::STRING,
		TypeParser::BOOL,
		TypeParser::CALLABLE,
		TypeParser::OBJECT,
	];


	public static function isType(mixed $value, string $type): bool
	{
		$filename = '';
		foreach (debug_backtrace() as $item) {
			if (!str_starts_with($item['file'] ?? '', __DIR__)) {
				$filename = $item['file'] ?? '';
				break;
			}
		}

		return (new self($filename, $type))->check($value); // cache? filename->type->value
	}

	private string $filename;

	private string $type;

	private PhpDocParser\Ast\Type\TypeNode $typeNode;


	private function __construct(string $filename, string $type)
	{
		$this->filename = $filename;
		$this->type = $type;
		$this->typeNode = TypeParser::parse($type);
	}


	private function check(mixed $value): bool
	{
		return $this->checkTypeNode($this->typeNode, $value);
	}


	private function checkTypeNode(Type\TypeNode $typeNode, mixed $value): bool
	{
		// https://phpstan.org/writing-php-code/phpdoc-types
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			return match ($typeNode->name) { // to lower?
				// Basic types
				'int', 'integer' => is_int($value),
				'string' => is_string($value),
				'array-key' => is_int($value) || is_string($value),
				'bool', 'boolean' => is_bool($value),
				'true' => $value === true,
				'false' => $value === false,
				'null' => $value === null,
				'float' => is_float($value), // alias is is_double
				'double' => is_double($value), // alias is is_float
				'number' => is_int($value) || is_float($value), // || is_double($value), -> alias
				'scalar' => is_scalar($value),
				'array' => is_array($value),
				'iterable' => is_iterable($value),
				'callable', 'pure-callable' => false, // todo
				'resource', 'closed-resource', 'open-resource' => false, // todo
				'void' => false, // todo not supported for isType
				'object' => is_object($value),
				// Mixed
				'mixed' => true,
				// Integer ranges
				'positive-int' => is_int($value) && $value > 0,
				'negative-int' => is_int($value) && $value < 0,
				'non-positive-int' => is_int($value) && $value <= 0,
				'non-negative-int' => is_int($value) && $value >= 0,
				'non-zero-int' => is_int($value) && $value !== 0,
//int<0, 100>
//int<min, 100>
//int<50, max>
				// Classes and interfaces
				default => $this->instanceOf($typeNode->name, $value),
			};
		} else if ($typeNode instanceof Type\ArrayTypeNode) {
			if (is_array($value)) {
				foreach ($value as $item) {
					if (!$this->checkTypeNode($typeNode->type, $item)) {
						return false;
					}
				}
			}
		} else if ($typeNode instanceof Type\ThisTypeNode) {
			throw new \RuntimeException('Not supported type: ' . $typeNode::class);
		} else if ($typeNode instanceof Type\InvalidTypeNode) {
			throw new \RuntimeException('Bad type description: ' . $this->type);
		}

		var_dump($typeNode);

		return true;
	}


	private function instanceOf(string $class, mixed $value): bool
	{
		$fullyQualifiedClassName = NarrowTypes\FullyQualifiedClassNameResolver::resolve($this->filename, $class);
		return $value instanceof $fullyQualifiedClassName;
	}


	public function XcheckType(string $filename, mixed $value, string $type): bool
	{
		foreach (TypeParser::parse($filename, $type) as $parsedType) {
			$checkType = $parsedType['type'];

			if ($checkType === TypeParser::MIXED) {
				return true;
			} else if (in_array($checkType, self::HAS_IS_FUNCTION_TYPES, true)) {
				if (call_user_func('is_' . $checkType, $value) === true) {
					return true;
				}
			} else if ($checkType === TypeParser::ARRAY) {
				if (is_array($value)) {
					if (isset($parsedType['key']) && isset($parsedType['value'])) {
						foreach ($value as $k => $v) {
							if (!self::checkType($filename, $k, $parsedType['key']) || !self::checkType($filename, $v, $parsedType['value'])) {
								continue 2;
							}
						}
					}

					return true;
				}
			} else if ($checkType === TypeParser::LIST) {
				if (is_array($value) && array_is_list($value)) {
					if (isset($parsedType['value'])) {
						foreach ($value as $v) {
							if (!self::checkType($filename, $v, $parsedType['value'])) {
								continue 2;
							}
						}
					}

					return true;
				}
			} else if ($checkType === TypeParser::OBJECT) {
				if (is_object($value)) {
					if (isset($parsedType['class'])) {
						if (!($value instanceof $parsedType['class'])) {
							continue;
						}
					}

					return true;
				}
			} else {
				throw new ShouldNotHappenException(sprintf('Invalid type to check \'%s\'.', $checkType));
			}
		}

		return false;
	}

}
