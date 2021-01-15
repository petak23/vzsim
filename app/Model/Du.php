<?php
namespace App\Model;

/**
 * Model starající se o tabulku du
 */
class Du extends Table {
    /** @var string */
    protected $tableName = 'du';

    public function zalozDU($data) {
        if (($q = $this->findOneBy(['id_tu'=>$data['id_tu'], 'du'=>$data['du']])) === null) {
            $q = $this->pridaj($data);
        } 
        return $q;
    }
}