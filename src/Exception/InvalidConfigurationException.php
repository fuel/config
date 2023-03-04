<?php declare(strict_types=1);

/**
 * The Fuel PHP Framework is a fast, simple and flexible development framework
 *
 * @package    fuel
 * @version    2.0.0
 * @author     FlexCoders Ltd, Fuel The PHP Framework Team
 * @license    MIT License
 * @copyright  2019-2021 Phil Bennett
 * @copyright  2023 FlexCoders Ltd, The Fuel PHP Framework Team
 * @link       https://fuelphp.org
 */

namespace Fuel\Config\Exception;

use UnexpectedValueException;

use function sprintf;
use function is_object;
use function print_r;
use function get_class;

/**
 * -----------------------------------------------------------------------------
 */

class InvalidConfigurationException extends UnexpectedValueException implements ConfigurationExceptionInterface
{
	/**
	 * @param string  $option      Name/path of the option
	 * @param mixed   $valueGiven  The invalid option that was provided
	 * @param ?string $description Additional text describing the issue (optional)
	 */
	public static function forConfigOption(string $option, mixed $valueGiven, ?string $description = null): self
	{
		$message = sprintf('Invalid config option for "%s": %s', $option, self::getDebugValue($valueGiven));

		if ($description !== null)
		{
			$message .= sprintf(' (%s)', $description);
		}

		return new self($message);
	}

	/**
	 * @param mixed $value
	 *
	 * @psalm-pure
	 */
	private static function getDebugValue($value): string
	{
		if (is_object($value))
		{
			return get_class($value);
		}

		return print_r($value, true);
	}
}
