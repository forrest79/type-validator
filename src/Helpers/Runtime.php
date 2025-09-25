<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Helpers;

use Forrest79\PHPStanNarrowTypes\Exceptions;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\Type;

class Runtime
{
	/** @var array<string, bool> */
	private static array $cache = [];

	private string $filename;

	private string $typeDescription;


	public function __construct(string $filename, string $typeDescription)
	{
		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	public function check(mixed $value): bool
	{
		if (is_callable($value)) { // callables can't be serialized
			return $this->checkValue($value);
		} else {
			$serializedValue = serialize($value);
			if (!isset(self::$cache[$serializedValue])) {
				self::$cache[$serializedValue] = $this->checkValue($value);
			}
		}

		return self::$cache[$serializedValue];
	}


	private function checkValue(mixed $value): bool
	{
		return $this->checkTypeNode(PhpDocParser::parseType($this->typeDescription), $value);
	}


	private function checkTypeNode(Type\TypeNode $typeNode, mixed $value): bool
	{
		// https://phpstan.org/writing-php-code/phpdoc-types
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			return match (strtolower($typeNode->name)) { // to lower?
				// Basic types
				'int', 'integer' => is_int($value),
				'string' => is_string($value),
				'non-empty-string' => is_string($value) && $value !== '',
				'non-falsy-string', 'truthy-string' => is_string($value) && (bool) $value,
				'lowercase-string' => is_string($value) && mb_strtolower($value) === $value,
				'uppercase-string' => is_string($value) && mb_strtoupper($value) === $value,
				'numeric-string' => is_string($value) && is_numeric($value),
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
				'callable', 'callable-string' => is_callable($value),
				'open-resource' => is_resource($value), // is_resource returns true only for open resource
				'object' => is_object($value),
				// Mixed
				'mixed' => true,
				// Integer ranges
				'positive-int' => is_int($value) && $value > 0,
				'negative-int' => is_int($value) && $value < 0,
				'non-positive-int' => is_int($value) && $value <= 0,
				'non-negative-int' => is_int($value) && $value >= 0,
				'non-zero-int' => is_int($value) && $value !== 0,
				// Not supported
				'resource', 'closed-resource', 'pure-callable', 'void', 'never', 'never-return', 'never-returns', 'no-return', 'literal-string' => throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode),
				// Classes and interfaces
				'class-string' => is_string($value) && class_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				default => $this->instanceOf($typeNode->name, $value),
			};
		} else if ($typeNode instanceof Type\NullableTypeNode) {
			if ($value === null) {
				return true;
			}

			return $this->checkTypeNode($typeNode->type, $value);
		} else if ($typeNode instanceof Type\ConstTypeNode) {
			$constExpr = $typeNode->constExpr;
			if ($constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
				return is_int($value) && (string) $value === $constExpr->value;
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprFloatNode) {
				return is_float($value) && (string) $value === $constExpr->value;
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprStringNode) {
				return is_string($value) && $value === $constExpr->value;
			} else {
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\ArrayTypeNode) {
			if (!is_array($value)) {
				return false;
			}

			foreach ($value as $item) {
				if (!$this->checkTypeNode($typeNode->type, $item)) {
					return false;
				}
			}

			return true;
		} else if ($typeNode instanceof Type\ArrayShapeNode) {
			if (!is_array($value)) {
				return false;
			}

			$missingKeyIndex = 0;
			foreach ($typeNode->items as $arrayShapeItem) {
				$keyName = $arrayShapeItem->keyName;
				if ($keyName instanceof Ast\ConstExpr\ConstExprIntegerNode) {
					$key = $keyName->value;
				} else if ($keyName instanceof Ast\ConstExpr\ConstExprStringNode) {
					$key = trim($keyName->value, '\'"');
				} else if ($keyName instanceof Type\IdentifierTypeNode) {
					$key = $keyName->name;
				} else {
					$key = $missingKeyIndex++;
				}

				if (!$arrayShapeItem->optional && !array_key_exists($key, $value)) {
					return false;
				} else if (array_key_exists($key, $value) && !$this->checkTypeNode($arrayShapeItem->valueType, $value[$key])) {
					return false;
				}
			}

			return true;
		} else if ($typeNode instanceof Type\ObjectShapeNode) {
			if (!is_object($value)) {
				return false;
			}

			foreach ($typeNode->items as $objectShapeItem) {
				$keyName = $objectShapeItem->keyName;
				if ($keyName instanceof Ast\ConstExpr\ConstExprStringNode) {
					$key = trim($keyName->value, '\'"');
				} else { // Type\IdentifierTypeNode
					$key = $keyName->name;
				}

				if (!$objectShapeItem->optional && !property_exists($value, $key)) {
					return false;
				} else if (property_exists($value, $key) && !$this->checkTypeNode($objectShapeItem->valueType, $value->{$key})) {
					return false;
				}
			}

			return true;
		} else if ($typeNode instanceof Type\GenericTypeNode) {
			$name = strtolower($typeNode->type->name);

			if ($name === 'array' || $name === 'non-empty-array') {
				if (count($typeNode->genericTypes) > 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				if (!is_array($value)) {
					return false;
				}

				if ($name === 'non-empty-array' && $value === []) {
					return false;
				}

				foreach ($value as $key => $item) {
					$checkItems = [$key, $item];
					foreach ($typeNode->genericTypes as $i => $genericType) {
						if (!$this->checkTypeNode($genericType, $checkItems[$i])) {
							return false;
						}
					}
				}
			} else if ($name === 'list' || $name === 'non-empty-list') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				if (!is_array($value) || !array_is_list($value)) {
					return false;
				}

				if ($name === 'non-empty-list' && $value === []) {
					return false;
				}

				foreach ($value as $item) {
					if (!$this->checkTypeNode($typeNode->genericTypes[0], $item)) {
						return false;
					}
				}
			} else if ($name === 'int') {
				if (count($typeNode->genericTypes) !== 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				$limits = [];
				foreach ($typeNode->genericTypes as $genericType) {
					if ($genericType instanceof Type\ConstTypeNode && $genericType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
						$limit = (int) $genericType->constExpr->value;
					} else if ($genericType instanceof Type\IdentifierTypeNode && in_array(strtolower($genericType->name), ['min', 'max'], true)) {
						$limit = strcasecmp($genericType->name, 'min') === 0 ? PHP_INT_MIN : PHP_INT_MAX;
					} else {
						throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
					}

					$limits[] = $limit;
				}

				if (!is_int($value) || $value < $limits[0] || $value > $limits[1]) {
					return false;
				}
			} else if ($name === 'int-mask') {
				foreach ($typeNode->genericTypes as $genericType) {
					if ($this->checkTypeNode($genericType, $value)) {
						return true;
					}
				}

				return false;
			} else if ($name === 'class-string') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				if (!is_string($value)) {
					return false;
				}

				return $this->isClassStringOf($typeNode->genericTypes[0], $value);
			} else { // key-of<...> | value-of<...> | iterable<...> | Collection<...> | Collection|Type[]
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}

			return true;
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				if ($this->checkTypeNode($typeNodeItem, $value)) {
					return true;
				}
			}

			return false;
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				if (!$this->checkTypeNode($typeNodeItem, $value)) {
					return false;
				}
			}

			return true;
		} else if ($typeNode instanceof Type\InvalidTypeNode) {
			throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
		} else { // Type\ThisTypeNode | Type\ConditionalTypeNode | Type\ConditionalTypeForParameterNode | Type\OffsetAccessTypeNode | Type\CallableTypeNode
			throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
		}
	}


	private function instanceOf(string $class, mixed $value): bool
	{
		$fullyQualifiedClassName = FullyQualifiedClassNameResolver::resolve($this->filename, $class);
		return $value instanceof $fullyQualifiedClassName;
	}


	private function isClassStringOf(Type\TypeNode $typeNode, string $value): bool
	{
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			return is_a($value, FullyQualifiedClassNameResolver::resolve($this->filename, $typeNode->name), true);
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			foreach ($typeNode->types as $type) {
				if ($this->isClassStringOf($type, $value)) {
					return true;
				}
			}

			return false;
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $type) {
				if (!$this->isClassStringOf($type, $value)) {
					return false;
				}
			}

			return true;
		} else {
			throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
		}
	}

}
