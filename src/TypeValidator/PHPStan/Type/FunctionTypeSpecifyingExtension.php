<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Type;

use PHPStan\Analyser;
use PHPStan\Reflection;
use PHPStan\Type;
use PhpParser\Node;

class FunctionTypeSpecifyingExtension extends ReturnTypeExtension implements Type\FunctionTypeSpecifyingExtension
{

	public function isFunctionSupported(
		Reflection\FunctionReflection $functionReflection,
		Node\Expr\FuncCall $node,
		Analyser\TypeSpecifierContext $context,
	): bool
	{
		return self::isMethodSupported($functionReflection->getName(), count($node->getArgs()));
	}


	public function specifyTypes(
		Reflection\FunctionReflection $functionReflection,
		Node\Expr\FuncCall $node,
		Analyser\Scope $scope,
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
		return ['is_type'];
	}

}
