<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use Forrest79\TypeValidator\Exceptions;

class RuntimeSourceFilename
{
	private static string|null $rootDir = null;


	public static function detect(): string
	{
		$filename = '';
		foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
			if (!str_starts_with($item['file'] ?? '', self::getRootDir())) {
				$filename = $item['file'] ?? '';
				break;
			}
		}

		return $filename;
	}


	private static function getRootDir(): string
	{
		if (self::$rootDir === null) {
			$dir = realpath(__DIR__ . '/..');
			if ($dir === false) {
				throw new Exceptions\ShouldNotHappenException();
			}

			self::$rootDir = $dir;
		}

		return self::$rootDir;
	}

}
