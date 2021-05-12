<?php

namespace App;

use App\Model;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * Router
 * Posledna zmena 06.05.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2021 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
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
    $router->addRoute('index.php', 'Front:Homepage:default', Route::ONE_WAY);
		$router->withModule('Front')
            ->addRoute('Edit/import', 'Edit:import')
            ->addRoute('edit[/<id>]', [
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
            ])
            ->addRoute('run[/<id>]', [
              'presenter' => 'Run',
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
            ])
            ->addRoute('forgottenPassword', 'User:forgottenPassword')
            ->addRoute('profile', 'UserLog:default')
            ->addRoute('registration', 'User:registracia')
            ->addRoute('login', 'User:default')
            ->addRoute('user[/<action>]', 'User:default')
            ->addRoute('userlog[/<action>]/<id>', 'UserLog:default')
            ->addRoute('error[/<action>]', 'Error:default');
    $router->withModule('Admin')
            ->addRoute('administration/<presenter>/<action>', 'Homepage:default');
    $router->addRoute('<presenter>/<action>[/<id>]', 'Front:Homepage:default');
		return $router;
	}
}