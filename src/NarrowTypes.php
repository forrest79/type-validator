<?php declare(strict_types=1);

namespace Forrest79;

use Forrest79\NarrowTypes\TypeParser;
use PHPStan\ShouldNotHappenException;

/**
 * @phpstan-type Type array{type: string, key?: string, value?: string, class?: string}
 */
final class NarrowTypes
{
	private const HAS_IS_FUNCTION_TYPES = [
		TypeParser::NULL,
		TypeParser::INT,
		TypeParser::FLOAT,
		TypeParser::STRING,
		TypeParser::BOOL,
		TypeParser::CALLABLE,
		TypeParser::OBJECT,
	];


	public static function isType(mixed $value, string $type): bool
	{
		$filename = '';
		foreach (debug_backtrace() as $item) {
			if (!str_starts_with($item['file'] ?? '', __DIR__)) {
				$filename = $item['file'] ?? '';
				break;
			}
		}

		return self::checkType($filename, $value, $type);
	}


	private static function checkType(string $filename, mixed $value, string $type): bool
	{
		foreach (TypeParser::parse($filename, $type) as $parsedType) {
			$checkType = $parsedType['type'];

			if ($checkType === TypeParser::MIXED) {
				return true;
			} else if (in_array($checkType, self::HAS_IS_FUNCTION_TYPES, true)) {
				if (call_user_func('is_' . $checkType, $value) === true) {
					return true;
				}
			} else if ($checkType === TypeParser::ARRAY) {
				if (is_array($value)) {
					if (isset($parsedType['key']) && isset($parsedType['value'])) {
						foreach ($value as $k => $v) {
							if (!self::checkType($filename, $k, $parsedType['key']) || !self::checkType($filename, $v, $parsedType['value'])) {
								continue 2;
							}
						}
					}

					return true;
				}
			} else if ($checkType === TypeParser::LIST) {
				if (is_array($value) && array_is_list($value)) {
					if (isset($parsedType['value'])) {
						foreach ($value as $v) {
							if (!self::checkType($filename, $v, $parsedType['value'])) {
								continue 2;
							}
						}
					}

					return true;
				}
			} else if ($checkType === TypeParser::OBJECT) {
				if (is_object($value)) {
					if (isset($parsedType['class'])) {
						if (!($value instanceof $parsedType['class'])) {
							continue;
						}
					}

					return true;
				}
			} else {
				throw new ShouldNotHappenException(sprintf('Invalid type to check \'%s\'.', $checkType));
			}
		}

		return false;
	}

}
