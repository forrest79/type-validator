<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\PHPStan\Type;

use Forrest79\PHPStanNarrowTypes\Exceptions;
use Forrest79\PHPStanNarrowTypes\Helpers;
use PHPStan\Analyser;
use PHPStan\Analyser\Scope;
use PHPStan\Parser;
use PHPStan\PhpDoc;
use PHPStan\Type;
use PhpParser\Error;
use PhpParser\Node;

abstract class NarrowTypesReturnTypeExtension implements Analyser\TypeSpecifierAwareExtension
{
	/** @var array<string, array<string, Type\Type>> */
	private static array $cache = [];

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
	protected function narrowTypes(array $args, Scope $scope): Analyser\SpecifiedTypes
	{
		$filename = $scope->getFile();

		$typeDescriptionArg = $args[1]->value;
		$typeDescriptionType = $scope->getType($args[1]->value);
		$typeDescriptionConstantStrings = $typeDescriptionType->getConstantStrings();

		try {
			if (count($typeDescriptionConstantStrings) === 1) {
				$typeDescription = $typeDescriptionConstantStrings[0]->getValue();

				Helpers\SupportedTypes::check($filename, $typeDescription);

				if (!isset(self::$cache[$filename][$typeDescription])) {
					self::$cache[$filename][$typeDescription] = (new Helpers\PhpStan($this->typeNodeResolver, $filename, $typeDescription))->convertToType();
				}

				return $this->typeSpecifier->create(
					$args[0]->value,
					self::$cache[$filename][$typeDescription],
					Analyser\TypeSpecifierContext::createTruthy(),
					$scope,
				);
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


	private static function error(string $filename, string $message, Node\Expr $arg): never
	{
		throw new Parser\ParserErrorsException([new Error($message, ['startLine' => $arg->getLine()])], $filename);
	}


	protected static function isMethodSupported(string $methodName, int $argCount): bool
	{
		return in_array($methodName, static::getSupportedMethodsList(), true) && ($argCount === 2);
	}

}
