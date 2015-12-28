<?php

namespace Art4\JsonApiClient\Resource;

use Art4\JsonApiClient\AccessInterface;
use Art4\JsonApiClient\Resource\ItemLinkInterface;
use Art4\JsonApiClient\Utils\AccessTrait;
use Art4\JsonApiClient\Utils\DataContainer;
use Art4\JsonApiClient\Utils\FactoryManagerInterface;
use Art4\JsonApiClient\Exception\AccessException;
use Art4\JsonApiClient\Exception\ValidationException;

/**
 * ItemLink Object
 *
 * @see http://jsonapi.org/format/#document-links
 */
final class ItemLink implements ItemLinkInterface
{
	use AccessTrait;

	/**
	 * @var AccessInterface
	 */
	protected $parent;

	/**
	 * @var DataContainerInterface
	 */
	protected $container;

	/**
	 * @var FactoryManagerInterface
	 */
	protected $manager;

	/**
	 * @param object $object The error object
	 *
	 * @return self
	 *
	 * @throws ValidationException
	 */
	public function __construct($object, FactoryManagerInterface $manager, AccessInterface $parent)
	{
		if ( ! is_object($object) )
		{
			throw new ValidationException('ItemLink has to be an object, "' . gettype($object) . '" given.');
		}

		$this->parent = $parent;

		$this->manager = $manager;

		$this->container = new DataContainer();

		foreach (get_object_vars($object) as $name => $value)
		{
			$this->set($name, $value);
		}

		return $this;
	}

	/**
	 * Get a value by the key of this object
	 *
	 * @param string $key The key of the value
	 * @return mixed The value
	 */
	public function get($key)
	{
		try
		{
			return $this->container->get($key);
		}
		catch (AccessException $e)
		{
			throw new AccessException('"' . $key . '" doesn\'t exist in this object.');
		}
	}

	/**
	 * Set a link
	 *
	 * @param string $name The Name
	 * @param string|object $link The Link
	 *
	 * @return self
	 */
	protected function set($name, $link)
	{
		// from spec: aA link MUST be represented as either:
		// - a string containing the link's URL.
		// - an object ("link object") which can contain the following members:
		if ( ! is_object($link) and ! is_string($link) )
		{
			throw new ValidationException('Link attribute has to be an object or string, "' . gettype($link) . '" given.');
		}

		if ( is_string($link) )
		{
			$this->container->set($name, strval($link));

			return $this;
		}

		// Now $link can only be an object
		$this->container->set($name, $this->manager->getFactory()->make(
			'Link',
			[$link, $this->manager, $this]
		));

		return $this;
	}
}