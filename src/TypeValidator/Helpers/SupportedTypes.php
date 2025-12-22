<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use Forrest79\TypeValidator\Exceptions;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\Type;

class SupportedTypes
{
	/** @var array<string, true> */
	private static array $cache = [];


	/**
	 * @param callable(): string $filenameCallback
	 * @throws Exceptions\Exception
	 */
	public static function check(string $typeDescription, callable $filenameCallback): void
	{
		if (!isset(self::$cache[$typeDescription])) {
			self::checkTypeNode(PhpDocParser::parseType($typeDescription), $typeDescription, $filenameCallback);
			self::$cache[$typeDescription] = true;
		}
	}


	/**
	 * @param callable(): string $filenameCallback
	 * @throws Exceptions\Exception
	 */
	private static function checkTypeNode(
		Type\TypeNode $typeNode,
		string $typeDescription,
		callable $filenameCallback,
	): void
	{
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			switch (strtolower($typeNode->name)) { // to lower?
				case 'pure-callable':
				case 'pure-closure':
				case 'void':
				case 'never':
				case 'never-return':
				case 'never-returns':
				case 'no-return':
				case 'literal-string':
				case 'non-empty-literal-string':
				case 'self':
				case 'static':
				case 'parent':
					throw new Exceptions\UnsupportedTypeException($filenameCallback(), $typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\NullableTypeNode) {
			self::checkTypeNode($typeNode->type, $typeDescription, $filenameCallback);
		} else if ($typeNode instanceof Type\ConstTypeNode) {
			$constExpr = $typeNode->constExpr;
			if ($constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
				// OK
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprFloatNode) {
				// OK
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprStringNode) {
				// OK
			} else {
				throw new Exceptions\UnsupportedTypeException($filenameCallback(), $typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\ArrayTypeNode) {
			self::checkTypeNode($typeNode->type, $typeDescription, $filenameCallback);
		} else if ($typeNode instanceof Type\ArrayShapeNode) {
			foreach ($typeNode->items as $arrayShapeItem) {
				self::checkTypeNode($arrayShapeItem->valueType, $typeDescription, $filenameCallback);
			}
		} else if ($typeNode instanceof Type\ObjectShapeNode) {
			foreach ($typeNode->items as $objectShapeItem) {
				self::checkTypeNode($objectShapeItem->valueType, $typeDescription, $filenameCallback);
			}
		} else if ($typeNode instanceof Type\GenericTypeNode) {
			$name = strtolower($typeNode->type->name);

			if ($name === 'array' || $name === 'non-empty-array') {
				if (count($typeNode->genericTypes) > 2) {
					throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
				}

				foreach ($typeNode->genericTypes as $genericType) {
					self::checkTypeNode($genericType, $typeDescription, $filenameCallback);
				}
			} else if ($name === 'list' || $name === 'non-empty-list') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
				}

				self::checkTypeNode($typeNode->genericTypes[0], $typeDescription, $filenameCallback);
			} else if ($name === 'int') {
				if (count($typeNode->genericTypes) !== 2) {
					throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
				}

				foreach ($typeNode->genericTypes as $genericType) {
					if ($genericType instanceof Type\ConstTypeNode && $genericType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
						// OK
					} else if ($genericType instanceof Type\IdentifierTypeNode && in_array(strtolower($genericType->name), ['min', 'max'], true)) {
						// OK
					} else {
						throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
					}
				}
			} else if ($name === 'int-mask') {
				if (count($typeNode->genericTypes) === 1 && $typeNode->genericTypes[0] instanceof Ast\Type\UnionTypeNode) {
					$maskTypes = $typeNode->genericTypes[0]->types;
				} else {
					$maskTypes = $typeNode->genericTypes;
				}

				foreach ($maskTypes as $maskType) {
					if (($maskType instanceof Ast\Type\ConstTypeNode && !$maskType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) || !$maskType instanceof Ast\Type\ConstTypeNode) {
						throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
					}
				}
			} else if ($name === 'class-string' || $name === 'interface-string') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
				}

				self::isClassStringOf($typeNode->genericTypes[0], $typeDescription, $filenameCallback);
			} else { // key-of<...> | value-of<...> | int-mask-of<...> | iterable<...> | enum-string<...> | template-type<...> | Collection<...> | new<...> | static<...> | Collection|Type[] | __benevolent<...>
				throw new Exceptions\UnsupportedTypeException($filenameCallback(), $typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				self::checkTypeNode($typeNodeItem, $typeDescription, $filenameCallback);
			}
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				self::checkTypeNode($typeNodeItem, $typeDescription, $filenameCallback);
			}
		} else if ($typeNode instanceof Type\InvalidTypeNode) {
			throw new Exceptions\BadDescriptionException($filenameCallback(), $typeDescription);
		} else { // Type\ThisTypeNode | Type\ConditionalTypeNode | Type\ConditionalTypeForParameterNode | Type\OffsetAccessTypeNode | Type\CallableTypeNode
			throw new Exceptions\UnsupportedTypeException($filenameCallback(), $typeDescription, $typeNode);
		}
	}


	/**
	 * @param callable(): string $filenameCallback
	 * @throws Exceptions\Exception
	 */
	private static function isClassStringOf(
		Type\TypeNode $typeNode,
		string $typeDescription,
		callable $filenameCallback,
	): void
	{
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			// OK
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			// OK
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			// OK
		} else {
			throw new Exceptions\UnsupportedTypeException($filenameCallback(), $typeDescription, $typeNode);
		}
	}

}
