<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Tests;

class Helper
{
	private static bool $visibleDump = false;


	public static function dump(mixed $x): void
	{
		if (self::$visibleDump) {
			var_dump($x);
		}
	}


	public static function showDumps(): void
	{
		self::$visibleDump = true;
	}

}
