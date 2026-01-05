<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Type;

use Forrest79\TypeValidator\Exceptions;
use Forrest79\TypeValidator\Helpers;
use Forrest79\TypeValidator\PHPStan;
use PHPStan\Analyser;
use PHPStan\Parser;
use PHPStan\PhpDoc;
use PHPStan\Type;
use PhpParser\Error;
use PhpParser\Node;

abstract class ReturnTypeExtension implements Analyser\TypeSpecifierAwareExtension
{
	private PhpDoc\TypeNodeResolver $typeNodeResolver;

	private Analyser\TypeSpecifier $typeSpecifier;


	public function __construct(PhpDoc\TypeNodeResolver $typeNodeResolver)
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}


	/**
	 * @return list<string>
	 */
	abstract protected static function getSupportedMethodsList(): array;


	public function setTypeSpecifier(Analyser\TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}


	/**
	 * @param array<Node\Arg> $args [0] = checked variable, [1] = type description
	 */
	protected function narrowTypes(array $args, Analyser\Scope $scope): Analyser\SpecifiedTypes
	{
		return $this->typeSpecifier->create(
			$args[0]->value,
			$this->prepareType($args[1]->value, $scope),
			Analyser\TypeSpecifierContext::createTruthy(),
			$scope,
		);
	}


	protected function prepareType(
		Node\Expr $typeDescriptionArg,
		Analyser\Scope $scope,
		string|null &$typeDescription = null,
	): Type\Type
	{
		$filename = $scope->getFile();

		$typeDescriptionType = $scope->getType($typeDescriptionArg);
		$typeDescriptionConstantStrings = $typeDescriptionType->getConstantStrings();

		try {
			if (count($typeDescriptionConstantStrings) === 1) {
				$typeDescription = $typeDescriptionConstantStrings[0]->getValue();

				Helpers\SupportedTypes::check($typeDescription, static fn (): string => $filename);

				return $this->typeNodeResolver->resolve(Helpers\PhpDocParser::parseType($typeDescription), PHPStan\Helpers\NameScopeFactory::create($filename));
			} else {
				self::error($filename, sprintf('Bad type description \'%s\' (only constant string type descriptions are allowed)', $typeDescriptionType->describe(Type\VerbosityLevel::precise())), $typeDescriptionArg);
			}
		} catch (Exceptions\BadDescriptionException | Exceptions\UnsupportedTypeException $e) {
			self::error(
				$filename,
				$e instanceof Exceptions\BadDescriptionException
					? sprintf('Bad type description \'%s\'', $e->typeDescription)
					: sprintf('Unsupported type \'%s\' for type description \'%s\'', $e->typeNode, $e->typeDescription),
				$typeDescriptionArg,
			);
		}
	}


	final protected static function error(string $filename, string $message, Node\Expr $arg): never
	{
		throw new Parser\ParserErrorsException([new Error($message, ['startLine' => $arg->getLine()])], $filename);
	}


	protected static function isMethodSupported(string $methodName, int $argCount): bool
	{
		return in_array($methodName, static::getSupportedMethodsList(), true) && ($argCount === 2);
	}

}
