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

use Nette\Schema\ValidationException as NetteException;

/**
 * -----------------------------------------------------------------------------
 */

class ValidationException extends InvalidConfigurationException
{
     /**
     * @var  string
     */
    protected array $messages;

    /**
     * -----------------------------------------------------------------------------
     *
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public function __construct(NetteException $innerException)
    {
        parent::__construct($innerException->getMessage(), (int) $innerException->getCode(), $innerException);

        $this->messages = $innerException->getMessages();
    }

    /**
     * @return string[]
     */
    /**
     * -----------------------------------------------------------------------------
     *
     * -----------------------------------------------------------------------------
     *
     * @since 2.0.0
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
