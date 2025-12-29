<?php declare(strict_types=1);

namespace Forrest79;

class TypeValidator
{

	public static function isType(mixed $value, string $type): bool
	{
		return TypeValidator\Helpers\Runtime::check($type, TypeValidator\Helpers\RuntimeSourceFilename::detectCallback(), $value);
	}


	public static function checkType(mixed $value, string $type): void
	{
		if (!TypeValidator\Helpers\Runtime::check($type, TypeValidator\Helpers\RuntimeSourceFilename::detectCallback(), $value)) {
			throw new TypeValidator\Exceptions\CheckException($type, $value);
		}
	}

}
