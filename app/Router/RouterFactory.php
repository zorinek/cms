<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('[<locale=en cs|en>/]', 'Frontend:Homepage:default');

		$router->addRoute('[<locale=en cs|en>/]articles/new', 'Article:Article:new');
		$router->addRoute('[<locale=en cs|en>/]articles/<art_id>[/<type>]', 'Article:Article:detail');
		$router->addRoute('[<locale=en cs|en>/]articles[/<type>]', 'Article:Article:overview');
		
		$router->addRoute('[<locale=en cs|en>/]comments/new', 'Comment:Comment:new');
		$router->addRoute('[<locale=en cs|en>/]comments[/<type>]', 'Comment:Comment:overview');
		$router->addRoute('[<locale=en cs|en>/]comments/detail/<com_id>[/<type>]', 'Comment:Comment:detail');

		return $router;
	}
}
