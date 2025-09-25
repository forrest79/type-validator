<?php declare(strict_types=1);

namespace Forrest79\PHPStanNarrowTypes\Exceptions;

class BadDescriptionException extends Exception
{
	public readonly string $filename;

	public readonly string $typeDescription;


	public function __construct(string $filename, string $typeDescription, \Throwable|null $previous = null)
	{
		parent::__construct(sprintf('Bad type description \'%s\' in file \'%s\'', $typeDescription, $filename), previous: $previous);

		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}

}
