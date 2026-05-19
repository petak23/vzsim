<?php
namespace App\AdminModule\Presenters;
use App\Model;
/**
 * Domovský preseter administracie.
 *
 * Posledna zmena(last change): 19.05.2026
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2026 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.8
 */
class HomepagePresenter extends BasePresenter {
  
  // -- DB
  /** @var Model\Hlavne_menu_cast @inject */
  public $hlavne_menu_cast;
  
  /** Vychodzi render 
   * @param int $id Id casti hlavneho menu ako zaporne cislo */
  public function renderDefault(int $id = 0): void {
    $user = $this->user;
    $this->template->id_menu = -1*$id;
    $this->template->view_add_link = $user->isAllowed('Admin:Menu', 'addpol') && ($user->isInRole("admin")) &&
                                     ($id ? $this->id_reg >= $this->hlavne_menu_cast->find(-1*$id)->id_user_roles : FALSE);
	}
}