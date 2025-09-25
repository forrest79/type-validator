<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Helpers;

use Forrest79\PHPStanNarrowTypes\Exceptions;
use PHPStan\PhpDocParser\Ast;

class Runtime
{
	private string $filename;

	private string $typeDescription;


	public function __construct(string $filename, string $typeDescription)
	{
		SupportedTypes::check($filename, $typeDescription);

		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	public function check(mixed $value): bool
	{
		return $this->checkTypeNode(PhpDocParser::parseType($this->typeDescription), $value);
	}


	private function checkTypeNode(Ast\Type\TypeNode $typeNode, mixed $value): bool
	{
		// PHPStan source - src/PhpDoc/TypeNodeResolver.php + https://phpstan.org/writing-php-code/phpdoc-types
		if ($typeNode instanceof Ast\Type\IdentifierTypeNode) {
			$typeNodeName = strtolower($typeNode->name);

			$result = match ($typeNodeName) {
				// Basic types
				'int' => is_int($value), // 'integer' can be also class name, so this type is checked later
				'string' => is_string($value),
				'non-empty-string' => is_string($value) && $value !== '',
				'non-empty-lowercase-string' => is_string($value) && $value !== '' && mb_strtolower($value) === $value,
				'non-empty-uppercase-string' => is_string($value) && $value !== '' && mb_strtoupper($value) === $value,
				'truthy-string', 'non-falsy-string' => is_string($value) && (bool) $value,
				'lowercase-string' => is_string($value) && mb_strtolower($value) === $value,
				'uppercase-string' => is_string($value) && mb_strtoupper($value) === $value,
				'numeric-string' => is_string($value) && is_numeric($value),
				'enum-string' => $value instanceof \UnitEnum,
				'__stringandstringable' => is_string($value) || $value instanceof \Stringable || (is_object($value) && method_exists($value, '__toString')),
				'array-key' => is_int($value) || is_string($value),
				'bool' => is_bool($value),
				//'boolean' => is_bool($value), // 'boolean' can be also class name, so this type is checked later
				'true' => $value === true,
				'false' => $value === false,
				'null' => $value === null,
				'float' => is_float($value),
				//'double' => is_double($value), // 'double' can be also class name, so this type is checked later
				//'number' => is_int($value) || is_float($value), // || is_double($value), -> alias, 'number' can be also class name, so this type is checked later
				//'numeric' => is_numeric($value), 'numeric' can be also class name, so this type is checked later
				//'scalar' => is_scalar($value), 'scalar' can be also class name, so this type is checked later
				'empty-scalar' => is_scalar($value) && (bool) $value === false,
				'non-empty-scalar' => is_scalar($value) && (bool) $value === true,
				'array', 'associative-array' => is_array($value),
				'non-empty-array' => is_array($value) && $value !== [],
				'list' => is_array($value) && array_is_list($value),
				'non-empty-list' => is_array($value) && array_is_list($value) && $value !== [],
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
				// Mixed
				'mixed' => true,
				'non-empty-mixed' => (bool) $value === true,
				// Integer ranges
				'positive-int' => is_int($value) && $value > 0,
				'negative-int' => is_int($value) && $value < 0,
				'non-positive-int' => is_int($value) && $value <= 0,
				'non-negative-int' => is_int($value) && $value >= 0,
				'non-zero-int' => is_int($value) && $value !== 0,
				// Classes and interfaces
				'class-string' => is_string($value) && class_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				'interface-string' => is_string($value) && interface_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				'trait-string' => is_string($value) && trait_exists(FullyQualifiedClassNameResolver::resolve($this->filename, $value)),
				default => $this->instanceOf($typeNode->name, $value),
			};

			if (!$result) {
				if (self::mightBeConstant($typeNode->name) && defined($typeNode->name)) {
					return $value === constant($typeNode->name);
				}

				if ($typeNodeName === 'integer') {
					return is_int($value);
				} else if ($typeNodeName === 'double') {
					return is_double($value);
				} else if ($typeNodeName === 'number') {
					return is_int($value) || is_float($value);
				} else if ($typeNodeName === 'numeric') {
					return is_numeric($value);
				} else if ($typeNodeName === 'boolean') {
					return is_bool($value);
				} else if ($typeNodeName === 'scalar') {
					return is_scalar($value);
				} else if ($typeNodeName === 'resource') {
					return is_resource($value) || str_starts_with(get_debug_type($value), 'resource ');
				} else if ($typeNodeName === 'empty') {
					return (bool) $value === false;
				}
			}

			return $result;
		} else if ($typeNode instanceof Ast\Type\NullableTypeNode) {
			if ($value === null) {
				return true;
			}

			return $this->checkTypeNode($typeNode->type, $value);
		} else if ($typeNode instanceof Ast\Type\ConstTypeNode) {
			$constExpr = $typeNode->constExpr;
			if ($constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
				return is_int($value) && (string) $value === $constExpr->value;
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprFloatNode) {
				return is_float($value) && (string) $value === $constExpr->value;
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprStringNode) {
				return is_string($value) && $value === $constExpr->value;
			}
		} else if ($typeNode instanceof Ast\Type\ArrayTypeNode) {
			if (!is_array($value)) {
				return false;
			}

			foreach ($value as $item) {
				if (!$this->checkTypeNode($typeNode->type, $item)) {
					return false;
				}
			}

			return true;
		} else if ($typeNode instanceof Ast\Type\ArrayShapeNode) {
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
				} else if ($keyName instanceof Ast\Type\IdentifierTypeNode) {
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
		} else if ($typeNode instanceof Ast\Type\ObjectShapeNode) {
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
		} else if ($typeNode instanceof Ast\Type\GenericTypeNode) {
			$name = strtolower($typeNode->type->name);

			if ($name === 'array' || $name === 'non-empty-array') {
				if (!is_array($value)) {
					return false;
				}

				if ($name === 'non-empty-array' && $value === []) {
					return false;
				}

				foreach ($value as $key => $item) {
					$checkItems = count($typeNode->genericTypes) === 1 ? [$item] : [$key, $item];
					foreach ($typeNode->genericTypes as $i => $genericType) {
						if (!$this->checkTypeNode($genericType, $checkItems[$i])) {
							return false;
						}
					}
				}
			} else if ($name === 'list' || $name === 'non-empty-list') {
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
				$limits = [];
				foreach ($typeNode->genericTypes as $genericType) {
					if ($genericType instanceof Ast\Type\ConstTypeNode && $genericType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
						$limit = (int) $genericType->constExpr->value;
					} else if ($genericType instanceof Ast\Type\IdentifierTypeNode && in_array(strtolower($genericType->name), ['min', 'max'], true)) {
						$limit = strcasecmp($genericType->name, 'min') === 0 ? PHP_INT_MIN : PHP_INT_MAX;
					} else {
						throw new Exceptions\ShouldNotHappenException();
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
			} else if ($name === 'class-string' || $name === 'interface-string') {
				if (!is_string($value)) {
					return false;
				}

				return $this->isClassStringOf($typeNode->genericTypes[0], $value);
			}

			return true;
		} else if ($typeNode instanceof Ast\Type\UnionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				if ($this->checkTypeNode($typeNodeItem, $value)) {
					return true;
				}
			}

			return false;
		} else if ($typeNode instanceof Ast\Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				if (!$this->checkTypeNode($typeNodeItem, $value)) {
					return false;
				}
			}

			return true;
		}

		throw new Exceptions\ShouldNotHappenException();
	}


	private function instanceOf(string $class, mixed $value): bool
	{
		$fullyQualifiedClassName = FullyQualifiedClassNameResolver::resolve($this->filename, $class);
		return $value instanceof $fullyQualifiedClassName;
	}


	private function isClassStringOf(Ast\Type\TypeNode $typeNode, string $value): bool
	{
		if ($typeNode instanceof Ast\Type\IdentifierTypeNode) {
			return is_a($value, FullyQualifiedClassNameResolver::resolve($this->filename, $typeNode->name), true);
		} else if ($typeNode instanceof Ast\Type\UnionTypeNode) {
			foreach ($typeNode->types as $type) {
				if ($this->isClassStringOf($type, $value)) {
					return true;
				}
			}

			return false;
		} else if ($typeNode instanceof Ast\Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $type) {
				if (!$this->isClassStringOf($type, $value)) {
					return false;
				}
			}

			return true;
		} else {
			throw new Exceptions\ShouldNotHappenException();
		}
	}


	private static function mightBeConstant(string $name): bool
	{
		return preg_match('((?:^|\\\\)[A-Z_][A-Z0-9_]*$)', $name) === 1;
	}

}
