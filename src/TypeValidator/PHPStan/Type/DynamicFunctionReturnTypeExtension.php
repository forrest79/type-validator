<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type;
use PhpParser\Node\Expr\FuncCall;

final class DynamicFunctionReturnTypeExtension extends ReturnTypeExtension implements Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return in_array($functionReflection->getName(), self::getSupportedMethodsList(), true);
	}


	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope,
	): Type\Type|null
	{
		if (!self::isMethodSupported($functionReflection->getName(), count($functionCall->getArgs()))) {
			return null;
		}

		return $this->getType($functionCall->getArgs()[1]->value, $scope);
	}


	/**
	 * @inheritDoc
	 */
	protected static function getSupportedMethodsList(): array
	{
		return ['assert_type'];
	}

}
