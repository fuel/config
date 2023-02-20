<?php declare(strict_types=1);

/**
 * The Fuel PHP Framework is a fast, simple and flexible development framework
 *
 * @package    fuel
 * @version    2.0.0
 * @author     FlexCoders Ltd, Fuel The PHP Framework Team
 * @license    MIT License
 * @copyright  2023 FlexCoders Ltd, The Fuel PHP Framework Team
 * @link       https://fuelphp.org
 */

namespace Fuel\Config;

/**
 * Accepts a Configuration instance
 */
trait ConfigurationAwareTrait
{
	/**
	 * @var Config
	 */
	protected Configuration $config;

	/**
	 * {@inheritdoc}
	 */
	public function getConfig(): Configuration
	{
		return $this->config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConfig(Configuration $config): void
	{
		$this->config = $config;
	}
}
