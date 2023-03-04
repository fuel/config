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

namespace Fuel\Config;

use Fuel\Config\Exception\UnknownOptionException;
use Fuel\Config\Exception\ValidationException;

/**
 * Interface for reading configuration values
 */
interface ConfigurationInterface
{
	/**
	 * @param string $key Configuration option path/key
	 *
	 * @psalm-param non-empty-string $key
	 *
	 * @return mixed
	 *
	 * @throws ValidationException if the schema failed to validate the given input
	 * @throws UnknownOptionException if the requested key does not exist or is malformed
	 */
	public function get(string $key, mixed $default = null): mixed;

	/**
	 * @param string $key Configuration option path/key
	 *
	 * @psalm-param non-empty-string $key
	 *
	 * @return bool Whether the given option exists
	 *
	 * @throws ValidationException if the schema failed to validate the given input
	 */
	public function exists(string $key): bool;
}
