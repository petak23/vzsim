ALTER TABLE `vlaky`
CHANGE `cesta` `cesta` varchar(500) COLLATE 'utf8_bin' NULL COMMENT 'Cestovný poriadok' AFTER `sm`;

ALTER TABLE `vlaky`
ADD `cp_miesta` varchar(500) COLLATE 'utf8_bin' NULL COMMENT 'CP miesta oddelené |',
ADD `cp_cas` varchar(500) COLLATE 'utf8_bin' NULL COMMENT 'CP časy oddelené |' AFTER `cp_miesta`,
ADD `cp_kolaj` varchar(500) COLLATE 'utf8_bin' NULL COMMENT 'CP odporúčané kolaje v staniciach oddelené |' AFTER `cp_cas`;

UPDATE `oblast_prvky` SET `oznacenie` = '3' WHERE `id_prvky_kluc` = '14' AND `id` = '20';
UPDATE `oblast_prvky` SET `oznacenie` = '1' WHERE `id_prvky_kluc` = '14' AND `id` = '37';
UPDATE `oblast_prvky` SET `oznacenie` = '2' WHERE `id_prvky_kluc` = '14' AND `id` = '53';
UPDATE `oblast_prvky` SET `oznacenie` = '4' WHERE `id_prvky_kluc` = '14' AND `id` = '61';
UPDATE `oblast_prvky` SET `oznacenie` = '3' WHERE `id_prvky_kluc` = '14' AND `id` = '78';
UPDATE `oblast_prvky` SET `oznacenie` = '1' WHERE `id_prvky_kluc` = '14' AND `id` = '97';
UPDATE `oblast_prvky` SET `oznacenie` = '2' WHERE `id_prvky_kluc` = '14' AND `id` = '125';
UPDATE `oblast_prvky` SET `oznacenie` = '4' WHERE `id_prvky_kluc` = '14' AND `id` = '146';
UPDATE `oblast_prvky` SET `oznacenie` = '5' WHERE `id_prvky_kluc` = '14' AND `id` = '155';
UPDATE `oblast_prvky` SET `oznacenie` = '6' WHERE `id_prvky_kluc` = '14' AND `id` = '161';
UPDATE `oblast_prvky` SET `oznacenie` = '3' WHERE `id_prvky_kluc` = '14' AND `id` = '174';
UPDATE `oblast_prvky` SET `oznacenie` = '8' WHERE `id_prvky_kluc` = '14' AND `id` = '184';
UPDATE `oblast_prvky` SET `oznacenie` = '1' WHERE `id_prvky_kluc` = '14' AND `id` = '193';
UPDATE `oblast_prvky` SET `oznacenie` = '5' WHERE `id_prvky_kluc` = '14' AND `id` = '213';
UPDATE `oblast_prvky` SET `oznacenie` = '3' WHERE `id_prvky_kluc` = '14' AND `id` = '225';
UPDATE `oblast_prvky` SET `oznacenie` = '3' WHERE `id_prvky_kluc` = '14' AND `id` = '235';
UPDATE `oblast_prvky` SET `oznacenie` = '1' WHERE `id_prvky_kluc` = '14' AND `id` = '257';
UPDATE `oblast_prvky` SET `oznacenie` = '1' WHERE `id_prvky_kluc` = '14' AND `id` = '277';
UPDATE `oblast_prvky` SET `oznacenie` = '2' WHERE `id_prvky_kluc` = '14' AND `id` = '307';
UPDATE `oblast_prvky` SET `oznacenie` = '2' WHERE `id_prvky_kluc` = '14' AND `id` = '328';
UPDATE `oblast_prvky` SET `oznacenie` = '4' WHERE `id_prvky_kluc` = '14' AND `id` = '343';
UPDATE `oblast_prvky` SET `oznacenie` = '4' WHERE `id_prvky_kluc` = '14' AND `id` = '357';
UPDATE `oblast_prvky` SET `oznacenie` = '6' WHERE `id_prvky_kluc` = '14' AND `id` = '367';
UPDATE `oblast_prvky` SET `oznacenie` = '8' WHERE `id_prvky_kluc` = '14' AND `id` = '377';

UPDATE `oblast_prvky` SET
`n0` = '0'
WHERE `id_prvky_kluc` = '14';