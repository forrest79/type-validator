<?php declare(strict_types=1);

namespace Forrest79;

class TypeValidator
{
	/** @var array<string, array<string, TypeValidator\Helpers\Runtime>> */
	private static array $cache = [];


	public static function isType(mixed $value, string $type): bool
	{
		return self::getRuntime($type)->check($value);
	}


	public static function checkType(mixed $value, string $type): void
	{
		if (!self::getRuntime($type)->check($value)) {
			throw new \InvalidArgumentException('todo');
		}
	}


	private static function getRuntime(string $type): TypeValidator\Helpers\Runtime
	{
		$filename = '';
		foreach (debug_backtrace() as $item) {
			if (!str_starts_with($item['file'] ?? '', __DIR__)) {
				$filename = $item['file'] ?? '';
				break;
			}
		}

		if (!isset(self::$cache[$filename][$type])) {
			self::$cache[$filename][$type] = new TypeValidator\Helpers\Runtime($filename, $type);
		}

		return self::$cache[$filename][$type];
	}

}
