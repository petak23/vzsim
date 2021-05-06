<?php
namespace App\AdminModule\Components\Clanky\Komponenty;

use Nette\Application\UI;

class BaseControl extends UI\Control {
  
  
  /** 
   * Render */
	public function render() {
    $this->template->setFile(__DIR__ . '/Base.latte');
    $this->template->render();  
  }
}
