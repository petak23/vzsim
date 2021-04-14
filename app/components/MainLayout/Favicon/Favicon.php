<?php
namespace PeterVojtech\MainLayout\Favicon;

use Nette\Application\UI\Control;

/**
 * Komponenta pre vlozenie kodu pre favicon-y
 * Posledna zmena(last change): 30.05.2019
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2019 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
class FaviconControl extends Control {
  
  public function render() {
    $this->template->setFile(__DIR__ . '/Favicon.latte');
    $this->template->render();
  }
}

interface IFaviconControl {
  /** @return FaviconControl */
  function create();
}