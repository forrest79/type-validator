<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Exceptions;

class CheckException extends \InvalidArgumentException
{
	public readonly string $typeDescription;

	public readonly mixed $value;


	public function __construct(string $typeDescription, mixed $value, \Throwable|null $previous = null)
	{
		parent::__construct(sprintf('Bad value for type \'%s\'.', $typeDescription), 0, $previous);

		$this->typeDescription = $typeDescription;
		$this->value = $value;
	}

}
