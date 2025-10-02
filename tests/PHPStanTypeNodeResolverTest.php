<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use PHPStan\PhpDoc\TypeNodeResolver;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class PHPStanTypeNodeResolverTest
{
	private const string EXPECTED_HASH_MD5 = '1c75bf936a33252756dd683fb1f6bd8a';


	public static function test(): void
	{
		$rc = new \ReflectionClass(TypeNodeResolver::class);

		$filename = $rc->getFileName();
		assert(is_string($filename) && file_exists($filename));

		$source = file_get_contents($filename);
		assert(is_string($source));

		$actualHash = md5($source);

		assert(
			$actualHash === self::EXPECTED_HASH_MD5,
			sprintf('Class %s was changed, expected hash is \'%s\' != actual hash \'%s\' check differences and update hash.', TypeNodeResolver::class, self::EXPECTED_HASH_MD5, $actualHash),
		);
	}

}

Assert::noError(function (): void {
	PHPStanTypeNodeResolverTest::test();
});
