<?php declare(strict_types=1);

if (!function_exists('is_type')) {

	function is_type(mixed $variable, string $type): bool
	{
		return Forrest79\TypeValidator::isType($variable, $type);
	}


	function as_type(mixed $variable, string $type): mixed
	{
		assert(Forrest79\TypeValidator::isType($variable, $type));
		return $variable;
	}

}
