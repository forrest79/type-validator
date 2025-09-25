<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Helpers;

use Forrest79\PHPStanNarrowTypes\Exceptions;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\Type as AstType;
use PHPStan\Type;

class PhpStan
{
	private string $filename;

	private string $typeDescription;


	public function __construct(string $filename, string $typeDescription)
	{
		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	public function narrowTypeDescription(): Type\Type
	{
		return $this->astTypeNodeToType(PhpDocParser::parseType($this->typeDescription));
	}


	private function astTypeNodeToType(AstType\TypeNode $typeNode): Type\Type
	{
		// https://phpstan.org/writing-php-code/phpdoc-types
		// https://github.com/phpstan/phpstan-src/blob/2.1.x/tests/PHPStan/Type/TypeCombinatorTest.php
		if ($typeNode instanceof AstType\IdentifierTypeNode) {
			return match (strtolower($typeNode->name)) { // to lower?
				// Basic types
				'int', 'integer' => new Type\IntegerType(),
				'string' => new Type\StringType(),
				'non-empty-string' => Type\TypeCombinator::intersect(new Type\StringType(), new Type\Accessory\AccessoryNonEmptyStringType()),
				'non-falsy-string', 'truthy-string' => Type\TypeCombinator::intersect(new Type\StringType(), new Type\Accessory\AccessoryNonFalsyStringType()),
				'lowercase-string' => Type\TypeCombinator::intersect(new Type\StringType(), new Type\Accessory\AccessoryLowercaseStringType()),
				'uppercase-string' => Type\TypeCombinator::intersect(new Type\StringType(), new Type\Accessory\AccessoryUppercaseStringType()),
				'numeric-string' => Type\TypeCombinator::intersect(new Type\StringType(), new Type\Accessory\AccessoryNumericStringType()),
				'array-key' => new Type\UnionType([new Type\IntegerType(), new Type\StringType()]),
				'bool', 'boolean' => new Type\BooleanType(),
				'true' => new Type\Constant\ConstantBooleanType(true),
				'false' => new Type\Constant\ConstantBooleanType(false),
				'null' => new Type\NullType(),
				'float', 'double' => new Type\FloatType(),
				'number' => new Type\UnionType([new Type\IntegerType(), new Type\FloatType()]),
				'scalar' => new Type\UnionType([new Type\IntegerType(), new Type\FloatType(), new Type\StringType(), new Type\BooleanType()]),
				'array' => new Type\ArrayType(new Type\MixedType(), new Type\MixedType()),
				'iterable' => new Type\IterableType(new Type\MixedType(), new Type\MixedType()),
				'callable' => new Type\CallableType(),
				'open-resource' => new Type\ResourceType(),
				'object' => new Type\ObjectType(\stdClass::class),
				// Mixed
				'mixed' => new Type\MixedType(),
				// Integer ranges
				'positive-int' => Type\IntegerRangeType::fromInterval(0, PHP_INT_MAX),
				'negative-int' => Type\IntegerRangeType::fromInterval(PHP_INT_MIN, 0),
				'non-positive-int' => Type\IntegerRangeType::fromInterval(PHP_INT_MIN, 1),
				'non-negative-int' => Type\IntegerRangeType::fromInterval(1, PHP_INT_MAX),
				'non-zero-int' => new Type\UnionType([Type\IntegerRangeType::fromInterval(PHP_INT_MIN, 1), Type\IntegerRangeType::fromInterval(1, PHP_INT_MAX)]),
				// Not supported
				'resource', 'closed-resource', 'pure-callable', 'void', 'never', 'never-return', 'never-returns', 'no-return', 'literal-string' => throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode),
				// Classes and interfaces
				'class-string' => new Type\ClassStringType(),
				default => new Type\ObjectType(FullyQualifiedClassNameResolver::resolve($this->filename, $typeNode->name)),
			};
		} else if ($typeNode instanceof AstType\NullableTypeNode) {
			return new Type\UnionType([$this->astTypeNodeToType($typeNode->type), new Type\NullType()]);
		} else if ($typeNode instanceof AstType\ConstTypeNode) {
			$constExpr = $typeNode->constExpr;
			if ($constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
				return new Type\Constant\ConstantIntegerType((int) $constExpr->value);
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprFloatNode) {
				return new Type\Constant\ConstantFloatType((float) $constExpr->value);
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprStringNode) {
				return new Type\Constant\ConstantStringType($constExpr->value);
			} else {
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof AstType\ArrayTypeNode) {
			return new Type\ArrayType(
				new Type\UnionType([new Type\IntegerType(), new Type\StringType()]),
				$this->astTypeNodeToType($typeNode->type),
			);
		} else if ($typeNode instanceof AstType\ArrayShapeNode) {
			$keys = [];
			$values = [];
			$optionalKeys = [];

			$missingKeyIndex = 0;
			foreach ($typeNode->items as $i => $arrayShapeItem) {
				$keyName = $arrayShapeItem->keyName;
				if ($keyName instanceof Ast\ConstExpr\ConstExprIntegerNode) {
					$key = new Type\Constant\ConstantIntegerType((int) $keyName->value);
				} else if ($keyName instanceof Ast\ConstExpr\ConstExprStringNode) {
					$key = new Type\Constant\ConstantStringType(trim($keyName->value, '\'"'));
				} else if ($keyName instanceof AstType\IdentifierTypeNode) {
					$key = new Type\Constant\ConstantStringType($keyName->name);
				} else {
					$key = new Type\Constant\ConstantIntegerType($missingKeyIndex++);
				}

				$keys[] = $key;
				$values[] = $this->astTypeNodeToType($arrayShapeItem->valueType);

				if ($arrayShapeItem->optional) {
					$optionalKeys[] = $i;
				}
			}

			return new Type\Constant\ConstantArrayType($keys, $values, optionalKeys: $optionalKeys);
		} else if ($typeNode instanceof AstType\ObjectShapeNode) {
			$properties = [];
			$optionalKeys = [];

			foreach ($typeNode->items as $objectShapeItem) {
				$keyName = $objectShapeItem->keyName;
				if ($keyName instanceof Ast\ConstExpr\ConstExprStringNode) {
					$key = trim($keyName->value, '\'"');
				} else { // Type\IdentifierTypeNode
					$key = $keyName->name;
				}

				$properties[$key] = $this->astTypeNodeToType($objectShapeItem->valueType);

				if ($objectShapeItem->optional) {
					$optionalKeys[] = $key;
				}
			}

			return new Type\ObjectShapeType($properties, $optionalKeys);
		} else if ($typeNode instanceof AstType\GenericTypeNode) {
			$name = strtolower($typeNode->type->name);

			if ($name === 'array' || $name === 'non-empty-array') {
				if (count($typeNode->genericTypes) > 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				if (count($typeNode->genericTypes) === 1) {
					$keyType = new Type\UnionType([new Type\IntegerType(), new Type\StringType()]);
					$valueType = $this->astTypeNodeToType($typeNode->genericTypes[0]);
				} else {
					$keyType = $this->astTypeNodeToType($typeNode->genericTypes[0]);
					$valueType = $this->astTypeNodeToType($typeNode->genericTypes[1]);
				}

				$arrayType = new Type\ArrayType($keyType, $valueType);

				if ($name === 'non-empty-array') {
					$arrayType = Type\TypeCombinator::intersect($arrayType, new Type\Accessory\NonEmptyArrayType());
				}

				return $arrayType;
			} else if ($name === 'list' || $name === 'non-empty-list') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				$listType = [
					new Type\ArrayType(new Type\IntegerType(), $this->astTypeNodeToType($typeNode->genericTypes[0])),
					new Type\Accessory\AccessoryArrayListType(),
				];
				if ($name === 'non-empty-list') {
					$listType[] = new Type\Accessory\NonEmptyArrayType();
				}

				return Type\TypeCombinator::intersect(...$listType);
			} else if ($name === 'int') {
				if (count($typeNode->genericTypes) !== 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				$limits = [];
				foreach ($typeNode->genericTypes as $genericType) {
					if ($genericType instanceof AstType\ConstTypeNode && $genericType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
						$limit = (int) $genericType->constExpr->value;
					} else if ($genericType instanceof AstType\IdentifierTypeNode && in_array(strtolower($genericType->name), ['min', 'max'], true)) {
						$limit = strcasecmp($genericType->name, 'min') === 0 ? PHP_INT_MIN : PHP_INT_MAX;
					} else {
						throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
					}

					$limits[] = $limit;
				}

				return Type\IntegerRangeType::fromInterval($limits[0], $limits[1]);
			} else if ($name === 'int-mask') {
				return new Type\UnionType($this->arrayAstTypeNodesToTypes($typeNode->genericTypes));
			} else if ($name === 'class-string') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				return new Type\Generic\GenericClassStringType($this->astTypeNodeToType($typeNode->genericTypes[0]));
			} else { // key-of<...> | value-of<...> | iterable<...> | Collection<...> | Collection|Type[]
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof AstType\UnionTypeNode) {
			return new Type\UnionType($this->arrayAstTypeNodesToTypes($typeNode->types));
		} else if ($typeNode instanceof AstType\IntersectionTypeNode) {
			return new Type\IntersectionType($this->arrayAstTypeNodesToTypes($typeNode->types));
		} else if ($typeNode instanceof AstType\InvalidTypeNode) {
			throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
		} else { // AstType\ThisTypeNode | AstType\ConditionalTypeNode | AstType\ConditionalTypeForParameterNode | AstType\OffsetAccessTypeNode | AstType\CallableTypeNode
			throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
		}
	}


	/**
	 * @param array<AstType\TypeNode> $astTypeNodes
	 * @return list<Type\Type>
	 */
	private function arrayAstTypeNodesToTypes(array $astTypeNodes): array
	{
		$types = [];
		foreach ($astTypeNodes as $astTypeNode) {
			$types[] = $this->astTypeNodeToType($astTypeNode);
		}

		return $types;
	}

}
