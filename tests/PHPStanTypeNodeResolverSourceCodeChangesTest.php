<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

use PHPStan\PhpDoc\TypeNodeResolver;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

class PHPStanTypeNodeResolverSourceCodeChangesTest
{
	private const string EXPECTED_HASH_MD5 = '5d3e49e20d3181495be522d9ac7075d7';

	private const string PHP_SOURCE_FOR_DIFF = __DIR__ . '/assets/phpstan-src/PHPStan_PhpDoc_TypeNodeResolver.phps';


	public static function test(): void
	{
		$rc = new \ReflectionClass(TypeNodeResolver::class);

		$filename = $rc->getFileName();
		assert(is_string($filename) && file_exists($filename));

		$source = file_get_contents($filename);
		assert(is_string($source));

		$actualHash = md5($source);

		file_put_contents(__DIR__ . '/assets/phpstan-src/PHPStan_PhpDoc_TypeNodeResolver.phps', $source);

		assert(
			$actualHash === self::EXPECTED_HASH_MD5,
			sprintf(
				'Class %s was changed, expected hash is \'%s\' != actual hash \'%s\' check differences in \'%s\' and update hash.',
				TypeNodeResolver::class,
				self::EXPECTED_HASH_MD5,
				$actualHash,
				self::PHP_SOURCE_FOR_DIFF,
			),
		);
	}

}

Assert::noError(static function (): void {
	PHPStanTypeNodeResolverSourceCodeChangesTest::test();
});
