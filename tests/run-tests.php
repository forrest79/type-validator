<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Tests;

require __DIR__ . '/../vendor/autoload.php';

if (strtolower($argv[1] ?? '') === '--debug') {
	Helper::showDumps();
}

TypeDescriptionsTest::test();
PHPStanExtensionTest::test();
PHPStanTypeNodeResolverTest::test();

echo "\033[1;32m[OK]\n\033[0m";
