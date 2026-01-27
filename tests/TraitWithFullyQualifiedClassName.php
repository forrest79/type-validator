<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Forrest79\TypeValidator\PHPStan\Helpers;

trait TraitWithFullyQualifiedClassName
{

	public static function testTraitIsListFqnObject(mixed $objectList): void
	{
		assert(TypeValidator::isType($objectList, 'list<Helpers\PhpParserNamespaceResolver>'));
		self::checkTraitIsListFqnObjectType($objectList);
	}


	/**
	 * @param list<Helpers\PhpParserNamespaceResolver> $objectList
	 */
	private static function checkTraitIsListFqnObjectType(array $objectList): void
	{
		var_dump($objectList);
	}

}
