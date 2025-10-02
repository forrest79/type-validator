<?php declare(strict_types=1);

if (!function_exists('is_type')) {

	function is_type(mixed $variable, string $type): bool
	{
		return Forrest79\TypeValidator::isType($variable, $type);
	}

}
