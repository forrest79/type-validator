<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\PHPStan\Type;

use Forrest79\PHPStanNarrowTypes\NarrowTypes;
use PHPStan\Analyser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type;
use PhpParser\Node\Expr\StaticCall;

class NarrowTypesStaticMethodReturnTypeExtension extends NarrowTypesReturnTypeExtension implements Type\StaticMethodTypeSpecifyingExtension
{

	public function getClass(): string
	{
		return NarrowTypes::class;
	}


	public function isStaticMethodSupported(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		Analyser\TypeSpecifierContext $context,
	): bool
	{
		return self::isMethodSupported($staticMethodReflection->getName(), count($node->getArgs()));
	}


	public function specifyTypes(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		Scope $scope,
		Analyser\TypeSpecifierContext $context,
	): Analyser\SpecifiedTypes
	{
		return $this->narrowTypes($node->getArgs(), $scope);
	}


	/**
	 * @return list<string>
	 */
	protected static function getSupportedMethodsList(): array
	{
		return ['isType', 'checkType'];
	}

}
