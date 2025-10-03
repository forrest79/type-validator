<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use Forrest79\TypeValidator;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class FullyQualifiedClassNameResolverTest
{

	public static function test(): void
	{
		assert(TypeValidator\Helpers\FullyQualifiedClassNameResolver::resolve(__DIR__ . '/NonExistingFileClass.php', 'NonExistingFileClass') === 'NonExistingFileClass');
		assert(TypeValidator\Helpers\FullyQualifiedClassNameResolver::resolve(__FILE__, 'FullyQualifiedClassNameResolverTest') === 'Forrest79\TypeValidator\Tests\FullyQualifiedClassNameResolverTest');
		assert(TypeValidator\Helpers\FullyQualifiedClassNameResolver::resolve(__DIR__ . '/assets/incorrect.php', 'FullyQualifiedClassNameResolverTest') === 'FullyQualifiedClassNameResolverTest');
	}

}

Assert::noError(static function (): void {
	FullyQualifiedClassNameResolverTest::test();
});
