<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\Helpers;

use PHPStan\PhpDoc;
use PHPStan\Type;

class PhpStan
{
	private PhpDoc\TypeNodeResolver $typeNodeResolver;

	private string $filename;

	private string $typeDescription;


	public function __construct(PhpDoc\TypeNodeResolver $typeNodeResolver, string $filename, string $typeDescription)
	{
		$this->typeNodeResolver = $typeNodeResolver;
		$this->filename = $filename;
		$this->typeDescription = $typeDescription;
	}


	public function convertToType(): Type\Type
	{
		return $this->typeNodeResolver->resolve(PhpDocParser::parseType($this->typeDescription), NameScopeFactory::create($this->filename));
	}

}
