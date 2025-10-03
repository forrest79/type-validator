<?php declare(strict_types=1);

namespace Forrest79\TypeValidator\PHPStan\Helpers;

use Forrest79\TypeValidator\Helpers\PhpDocParser;
use PHPStan\PhpDoc;
use PHPStan\Type;

class TypeConverter
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
