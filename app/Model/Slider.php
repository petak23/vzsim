<?php

namespace App\Model;
use Nette;

/**
 * Model, ktory sa stara o tabulku slider
 * 
 * Posledna zmena 14.04.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.4
 */
class Slider extends Table {
  /** @var string */
  protected $tableName = 'slider';

  /** 
   * Vrati vsetky polozky z tabulky slider usporiadane podla "usporiadaj"
   * @param string $usporiadaj - názov stlpca, podla ktoreho sa usporiadava a sposob
   * @return Nette\Database\Table\Selection */
  function getSlider(string $usporiadaj = 'poradie ASC') {
		return $this->findAll()->order($usporiadaj);//->limit($pocet);
	}
  
  /** 
   * Vrati nasledujuce cislo poradia
   * @return int */
  public function getNextCounter(): int {
    $poradie = $this->findAll()->max('poradie');
    return $poradie ? (++$poradie) : 1;
  }
  
  /** 
   * Zmeni poradie prvkov
   * @param int $item_id Prvok, ktoreho poradie sa meni
   * @param int $prev_id Za ktory prvok sa vklada
   * @param int $next_id Pred ktory prvok sa vklada */
  public function sortItem($item_id, $prev_id, $next_id) {
    $item = $this->find($item_id);

    // 1. Find out order of item BEFORE current item 
    $previousItem = (!$prev_id) ? NULL : $this->find($prev_id);

    // 2. Find out order of item AFTER current item 
    $nextItem = (!$next_id) ? NULL : $this->find($next_id);

    // 3. Find all items that have to be moved one position up 
    $itemsToMoveUp = $this->findBy(['poradie <='.($previousItem ? $previousItem->poradie : 0), 'poradie >'. $item->poradie]);
    foreach ($itemsToMoveUp as $t) {
      $t->update(['poradie'=>($t->poradie - 1)]);
    }

    // 4. Find all items that have to be moved one position down
    $itemsToMoveDown = $this->findBy(['poradie >='.($nextItem ? $nextItem->poradie : 0), 'poradie <'. $item->poradie]);
    foreach ($itemsToMoveDown as $t) {
      $t->update(['poradie'=>($t->poradie + 1)]);
    }

    // 5. Update current item order
    if ($previousItem) {
      $item->update(['poradie'=>($previousItem->poradie + 1)]);
    } else if ($nextItem) {
      $item->update(['poradie'=>($nextItem->poradie - 1)]);
    } else {
      $item->update(['poradie'=>1]);
    }
  }
}