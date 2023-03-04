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

/**
 * Interface for setting/merging user-defined configuration values into the configuration object
 */
interface MutableConfigurationInterface
{
	/**
	 * @param mixed $value
	 *
	 * @throws UnknownOptionException if $key contains a nested path which doesn't point to an array value
	 */
	public function set(string $key, mixed $value): void;

	/**
	 * @param array<string, mixed> $config
	 */
	public function merge(array $config = []): void;
}
