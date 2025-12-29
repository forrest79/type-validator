<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use Forrest79\TypeValidator\Exceptions;

class RuntimeSourceFilename
{
	/** @var (\Closure(): string)|null */
	private static \Closure|null $detectCallback = null;

	private static string|null $rootDir = null;


	/**
	 * @return callable(): string
	 */
	public static function detectCallback(): callable
	{
		if (self::$detectCallback === null) {
			self::$detectCallback = static function (): string {
				$filename = '';
				foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
					if (!str_starts_with($item['file'] ?? '', self::getRootDir())) {
						$filename = $item['file'] ?? '';
						break;
					}
				}

				return $filename;
			};
		}

		return self::$detectCallback;
	}


	private static function getRootDir(): string
	{
		if (self::$rootDir === null) {
			$dir = realpath(__DIR__ . '/../..');
			if ($dir === false) {
				throw new Exceptions\ShouldNotHappenException();
			}

			self::$rootDir = $dir;
		}

		return self::$rootDir;
	}

}
