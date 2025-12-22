<?php declare(strict_types=1);

namespace Forrest79;

class TypeValidator
{
	private static \Closure|null $filenameCallback = null;


	public static function isType(mixed $value, string $type): bool
	{
		return TypeValidator\Helpers\Runtime::check($type, self::getFilenameCallback(), $value);
	}


	public static function checkType(mixed $value, string $type): void
	{
		if (!TypeValidator\Helpers\Runtime::check($type, self::getFilenameCallback(), $value)) {
			throw new TypeValidator\Exceptions\CheckException($type, $value);
		}
	}


	/**
	 * @return callable(): string
	 */
	private static function getFilenameCallback(): callable
	{
		if (self::$filenameCallback === null) {
			self::$filenameCallback = static fn (): string => TypeValidator\Helpers\RuntimeSourceFilename::detect();
		}

		return self::$filenameCallback;
	}

}
