<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Exceptions;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

class UnsupportedTypeException extends Exception
{
	public readonly string $filename;

	public readonly string $typeDescription;

	public readonly TypeNode $typeNode;


	public function __construct(
		string $filename,
		string $typeDescription,
		TypeNode $typeNode,
		\Throwable|null $previous = null,
	)
	{
		parent::__construct(sprintf('Unsupported type \'%s\' for type description \'%s\' in file \'%s\'', $typeNode, $typeDescription, $filename), previous: $previous);

		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
		$this->typeNode = $typeNode;
	}

}
