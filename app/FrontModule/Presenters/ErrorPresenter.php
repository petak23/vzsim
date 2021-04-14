<?php

namespace App\FrontModule\Presenters;

use Tracy\Debugger,
	  \Nette\Application as NA;
//use Language_support;
/**
 * Prezenter pre smerovanie na chybove stranky.
 * Posledna zmena(last change): 14.04.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.6
 *
 */
class ErrorPresenter extends BasePresenter {
  
	/**
	 * @param  Exception
	 * @return void */
	public function renderDefault($exception): void {
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = TRUE;
			$this->terminate();

		} elseif ($exception instanceof NA\BadRequestException) {
			$code = $exception->getCode();
      $code_a = in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx';
      $this->template->err_code = "err_".$code_a;
			$this->setView($code==500 ? "500" : "400");
			// log to access.log
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		} else {
			$this->setView('500'); // load template 500.latte
			Debugger::log($exception, Debugger::ERROR); // and log exception
		}
	}

}
