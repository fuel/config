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

use StdClass;

use Fuel\Config\Exception\UnknownOptionException;
use Fuel\Config\Exception\ValidationException;
use Fuel\FileSystem\Finder;

use Dflydev\DotAccessData\Data;
use Dflydev\DotAccessData\DataInterface;
use Dflydev\DotAccessData\Exception\DataException;
use Dflydev\DotAccessData\Exception\InvalidPathException;
use Dflydev\DotAccessData\Exception\MissingPathException;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\ValidationException as NetteValidationException;

use array_key_exists;
use func_num_args;
use is_array;
use is_file;
use is_readable;
use str_replace;
use strlen;
use strpos;
use substr;
use trigger_error;

use E_USER_DEPRECATED;

class Configuration implements ConfigurationBuilderInterface, ConfigurationInterface
{
	/**
	 * Fuel Finder Instance
	 *
	 * @since 2.0.0
	 */
	protected Finder $finder;

	/** @psalm-readonly */
	protected Data $userConfig;

	/**
	 * @var array<string, Schema>
	 *
	 * @psalm-allow-private-mutation
	 */
	protected array $configSchemas = [];

	/** @psalm-allow-private-mutation */
	protected Data $finalConfig;

	/**
	 * @var array<string, mixed>
	 *
	 * @psalm-allow-private-mutation
	 */
	protected array $cache = [];

	/** @psalm-readonly */
	protected ConfigurationInterface $reader;

	/**
	 * @param array<string, Schema> $baseSchemas
	 */
	public function __construct(Finder $finder, array $baseSchemas = [])
	{
		$this->finder        = $finder;

		$this->configSchemas = $baseSchemas;
		$this->userConfig    = new Data();
		$this->finalConfig   = new Data();

		$this->reader = new ReadOnlyConfiguration($this);
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Load configuration values from a file
	 * -----------------------------------------------------------------------------
	 *
	 * @since 2.0.0
	 */
	public function load(string $key, string $file): void
	{
		// check if we've passed a filename and we can access the file
		if (is_file($file) and is_readable($file))
		{
			// load the config file
			$config = import($file);

			// validate it
			if ( ! is_array($config))
			{
				// @TODO
				throw new InvalidConfigurationException(sprintf('Config file %s does not return an array !', $file));
			}

			// merge it
			$this->merge([$key => $config]);
		}
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Load a configation
	 * -----------------------------------------------------------------------------
	 *
	 * And optionally validate it if there is a schema definition for it
	 *
	 * @since 2.0.0
	 */
	public function loadConfig(string $name): array
	{
		// check if we have a schema for this config
		$files = $this->finder->findAllFiles('schemas'.DS.$name);

		// and load the last one found
		if ($files)
		{
			$this->loadSchema($name, end($files));
		}

		// now find all config files themseelfs
		$files = $this->finder->findAllFiles('config'.DS.$name);

		foreach ($files ?: [] as $file)
		{
			$this->load($name, $file);
		}

		return $this->get($name);
	}

	/**
	 * -----------------------------------------------------------------------------
	 * Add a new schema from a Schema file
	 * -----------------------------------------------------------------------------
	 *
	 * Registers a new configuration schema at the given top-level key
	 *
	 * @since 2.0.0
	 */
	public function loadSchema(string $key, string $file): void
	{
		// check if we've passed a filename and we can access the file
		if (is_file($file) and is_readable($file))
		{
			$schema = import($file);

			// make sure we have a schema now
			if ( ! $schema instanceOf Schema)
			{
				// @TODO
				throw new InvalidConfigurationException(sprintf('Scheme file %s does contain a schema definition !', $file));
			}
		}
		else
		{
			// no schema find, create one that only validates if the config
			// file returns an array
			$schema = Expect::structure([]);
		}

		// load the schema
		$this->addSchema($key, $schema);
	}

	/**
	 * Registers a new configuration schema at the given top-level key
	 *
	 * @psalm-allow-private-mutation
	 */
	public function addSchema(string $key, Schema $schema): void
	{
		$this->invalidate();

		$this->configSchemas[$key] = $schema;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @psalm-allow-private-mutation
	 */
	public function merge(array $config = []): void
	{
		$this->invalidate();

		$this->userConfig->import($config, DataInterface::REPLACE);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @psalm-allow-private-mutation
	 */
	public function set(string $key, $value): void
	{
		$this->invalidate();

		try
		{
			$this->userConfig->set($key, $value);
		}
		catch (DataException $ex)
		{
			throw new UnknownOptionException($ex->getMessage(), $key, (int) $ex->getCode(), $ex);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @psalm-external-mutation-free
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		if (array_key_exists($key, $this->cache))
		{
			return $this->cache[$key];
		}

		try
		{
			$this->build(self::getTopLevelKey($key));

			return $this->cache[$key] = $this->finalConfig->get($key);
		}
		catch (InvalidPathException | MissingPathException $ex)
		{
			if (func_num_args() == 1)
			{
				throw new UnknownOptionException($ex->getMessage(), $key, (int) $ex->getCode(), $ex);
			}
		}
		return $default;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @psalm-external-mutation-free
	 */
	public function exists(string $key): bool
	{
		if (array_key_exists($key, $this->cache))
		{
			return true;
		}

		try
		{
			$this->build(self::getTopLevelKey($key));

			return $this->finalConfig->has($key);
		}
		catch (InvalidPathException | UnknownOptionException $ex)
		{
			return false;
		}
	}

	/**
	 * @psalm-mutation-free
	 */
	public function reader(): ConfigurationInterface
	{
		return $this->reader;
	}

	/**
	 * @psalm-external-mutation-free
	 */
	private function invalidate(): void
	{
		$this->cache       = [];
		$this->finalConfig = new Data();
	}

	/**
	 * Applies the schema against the configuration to return the final configuration
	 *
	 * @throws ValidationException|UnknownOptionException|InvalidPathException
	 *
	 * @psalm-allow-private-mutation
	 */
	private function build(string $topLevelKey): void
	{
		if ($this->finalConfig->has($topLevelKey))
		{
			return;
		}

		if (! isset($this->configSchemas[$topLevelKey]))
		{
			throw new UnknownOptionException(\sprintf('Missing config schema for "%s"', $topLevelKey), $topLevelKey);
		}

		try
		{
			$userData = [$topLevelKey => $this->userConfig->get($topLevelKey)];
		}
		catch (DataException $ex)
		{
			$userData = [];
		}

		try
		{
			$schema    = $this->configSchemas[$topLevelKey];
			$processor = new Processor();

			$processed = $processor->process(Expect::structure([$topLevelKey => $schema]), $userData);

			$this->raiseAnyDeprecationNotices($processor->getWarnings());

			$this->finalConfig->import((array) self::convertStdClassesToArrays($processed));
		}
		catch (NetteValidationException $ex)
		{
			throw new ValidationException($ex);
		}
	}

	/**
	 * Recursively converts stdClass instances to arrays
	 *
	 * @phpstan-template T
	 *
	 * @param T $data
	 *
	 * @return mixed
	 *
	 * @phpstan-return ($data is \stdClass ? array<string, mixed> : T)
	 *
	 * @psalm-pure
	 */
	private static function convertStdClassesToArrays($data)
	{
		if ($data instanceof stdClass)
		{
			$data = (array) $data;
		}

		if (is_array($data))
		{
			foreach ($data as $k => $v)
			{
				$data[$k] = self::convertStdClassesToArrays($v);
			}
		}

		return $data;
	}

	/**
	 * @param string[] $warnings
	 */
	private function raiseAnyDeprecationNotices(array $warnings): void
	{
		foreach ($warnings as $warning)
		{
			@trigger_error($warning, E_USER_DEPRECATED);
		}
	}

	/**
	 * @throws InvalidPathException
	 */
	private static function getTopLevelKey(string $path): string
	{
		if (strlen($path) === 0)
		{
			throw new InvalidPathException('Path cannot be an empty string');
		}

		$path = str_replace(['.', '/'], '.', $path);

		$firstDelimiter = strpos($path, '.');
		if ($firstDelimiter === false)
		{
			return $path;
		}

		return substr($path, 0, $firstDelimiter);
	}
}
