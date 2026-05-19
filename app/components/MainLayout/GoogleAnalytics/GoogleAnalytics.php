<?php

namespace PeterVojtech\MainLayout\GoogleAnalytics;

use DbTable;
use Nette\Application\UI\Control;
use Nette\Database;
use Nette\Http\Request;

/**
 * Komponenta pre vlozenie kodu pre google analytics do stranky
 * Posledna zmena(last change): 29.12.2025
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2025 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.6
 */

class GoogleAnalyticsControl extends Control
{
  /** @var Database\Table\ActiveRow|null */
  private $udaj;
  /** @var string */
  private $host;

  /** 
   * @param DbTable\Udaje $udaje 
   * @param Request $request */
  public function __construct(DbTable\Udaje $udaje, Request $request)
  {
    $this->udaj = $udaje->getValByName("google-analytics");
    $this->host = $request->getUrl()->host;
  }

  public function render()
  {
    $this->template->setFile(__DIR__ . '/GoogleAnalytics.latte');
    $this->template->id_google_analytics = ($this->udaj != FALSE & $this->host != "localhost") ? (strpos($this->udaj, "UA-") === 0 ? $this->udaj : FALSE) : FALSE;
    $this->template->render();
  }
}

interface IGoogleAnalyticsControl
{
  function create(): GoogleAnalyticsControl;
}
