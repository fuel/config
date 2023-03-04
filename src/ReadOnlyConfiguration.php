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

use function func_num_args;

/**
 * Provides read-only access to a given Configuration object
 */
class ReadOnlyConfiguration implements ConfigurationInterface
{
	/*
	 * @var Configuration
	 */
	protected Configuration $config;

	/**
	 * -----------------------------------------------------------------------------
	 *
	 * -----------------------------------------------------------------------------
	 *
	 * @since 2.0.0
	 */
	public function __construct(Configuration $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		if (func_num_args() === 1)
		{
			return $this->config->get($key);
		}

		return $this->config->get($key, $default);
	}

	public function exists(string $key): bool
	{
		return $this->config->exists($key);
	}
}
