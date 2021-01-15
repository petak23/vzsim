<?php

namespace App;

use App\Model;
use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

/**
 * Router
 * Posledna zmena 12.01.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class RouterFactory {
  
  /** @var Model\Oblast */
  public $oblast;
  
  /**
   * @param Model\Oblast $tu */
  public function __construct(Model\Oblast $oblast) {
    $this->oblast = $oblast;
  }
  
	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()	{
    $servis = $this;
		$router = new RouteList;
		$router[] = new Route('create-user/<username>/<password>', 'Sign:createUser');
		$router[] = new Route('forgottenPassword', 'User:forgottenPassword');
    $router[] = new Route('profile', 'UserLog:default');
    $router[] = new Route('registration', 'User:registracia');
    $router[] = new Route('login', 'User:default');
    $router[] = new Route('user[/<action>]', 'User:default');
    $router[] = new Route('userlog[/<action>]/<id>', 'UserLog:default');
    $router[] = new Route('Edit/import', 'Edit:import');
    $router[] = new Route('edit[/<id>]', [
      'presenter' => 'Edit',
      'action' => 'default',
      'id' => [ Route::FILTER_IN => function ($id) use ($servis) {
                    if (is_numeric($id)) {
                      return $id;
                    } else {
                      $hh = $servis->oblast->findOneBy(['web_name'=>$id]);
                      return $hh != null ? $hh->id : 0;
                    }
                },
                Route::FILTER_OUT => function ($id) use ($servis) {
                    if (!is_numeric($id)) {
                      return $id;
                    } else {
                      $hh = $servis->oblast->find($id);
                      return $hh != null ? $hh->web_name : 0;
                    }
                }
            ],
    ]);
    $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}