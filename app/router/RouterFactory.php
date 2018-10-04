<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\CliRouter;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter(\h4kuna\Gettext\GettextSetup $translator)
	{
		$router = new RouteList;
                $router[] = new CliRouter();
                $router[] = new Route('index.php', [
                    'presenter' => 'Home',
                    'action' => 'default',
                    'lang' => $translator->getDefault()]);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Home:default');
                $router[] = new Route('[<lang ' . $translator->routerAccept() . '>/]<presenter>/<action>/[<id>/]', [
                    'presenter' => 'Home',
                    'action' => 'default',
                    'lang' => $translator->getDefault()]);
                
		return $router;
	}
}
