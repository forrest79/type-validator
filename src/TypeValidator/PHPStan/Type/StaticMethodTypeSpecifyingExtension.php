<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Type;

use Forrest79\TypeValidator;
use PHPStan\Analyser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type;
use PhpParser\Node\Expr\StaticCall;

class StaticMethodTypeSpecifyingExtension extends ReturnTypeExtension implements Type\StaticMethodTypeSpecifyingExtension
{

	public function getClass(): string
	{
		return TypeValidator::class;
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
