<?php

namespace PeterVojtech\MainLayout\GoogleAnalytics;

/**
 * Traita pre favicon-y
 * 
 * Posledna zmena(last change): 04.01.2023
 * 
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.1
 */
trait googleAnalyticsTrait
{
	/** @var IGoogleAnalyticsControl @inject */
	public $googleAnalyticsFactory;

	/** 
	 * Vytvorenie komponenty */
	public function createComponentGoogleAnalytics(): GoogleAnalyticsControl
	{
		return $this->googleAnalyticsFactory->create();
	}
}
