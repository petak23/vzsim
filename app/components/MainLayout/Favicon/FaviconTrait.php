<?php

namespace PeterVojtech\MainLayout\Favicon;

/**
 * Traita pre favicon-y
 * 
 * Posledna zmena(last change): 30.05.2019
 * 
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2019 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */
 trait faviconTrait {
  /** @var IFaviconControl @inject */
  public $faviconFactory;
  
  /** 
   * Vytvorenie komponenty 
   * @return FaviconControl */
	public function createComponentFavicon() {
    return $this->faviconFactory->create();
	}
}