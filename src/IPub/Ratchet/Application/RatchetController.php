<?php
/**
 * RatchetController.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPubModule;

use Nette;
use Nette\DI;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;

/**
 * Ratchet micro controller
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class RatchetController implements UI\IController
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var DI\Container|NULL
	 */
	private $context;

	/**
	 * @var Router\IRouter
	 */
	private $router;

	/**
	 * @var Application\Request
	 */
	private $request;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param DI\Container|NULL $context
	 * @param Router\IRouter|NULL $router
	 */
	public function __construct(DI\Container $context = NULL, Router\IRouter $router = NULL)
	{
		$this->context = $context;
		$this->router = $router;
	}

	/**
	 * Gets the context
	 *
	 * @return Nette\DI\Container
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param Application\Request $request
	 *
	 * @return Responses\IResponse
	 *
	 * @throws Exceptions\BadRequestException
	 */
	public function run(Application\Request $request) : Responses\IResponse
	{
		$this->name = $request->getControllerName();

		$this->request = $request;

		$params = $request->getParameters();

		if (!isset($params['callback'])) {
			throw new Exceptions\BadRequestException('Parameter callback is missing.');
		}

		$callback = $params['callback'];

		$reflection = Utils\Callback::toReflection(Utils\Callback::check($callback));

		if ($this->context) {
			foreach ($reflection->getParameters() as $param) {
				if ($param->getClass()) {
					$params[$param->getName()] = $this->context->getByType($param->getClass()->getName(), FALSE);
				}
			}
		}

		$params['controller'] = $this;
		$params = Nette\Application\UI\ComponentReflection::combineArgs($reflection, $params);

		$response = call_user_func_array($callback, $params);

		if (is_string($response)) {
			$response = new Responses\MessageResponse($response);

		} elseif (!$response instanceof Responses\IResponse) {
			$response = new Responses\CallResponse($response->callback, $response->data);
		}

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return Nette\Application\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}
}