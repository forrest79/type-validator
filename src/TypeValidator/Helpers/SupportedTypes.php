<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use Forrest79\TypeValidator\Exceptions;
use PHPStan\PhpDocParser\Ast;
use PHPStan\PhpDocParser\Ast\Type;

class SupportedTypes
{
	private string $filename;

	private string $typeDescription;


	public function __construct(string $filename, string $typeDescription)
	{
		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	/**
	 * @throws Exceptions\Exception
	 */
	public function checkTypeDescription(): void
	{
		$this->checkTypeNode(PhpDocParser::parseType($this->typeDescription));
	}


	/**
	 * @throws Exceptions\Exception
	 */
	private function checkTypeNode(Type\TypeNode $typeNode): void
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
					throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\NullableTypeNode) {
			$this->checkTypeNode($typeNode->type);
		} else if ($typeNode instanceof Type\ConstTypeNode) {
			$constExpr = $typeNode->constExpr;
			if ($constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
				// OK
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprFloatNode) {
				// OK
			} else if ($constExpr instanceof Ast\ConstExpr\ConstExprStringNode) {
				// OK
			} else {
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\ArrayTypeNode) {
			$this->checkTypeNode($typeNode->type);
		} else if ($typeNode instanceof Type\ArrayShapeNode) {
			foreach ($typeNode->items as $arrayShapeItem) {
				$this->checkTypeNode($arrayShapeItem->valueType);
			}
		} else if ($typeNode instanceof Type\ObjectShapeNode) {
			foreach ($typeNode->items as $objectShapeItem) {
				$this->checkTypeNode($objectShapeItem->valueType);
			}
		} else if ($typeNode instanceof Type\GenericTypeNode) {
			$name = strtolower($typeNode->type->name);

			if ($name === 'array' || $name === 'non-empty-array') {
				if (count($typeNode->genericTypes) > 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				foreach ($typeNode->genericTypes as $genericType) {
					$this->checkTypeNode($genericType);
				}
			} else if ($name === 'list' || $name === 'non-empty-list') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				$this->checkTypeNode($typeNode->genericTypes[0]);
			} else if ($name === 'int') {
				if (count($typeNode->genericTypes) !== 2) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				foreach ($typeNode->genericTypes as $genericType) {
					if ($genericType instanceof Type\ConstTypeNode && $genericType->constExpr instanceof Ast\ConstExpr\ConstExprIntegerNode) {
						// OK
					} else if ($genericType instanceof Type\IdentifierTypeNode && in_array(strtolower($genericType->name), ['min', 'max'], true)) {
						// OK
					} else {
						throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
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
						throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
					}
				}
			} else if ($name === 'class-string' || $name === 'interface-string') {
				if (count($typeNode->genericTypes) !== 1) {
					throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
				}

				$this->isClassStringOf($typeNode->genericTypes[0]);
			} else { // key-of<...> | value-of<...> | int-mask-of<...> | iterable<...> | enum-string<...> | template-type<...> | Collection<...> | new<...> | static<...> | Collection|Type[] | __benevolent<...>
				throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
			}
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				$this->checkTypeNode($typeNodeItem);
			}
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			foreach ($typeNode->types as $typeNodeItem) {
				$this->checkTypeNode($typeNodeItem);
			}
		} else if ($typeNode instanceof Type\InvalidTypeNode) {
			throw new Exceptions\BadDescriptionException($this->filename, $this->typeDescription);
		} else { // Type\ThisTypeNode | Type\ConditionalTypeNode | Type\ConditionalTypeForParameterNode | Type\OffsetAccessTypeNode | Type\CallableTypeNode
			throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
		}
	}


	/**
	 * @throws Exceptions\Exception
	 */
	private function isClassStringOf(Type\TypeNode $typeNode): void
	{
		if ($typeNode instanceof Type\IdentifierTypeNode) {
			// OK
		} else if ($typeNode instanceof Type\UnionTypeNode) {
			// OK
		} else if ($typeNode instanceof Type\IntersectionTypeNode) {
			// OK
		} else {
			throw new Exceptions\UnsupportedTypeException($this->filename, $this->typeDescription, $typeNode);
		}
	}


	/**
	 * @throws Exceptions\Exception
	 */
	public static function check(string $filename, string $typeDescription): void
	{
		(new self($filename, $typeDescription))->checkTypeDescription();
	}

}
