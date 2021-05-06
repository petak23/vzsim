<?php
namespace App\AdminModule\Presenters;
use App\AdminModule\Forms\User;
use App\Model;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation;

/**
 * Prezenter pre spravu uzivatela.
 * 
 * Posledna zmena(last change): 16.04.2021
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.9
 */
class UserPresenter extends BasePresenter {
  
  // -- DB
  /** @var Model\User_main @inject */
	public $user_main;
  /** @var Model\User_profiles @inject */
	public $user_profiles;
  
  // -- Forms
  /** @var User\AddUserFormFactory @inject*/
	public $addUserForm;
  /** @var User\EditUserMainFormFactory @inject*/
	public $editUserMainForm;
  /** @var User\EditUserProfilesFormFactory @inject*/
	public $editUserProfilesForm;
  
  /** @var \Nette\Database\Table\ActiveRow Udaje konkretneho clena*/
  private $clen;
  /** @var array Nastavenie zobrazovania volitelnych poloziek */
  protected function startup() {
    parent::startup();
    // Nastavenie zobrazovania volitelnych poloziek 
    $this->template->user_view_fields = $this->nastavenie['user_view_fields'];
	}
  /** 
   * Akcia pre editaciu clena
   * @param int $id Id editovaneho clena */
  public function actionEdit($id) {
    if (($this->clen = $this->user_main->find($id)) === FALSE) {
			$this->setView('notFound');
		} else {
      $this["editUserMainForm"]->setDefaults($this->clen);
      $this["editUserProfilesForm"]->setDefaults($this->clen->user_profiles);
    }
	}
  
  /**
   * Edit user form component factory. Tovarnicka na formular pre editaciu clena
   * @return \Nette\Application\UI\Form */
	protected function createComponentEditUserMainForm() {
    $form = $this->editUserMainForm->create($this->nastavenie['user_view_fields']);
    $form['uloz']->onClick[] = function ($button) { 
      $this->flashOut(!count($button->getForm()->errors), 'User:', 'Údaje boli uložené!', 'Došlo k chybe a údaje sa neuložili. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('User:');
		};
		return $this->_vzhladForm($form);
	}
  
  /**
   * Edit user form component factory. Tovarnicka na formular pre editaciu clena
   * @return \Nette\Application\UI\Form */
	protected function createComponentEditUserProfilesForm() {
    $form = $this->editUserProfilesForm->create($this->nastavenie['user_view_fields']);
    $form['uloz']->onClick[] = function ($button) { 
      $this->flashOut(!count($button->getForm()->errors), 'User:', 'Údaje boli uložené!', 'Došlo k chybe a údaje sa neuložili. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('User:');
		};
		return $this->_vzhladForm($form);
	}
  
  /** Formular pre pridanie uzivatela.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentAddUserForm() {
    $form = $this->addUserForm->create();
    $form['uloz']->onClick[] = function ($button) { 
      $this->flashOut(!count($button->getForm()->errors), 'User:', 'Nový užívateľ bol uložený!', 'Došlo k chybe a údaje sa neuložili. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('User:');
		};
		return $this->_vzhladForm($form);
	}
  /** 
   * Datagrid pre uzivatelov
   * @param type $name
   * @return DataGrid */
  public function createComponentUsersGrid($name) {
    $poc_pr_udaje = $this->user_profiles->getPocetPr();
    $sthis =$this;
		$grid = new DataGrid($this, $name);
    $grid->setDataSource($this->user_main->findAll()->order('id ASC'));
    if ($this->nastavenie['user_view_fields']['avatar']) { 
      $grid->addColumnText('avatar', 'Avatar', 'user_profiles.avatar')
            ->setRenderer(function($item) use ($sthis){
              $avatar = $sthis->nastavenie["dir_to_user"].$item->id."/".$item->user_profiles->avatar;
              return is_file($avatar) ? Html::el('img')->src($sthis->template->basePath."/".$avatar)->alt('avatar') : $item->user_profiles->avatar;
          })->setAlign('center')->addAttributes(['style' => 'width: 5rem;'])->setTemplateEscaping(FALSE);
    }
    $grid->addColumnNumber('id', 'Id')->setAlign('right')->setFormat(0)->addCellAttributes(['style' => 'width: 3rem;']);
    $grid->addColumnText('meno', 'Meno PRIEZVISKO; email')
         ->setRenderer(function($item) { return $item->meno . ' ' . mb_strtoupper($item->priezvisko).';<br><i>'.$item->email.'</i>';})->setTemplateEscaping(FALSE);
    $grid->addColumnText('id_user_roles', 'Registrácia', 'user_roles.name')
         ->setRenderer(function($item) {
           return Html::el('span', ['class'=> 'uroven-registracie uroven-registracie-'.$item->id_user_roles])
                          ->setHtml($item->id_user_roles.' - '.$item->user_roles->name. ' ('.$item->user_roles->role.')');
         })
         ->setAlign('left');
    $grid->addColumnText('pocet_pr', 'Počet prihlásení', 'user_profiles.pocet_pr')
          ->setRenderer(function($item) use ($poc_pr_udaje){
           $sirka = "width: ".((($item->user_profiles->pocet_pr*180)/$poc_pr_udaje['max'])+10)."px;";
           $prec = 100*$item->user_profiles->pocet_pr/$poc_pr_udaje['max'];
           return Html::el('div', ['class'=> 'vyb'.($prec>70 ? 1 : ($prec>45 ? 2 : ($prec>30 ? 3 : ($prec>0 ? 4 : 5)))), 'style'=>$sirka])->addHtml(Html::el('span')->setText($item->user_profiles->pocet_pr));
          })->addCellAttributes(['class' => 'pocet_pr'])->setTemplateEscaping(FALSE);
    $grid->addColumnDateTime('last_login', 'Posledné prihlásenie', 'user_profiles.last_login')->setFormat('d.m.Y H:i:s')->setAlign('right');
    $grid->addColumnText('news', 'Novinky', 'user_profile.news')
         ->setRenderer(function($item) { return Html::el('div', ['class'=> $item->user_profile->news == 'A' ? "vklad" : "vyber"])->setHtml($item->user_profile->news);})
         ->setTemplateEscaping(FALSE)->setAlign('center');
    if ($this->user->isInRole('admin')) {
      $grid->addAction('edit', '')->setIcon('pencil-alt')->setTitle('Edituj užívateľa');
      $grid->addAction('delete', '', 'confirmedDeleteClen!')
            ->setIcon('trash-alt')->setTitle('Zmazať užívateľa')->setClass('btn btn-xs btn-danger ajax')
            ->setConfirmation(
                new Confirmation\CallbackConfirmation(
                  function($item) {
                    return "Naozaj chceš zmazať užívateľa: '".$item->meno . ' ' . mb_strtoupper($item->priezvisko)."'?";
                  }
                )
              );
    }   
    return $grid;
	}
  
  /** 
   * Funkcia pre spracovanie signálu vymazavania clena
   * @param int $id Id clena */
	function handleConfirmedDeleteClen($id)	{
    $path = $this->nastavenie["wwwDir"].$this->nastavenie["dir_to_user"].$id;
    try {
      if (is_dir($path)) { //Vymazanie adresaru s avatarom
        foreach (glob("$path*.{jpg,jpeg,gif,png}", GLOB_BRACE) as $file) {
          @unlink($file);
        }
        rmdir($path);
      }
      $clen =$this->user_main->find($id);
      $meno = $clen->meno." ".$clen->priezvisko;
      $this->user_main->oprav($id, ['id_user_profiles'=>1]);
      $this->user_profiles->delUser($clen->id_user_profiles);
      $this->user_main->zmaz($id);
      $this->flashMessage('Užívateľ '.$meno.' bol zmazaný!', 'success');
    } catch (Exception $e) {
      $this->flashMessage('Došlo k chybe pri vymazávaní. Skúste neskôr znovu...'.$e->getMessage(), 'danger');
    }
    if ($this->isAjax()) {
      $this->redrawControl();
      $this['usersGrid']->reload();
    } else {
      $this->redirect('User:');
    }
  }
  
  /** Funkcia pre spracovanie signálu vymazavania
	  * @param int $id - id polozky v hlavnom menu
		* @param string $nazov - nazov polozky z hl. menu - na zrusenie?
		* @param string $druh - blizsia specifikacia, kde je to potrebne
		*/
	function confirmedDelete($id, $nazov, $druh = "")	{
    if ($druh === "admin") { 
      $path = $this->nastavenie["wwwDir"].$this->nastavenie["dir_to_user"].$id;
      if (is_dir($path)) { //Vymazanie adresaru s avatarom
        foreach (glob("$path*.{jpg,jpeg,gif,png}", GLOB_BRACE) as $file) {
          @unlink($file);
        }
        rmdir($path);
      }
      $clen =$this->user_main->find($id);
      $clen_id_user_profiles = $clen->id_user_profiles;
      $meno = $clen->meno." ".$clen->priezvisko;
      try {
        $this->user_main->oprav($id, ['id_user_profiles'=>NULL]);
        $this->user_profiles->delUser($clen_id_user_profiles);
        $this->user_main->zmaz($id);
        $this->flashMessage('Užívateľ '.$meno.' bol zmazaný!', 'success');
      } catch (Exception $e) {
        $this->flashMessage('Došlo k chybe pri vymazávaní. Skúste neskôr znovu...'.$e->getMessage(), 'danger');
      }
      if (!$this->isAjax()) { $this->redirect('User:'); }
    }
  }
}