<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author IDNK Soft <i@dnk.software>
 * @copyright  2021-2022 IDNK Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 */

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'idnk_csp` (
    `id_idnk_csp` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_idnk_csp`),
    `idnk_category_id` int(11) NOT NULL,
    `idnk_category_img` varchar(255) NOT NULL,
    `idnk_category_color` varchar(255) NOT NULL
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
