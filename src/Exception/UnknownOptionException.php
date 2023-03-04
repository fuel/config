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

use Throwable;
use InvalidArgumentException;

/**
 * -----------------------------------------------------------------------------
 */

class UnknownOptionException extends InvalidArgumentException implements ConfigurationExceptionInterface
{
	 /**
	 * @var  string
	 */
	protected string $path;

	/**
	 * -----------------------------------------------------------------------------
	 *
	 * -----------------------------------------------------------------------------
	 *
	 * @since 2.0.0
	 */
	public function __construct(string $message, string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->path = $path;
	}

	/**
	 * -----------------------------------------------------------------------------
	 *
	 * -----------------------------------------------------------------------------
	 *
	 * @since 2.0.0
	 */
	public function getPath(): string
	{
		return $this->path;
	}
}
