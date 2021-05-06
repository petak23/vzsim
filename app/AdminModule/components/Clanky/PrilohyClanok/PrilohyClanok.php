<?php
declare(strict_types=1);

namespace App\AdminModule\Components\Clanky\PrilohyClanok;

use App\Model;
use Nette;
use Nette\Security\User;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

/**
 * Komponenta pre spravu priloh clanku.
 * 
 * Posledna zmena(last change): 14.04.2021
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.1.3
 */
class PrilohyClanokAControl extends Nette\Application\UI\Control {

  /** @var Model\Dokumenty $clanok Info o clanku */
  public $dokumenty;
  /** @var string $nazov_stranky */
  private $nazov_stranky;
  /** @var Nette\Database\Table\ActiveRow $clanok Info o clanku */
  private $clanok;
  /** @var array */
  private $prilohy_images;
  /** &var EditPrilohyFormFactory */
  public $editPrilohyForm;
  /** &var AddMultiPrilohyFormFactory */
  public $addMultiPrilohyForm;
  /** @var array */
  private $admin_links;
  /** @var Nette\Security\User */
  private $user;
  /** @var Model\Hlavne_menu */
  private $hlavne_menu;
  
  /** @var mixed */
  protected $big_img;

  /**
   * @param array $prilohy_images Nastavenie obrazkov pre prilohy - Nastavenie priamo cez servises.neon
   * @param Model\Dokumenty $dokumenty
   * @param Model\Hlavne_menu $hlavne_menu
   * @param EditPrilohyFormFactory $editPrilohyFormFactory
   * @param AddMultiPrilohyFormFactory $addMultiPrilohyFormFactory
   * @param User $user */
  public function __construct($prilohy_images,
                              Model\Dokumenty $dokumenty, Model\Hlavne_menu $hlavne_menu,
                              EditPrilohyFormFactory $editPrilohyFormFactory,
                              AddMultiPrilohyFormFactory $addMultiPrilohyFormFactory, 
                              User $user) {
    $this->dokumenty = $dokumenty;
    $this->editPrilohyForm = $editPrilohyFormFactory;
    $this->addMultiPrilohyForm = $addMultiPrilohyFormFactory;
    $this->user = $user;
    $this->hlavne_menu = $hlavne_menu;
    $this->prilohy_images = $prilohy_images;
  }
  
  /** 
   * Nastavenie komponenty
   * @param Nette\Database\Table\ActiveRow $clanok
   * @param string $nazov_stranky
   * @param string $name
   * @return PrilohyClanokControl */
  public function setTitle(Nette\Database\Table\ActiveRow $clanok, string $nazov_stranky, string $name): PrilohyClanokAControl {
    $this->clanok = $clanok;
    $this->nazov_stranky = $nazov_stranky;
    
    $hlm = $this->clanok->hlavne_menu; // Pre skratenie zapisu
    $vlastnik = $this->user->isInRole('admin') ? TRUE : $this->user->getIdentity()->id == $hlm->id_user_main;//$this->vlastnik($hlm->id_user_main);
    // Test opravnenia na pridanie podclanku: Si admin? Ak nie, si vlastnik? Ak nie, povolil vlastnik pridanie, editaciu? A mám dostatocne id reistracie?
    $opravnenie_add = $vlastnik ? TRUE : (boolean)($hlm->id_hlavne_menu_opravnenie & 1);
    $opravnenie_edit = $vlastnik ? TRUE : (boolean)($hlm->id_hlavne_menu_opravnenie & 2);
    $opravnenie_del = $vlastnik ? TRUE : (boolean)($hlm->id_hlavne_menu_opravnenie & 4);
    // Test pre pridanie a odkaz: 0 - nemám oprávnenie; 1 - odkaz bude na addpol; 2 - odkaz bude na Clanky:add
    $druh_opravnenia = $opravnenie_add ? ($this->user->isAllowed($name, 'addpol') ? 1 : ($this->user->isAllowed($this->name, 'add') ? 2 : 0)) : 0;
    $this->admin_links = [
      "alink" => ["druh_opravnenia" => $druh_opravnenia,
                  "link"    => $druh_opravnenia ? ($druh_opravnenia == 1 ? ['main'=>$name.':addpol']
                                                                         : ['main'=>'Clanky:add', 'uroven'=>$hlm->uroven+1]) : NULL,
                  "text"    => "Pridaj podčlánok"
                 ],
      "elink" => $opravnenie_edit && $this->user->isAllowed($name, 'edit'),
      "dlink" => $opravnenie_del && $this->user->isAllowed($name, 'del') && !$this->hlavne_menu->maPodradenu($this->clanok->id_hlavne_menu),
      "vlastnik" => $vlastnik,
    ];
    return $this;
  }
  
  /** 
   * Render */
	public function render() {
    $this->template->setFile(__DIR__ . '/PrilohyClanok.latte');
    $this->template->clanok = $this->clanok;
    $this->template->admin_links_prilohy = $this->admin_links;
    $this->template->big_img = $this->big_img;
		$this->template->render();
	}
  
  /**
   * Grid
   * @param string $name */
  public function createComponentPrilohyGrid(string $name) {
		$grid = new DataGrid($this, $name);
		$grid->setDataSource($this->dokumenty->findBy(['id_hlavne_menu'=>$this->clanok->id_hlavne_menu]));
    $grid->addColumnText('znacka', 'Značka');
    $grid->addColumnText('main_file', 'Súbor')
         ->setRenderer(function($item){
            return Html::el('button', ['class' => 'btn btn-link btn-for-big-image'])
                           ->data('toogle', 'modal')
                           ->data('target', '#imageModalCenterAttachments')
                           ->data('imgsrc', $item->main_file)
                           ->data('imgname', $item->name)
                           ->setHtml(Html::el('img', ['class' => 'img-thumbnail'])->src($this->template->basePath.'/'.$item->thumb_file)->alt($item->name));      
         });
    $grid->addColumnText('name', 'Názov')
         ->setEditableCallback(function($id, $value) {
           $this->dokumenty->oprav($id, ['name'=>$value]);
         });
    $grid->addColumnText('description', 'Popis')
         ->setEditableCallback(function($id, $value) {
           $this->dokumenty->oprav($id, ['description'=>$value]);
         });
    if ($this->admin_links['elink']) {
      $grid->addAction('edit', '')
           ->setIcon('pencil-alt fa-2x')
           ->setClass('btn btn-success btn-sm')
           ->setTitle('Editácia položky');
      $grid->addAction('delete', '', 'confirmedDelete!')
           ->setIcon('trash-alt fa-2x')
           ->setClass('btn btn-danger btn-sm ajax')
           ->setTitle('Vymazanie položky')
           ->setConfirmation(
              new Confirmation\CallbackConfirmation(
                function($item) {
                  return sprintf('Naozaj chceš zmazať položku %s?', $item->name);
                }
              )
            );
      $grid->addAction('showInText', '')
           ->setIcon('adjust fa-2x')
           ->setClass(function($item) { 
                        $pr = strtolower($item->pripona);
                        return ($pr == 'jpg' OR $pr == 'png' OR $pr == 'gif' OR $pr == 'bmp') ? ("btn ".($item->zobraz_v_texte ? 'btn-success' : 'btn-warning')." btn-sm ajax") : 'display-none' ; 
                      })
           ->setTitle('Nezobraz obrázok v prílohách');
    }
    
    $translator = new SimpleTranslator([
      'ublaboo_datagrid.no_item_found_reset' => 'Žiadné položky neboli nájdené. Filter môžete vynulovať...',
      'ublaboo_datagrid.no_item_found' => 'Žiadné položky neboli nájdené.',
      'ublaboo_datagrid.here' => 'tu',
      'ublaboo_datagrid.items' => 'Položky',
      'ublaboo_datagrid.all' => 'všetky',
      'ublaboo_datagrid.from' => 'z',
      'ublaboo_datagrid.reset_filter' => 'Resetovať filter',
      'ublaboo_datagrid.group_actions' => 'Hromadné akcie',
      'ublaboo_datagrid.show_all_columns' => 'Zobraziť všetky stĺpce',
      'ublaboo_datagrid.hide_column' => 'Skryť stĺpec',
      'ublaboo_datagrid.action' => 'Akcia',
      'ublaboo_datagrid.previous' => 'Predošlá',
      'ublaboo_datagrid.next' => 'Daľšia',
      'ublaboo_datagrid.choose' => 'Vyberte',
      'ublaboo_datagrid.execute' => 'Vykonať',
      'ublaboo_datagrid.short' => 'Usporiadaj',
    ]);
    $grid->setTranslator($translator);
    $grid->setRememberState(false);
	}
  
  /**
   * Signal na editaciu
   * @param int $id Id polozky na editaciu */
  public function handleEdit(int $id): void {
    $this->presenter->redirect('Dokumenty:edit', $id);
  }
  
  /**
   * Signal pre zobrazenie velkeho nahladu obrazka
   * @param int $id_big_image 
   * @return void */
  public function handleBigImg(int $id_big_image): void {
    $this->big_img = $this->dokumenty->find($id_big_image);
    if ($this->httpRequest->isAjax()) {
      $this->redrawControl('lightbox-image-a');
    }
  }
  
  /** 
   * Komponenta formulara pre pridanie a editaciu prílohy polozky.
   * @return Nette\Application\UI\Form */
  public function createComponentEditPrilohyForm(): Nette\Application\UI\Form {
    $form = $this->editPrilohyForm->create();
    $form->setDefaults(["id"=>0, "id_hlavne_menu"=>$this->clanok->id_hlavne_menu, "id_user_roles"=>$this->clanok->hlavne_menu->id_user_roles]);
    $form['uloz']->onClick[] = function ($button) { 
      //$this->presenter->flashOut(!count($button->getForm()->errors), ['this',['tab'=>'prilohy-tab']], 'Príloha bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
      if (!count($button->getForm()->errors)) {
        $this->presenter->flashRedirect(['this',['tab'=>'prilohy-tab']], 'Príloha bola úspešne uložená!', 'success');
      } else {
        $this->flashMessage('Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...', 'danger');
        $this->flashMessage($button->getForm()->errors);
      }
		};
    return $this->presenter->_vzhladForm($form);
  }
  
  /** 
   * Komponenta formulara pre pridanie viacerich prílohy polozky.
   * @return Nette\Application\UI\Form */
  public function createComponentAddMultiPrilohyForm(): Nette\Application\UI\Form {
    $form = $this->addMultiPrilohyForm->create();
    $form->setDefaults(["id"=>0, "id_hlavne_menu"=>$this->clanok->id_hlavne_menu, "id_user_roles"=>$this->clanok->hlavne_menu->id_user_roles]);
    $form['uloz']->onClick[] = function ($button) { 
      $this->presenter->flashOut(!count($button->getForm()->errors), ['this',['tab'=>'prilohy-tab']], 'Prílohy boli úspešne uložené!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    return $this->presenter->_vzhladForm($form);
  }
  
  public function handleShowInText(int $id): void {
    $priloha = $this->dokumenty->find($id);
    $priloha->update(['zobraz_v_texte'=>(1 - $priloha->zobraz_v_texte)]);
		if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('flashes');
      $this->redrawControl('prilohy-in');
    }
  }
  
  /** 
   * Signal vymazavania
	 * @param int $id Id polozky na zmazanie */
	function handleConfirmedDelete(int $id): void {
    $pr = $this->dokumenty->find($id);//najdenie prislusnej polozky menu, ku ktorej priloha patri
    $pthis = $this->presenter;
    if ($pr !== FALSE) {
      //$vysledok = $this->_vymazSubor($pr->main_file) ? (in_array(strtolower($pr->pripona), ['png', 'gif', 'jpg']) ? $this->_vymazSubor($pr->thumb_file) : TRUE) : FALSE;
      $vysledok = FALSE;
      if (($vysledok ? $pr->delete() : FALSE)) { 
        $this->flashMessage('Príloha bola vymazaná!', 'success'); 
      } else { 
        $this->flashMessage('Došlo k chybe a príloha nebola vymazaná!', 'danger'); 
      }
    } else { $this->flashMessage('Došlo k chybe a príloha nebola vymazaná!', 'danger');}
    if (!$pthis->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('flashes');
      //$this->redrawControl('prilohy');
      $this['prilohyGrid']->reload();
    }
  }
  
  /** 
   * Funkcia vymaze subor ak exzistuje
	 * @param string $subor Nazov suboru aj srelativnou cestou
	 * @return int Ak zmaze alebo neexistuje(nie je co mazat) tak 1 inak 0 */
	private function _vymazSubor(string $subor): int {
		return (is_file($subor)) ? (int)unlink($this->presenter->context->parameters["wwwDir"]."/".$subor) : -1;
	}
}

interface IPrilohyClanokAControl {
  /** @return PrilohyClanokAControl */
  function create();
}