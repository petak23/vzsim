<?php

namespace App\AdminModule\Components\Menu;
use Nette;

/**
 * Komponenta na vytvorenie menu
 * Posledna zmena 12.11.2020
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2020 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.6
 */
class Menu extends Nette\Application\UI\Control {
	var $rootNode; // = new MenuItem();
	var $separateMenuLevel;
	protected $_selected;
	var $allNodes = [];
	protected $_path = null;
	var $templatePath = [];
	public $idCounter = 0;
  protected $nastav = [
          "level" => 0,
          "templateType" => "tree",
          "nadpis" => FALSE,
          "divClass" => FALSE,
          "avatar" => "",
          "anotacia" => FALSE,
          "text_title_image" =>"Titulný obrázok",
					"article_avatar_view_in" => 0,
          "separator" => "|",
          ];
  private $nastavenie;

  public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		$this->templatePath = [
			'nav' => dirname(__FILE__) . '/Menu.latte',// sablona pro drobeckovou navigaci
			'single' => dirname(__FILE__) . '/Menu.latte',// sablona pro jednourovnovou cast menu
			'tree' => dirname(__FILE__) . '/Menu.latte',// sablona pro stromovou cast menu
      'podclanky' => dirname(__FILE__) . '/Podclanky.latte',// sablona pro stromovou cast menu
      'fixed' => dirname(__FILE__) . '/Fixed.latte',// sablona pro fixne menu
      'mapa' => dirname(__FILE__) . '/Mapa.latte',// sablona pro fixne menu
      'main_fixed' => dirname(__FILE__) . '/MainFixed.latte',// sablona pro fixne menu
		];
		$this->rootNode = new MenuNode();
		$this->rootNode->menu = $this;
		$this->rootNode->isRootNode = true;
	}
  
  public function setNastavenie($nastavenie) {
    $this->nastavenie = $nastavenie;
  }

	public function setSelected($node) {
      if (is_scalar($node)) {
      if (!isset($this->allNodes[$node])) { return;}
			$node = $this->allNodes[$node];
		};
		$this->_path = null;
		$this->_selected = $node;
	}

	public function getSelected() {
		return $this->_selected;
	}
  
  public function setTextTitleImage($text){
    $this->nastav["text_title_image"] = $text;
  }

	public function getPath() {
    if (!$this->_path) { $this->_path = $this->makePath(); }
		return $this->_path;
	}

	function makePath() {
		$node = $this->getSelected();
		$path = [];
		while ($node && ($node != $this->rootNode)) {
			$path[] = $node;
			$node = $node->parent;
		};
		$path = array_reverse($path);
		return $path;
	}

	public function render($part, $templateType) {
		$template = $this->template;
		$template->path = $this->getPath();
		$template->selected = $this->getSelected();
		$level = (int)$part;
		$template->startNode = ($level == 0) ? $this->rootNode : (isset($this->getPath()[$level - 1]) ? $this->getPath()[$level - 1] : null);
    if ($templateType == 'tree') { $template->showAll = false; }
    elseif ($templateType == 'podclanky') { $template->showAll = false; }
    elseif ($templateType == 'map') {
      $templateType = 'tree';
      $template->showAll = true;
    } elseif ($templateType == 'fixed' || $templateType == 'main_fixed') {
      $template->showAll = true;
      if (isset($this->template->nastav["cast"])) {
        $p = [];
        foreach ($this->rootNode->nodes as $v) { $p[] = $v->id; }
        $o = array_search(-1*$this->template->nastav["cast"], $p);
        $template->startNode = $this->rootNode->nodes[$o]; 
      } else {
        $template->startNode = $this->rootNode->nodes[0];
      }
    } elseif ($templateType == 'mapa') {
      $template->startNode = $this->allNodes;
    }
    $template->startLevel = $level;
		$template->templateType = $templateType;
    $template->spRootNode = $this->rootNode;
    $template->nastavenie = $this->nastavenie;
		$template->setFile($this->templatePath[$templateType]);
		$template->render();
	}

	public function renderNav($opt = 0) {
    $nastav = $this->nastav;
    $separator = ' > ';
		if (is_array($opt)) {
      if (isset($opt['separator'])) {$separator = $opt['separator'];}
			$level = $opt[0];
		} else {
			$level = $opt;
		};
		$this->template->navSeparator = $separator;
    $this->template->nastav = $nastav;
    $this->render($level, 'nav');
	}

  public function renderFixed($opt = 0) {
		if (is_array($opt)) {
      $level = isset($opt[0]) ? $opt[0] : 0;
      $nastav = array_merge($this->nastav, $opt);
		} else {
			$level = $opt;
      $nastav = $this->nastav;
		}
    $this->template->nastav = $nastav;
		$this->render($level, "fixed");
	}
  
  public function renderMainFixed($opt = 0) {
		if (is_array($opt)) {
      $level = isset($opt[0]) ? $opt[0] : 0;
      $nastav = array_merge($this->nastav, $opt);
		} else {
			$level = $opt;
      $nastav = $this->nastav;
		}
    $this->template->nastav = $nastav;
		$this->render($level, "main_fixed");
	}
  
	public function renderSingle($opt = 0) {
		$separator = ' | ';
		if (is_array($opt)) {
      $level = isset($opt[0]) ? $opt[0] : 0;
      if (isset($opt['separator'])) {
        $separator = $opt['separator'];
        unset($opt['separator']);
      }
      $nastav = array_merge($this->nastav, $opt);
		} else {
			$level = $opt;
      $nastav = $this->nastav;
		}
		$this->template->singleSeparator = $separator;
    $this->template->nastav = $nastav;
		$this->render($level, 'single');
	}

	public function renderTree($opt = 0) {
    if (is_array($opt)) {
      $level = isset($opt[0]) ? $opt[0] : 0;
      $nastav = array_merge($this->nastav, $opt);
		} else { $level = $opt;	$nastav = $this->nastav;}
    $this->template->nastav = $nastav;
    $this->render($level, 'tree');   
  }
  
  public function renderPodclanky($opt = 0) {
    if (is_array($opt)) {
      $level = $opt[0];
      $nastav = array_merge($this->nastav, $opt);
		} else { $level = $opt;	$nastav = $this->nastav;}
    $this->template->nastav = $nastav;
    $this->render($level, 'podclanky');   
  }

	public function renderMap($level = 0) {	
    $this->template->nastav = $this->nastav; $this->render($level, 'map');
  }
  public function renderMapa($level = 0) { 
    $this->template->nastav = $this->nastav; $this->render($level, 'mapa');	
  }

	public function fromTable($data, $setupNode) {
		$nodes = [];
		foreach($data as $row) {
			$node = new MenuNode;
			$parentId = $setupNode($node, $row);
			$nodes[$parentId][] = $node;
		}
		$this->linkNodes(null, $nodes);
	}

	protected function linkNodes($parentId, &$nodes) {
		if (isset($nodes[$parentId])) {
			foreach($nodes[$parentId] as $node) {
				if ($parentId) {
					$this->allNodes[$parentId]->add($node);
				} else {
					$this->rootNode->add($node);
				}
				$this->linkNodes($node->id, $nodes);
			}
		}
	}

	public function byId($id) {
		return $this->allNodes[$id];
	}

	public function selectByUrl($url) {
		foreach($this->allNodes as $node) {
			if ($url == $node->link) {
				$this->setSelected($node);
			}
		}
	}
  
  public function getTreeMenu() {
    $out = [];
    foreach ($this->rootNode->nodes as $node) {
      $out[$node->name] = $this->_node($node);
    }
    return $out;
  }
  
  private function _node($node) {
    $out = [];
    if (count($node->nodes)) {
      foreach ($node->nodes as $no) {
        $out[$no->id] = $no->name;
      }
    }
    return $out;
  }
  
  public function getFullTreeMenu() {
    $out = [];
    foreach ($this->rootNode->nodes as $node) {
      $out[$node->name] = $this->_fnode($node, 0);
    }
    return $out;
  }
  
  private function _fnode($node, $level) {
    $out = [];
    $l = "";
    for ($i=0; $i<$level; $i++) {
      $l .= "-";
    }
    if (count($node->nodes)) {
      foreach ($node->nodes as $no) {
        $out[$no->id] = (strlen($l) ? $l." " : "").$no->name;
        if (count($no->nodes)) {
          $level++;
          $out += $this->_fnode($no, $level);
          $level--;
        }
      }
    }
    return $out;
  }
}

class MenuNode {
  use Nette\SmartObject;
  
	var $name;
	var $tooltip;
  var $view_name;
	var $avatar;
	var $anotacia;
  var $node_class;
  var $poradie_podclankov = 0;
  var $datum_platnosti;
	var $link;	//Odkaz na polozku
	var $nodes = [];
	var $parent;
	var $id;
	var $menu;
	var $isRootNode = false;

	public function Add($node) {
		if (is_array($node)) {
			$newNode = new MenuNode;
			foreach($node as $k => $v) {
				$newNode->{$k} = $v;
			}
			$node = $newNode;
		}
		$node->parent = $this;
		$node->menu = $this->menu;
		if (!$node->id) {
			$node->id = '__auto_id_'.$this->menu->idCounter++;
		}
		$this->nodes[] = $node;
		$this->menu->allNodes[$node->id] = $node;
		return $node;
	}

	public function getItemClass() {
    $out = "";
		if ($this == $this->menu->getSelected()) {
			$out .= ' selected';
		} else if (in_array($this, $this->menu->getPath())) {
			$out .= ' in-path';
		}
    return $out;
	}
}
