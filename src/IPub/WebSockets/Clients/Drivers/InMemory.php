<?php
/**
 * IDriver.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           23.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients\Drivers;

/**
 * Classic memory client storage driver
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class InMemory implements IDriver
{
	/**
	 * @var array
	 */
	private $elements;

	public function __construct()
	{
		$this->elements = [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch(int $id)
	{
		if (!$this->contains($id)) {
			return FALSE;
		}

		return $this->elements[$id];
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetchAll() : array
	{
		return array_values($this->elements);
	}

	/**
	 * {@inheritdoc}
	 */
	public function contains(int $id) : bool
	{
		return isset($this->elements[$id]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(int $id, $data, int $lifeTime = 0) : bool
	{
		$this->elements[$id] = $data; // Lifetime is not supported

		return TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $id) : bool
	{
		unset($this->elements[$id]);

		return TRUE;
	}
}
