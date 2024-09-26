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

class AdminCblocksController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'edit';
        $this->context = Context::getContext();
        parent::__construct();

        $this->meta_title = $this->l('Category configuration');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('saveCategoryData') && isset($_FILES['category_img'])) {
            $category_img = basename($_FILES['category_img']["name"]);
            $color = Tools::getValue('category_color');
            $uploadOk = 1;
            /*===================== Handling Image for Prestashop Upload: Start ===================== */
            //file upload code

            if (isset($_FILES['category_img']) && !empty($_FILES['category_img']["name"])) {
                //$target_dir = _PS_UPLOAD_DIR_;
                $target_dir = "..".__PS_BASE_URI__."modules/idnkcategoryshowproducts/views/img/";
                $target_file = $target_dir . basename($_FILES['category_img']["name"]);
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                // Check if image file is a actual image or fake image
                $check = getimagesize($_FILES['category_img']["tmp_name"]);
                if ($check !== false) {
                    $uploadOk = 1;
                } else {
                    $this->redirect_after = Context::getContext()->link->getAdminLink('AdminModules', false).
                        '&configure=idnkcategoryshowproducts&cb_error=3&token=' . Tools::getAdminTokenLite('AdminModules');
                    $uploadOk = 0;
                }
                // Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                    && $imageFileType != "gif") {
                    $this->redirect_after = Context::getContext()->link->getAdminLink('AdminModules', false).
                        '&configure=idnkcategoryshowproducts&cb_error=1&token=' . Tools::getAdminTokenLite('AdminModules');
                    $uploadOk = 0;
                }
                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk != 0) {
                    if (!move_uploaded_file($_FILES['category_img']["tmp_name"], $target_file)) {
                        $this->redirect_after = Context::getContext()->link->getAdminLink('AdminModules', false).
                            '&configure=idnkcategoryshowproducts&cb_error=2&token=' . Tools::getAdminTokenLite('AdminModules');
                    }
                }
            }
            /*===================== Handling Image for Prestashop Upload: End ===================== */
            if ($uploadOk == 1) {
                if (!empty($_FILES['category_img']["name"])) {
                    Db::getInstance()->update(
                        'idnk_csp',
                        [
                            'idnkcategory_img' => pSQL($category_img),
                            'idnkcategory_color' => pSQL($color),
                        ],
                        'id_idnk_csp = '. Tools::getValue('category_id')
                    );
                } else {
                    Db::getInstance()->update(
                        'idnk_csp',
                        [
                            'idnkcategory_color' => pSQL($color),
                        ],
                        'id_idnk_csp = '. Tools::getValue('category_id')
                    );
                }
                // all is ok, redirect to main
                $this->redirect_after = Context::getContext()->link->getAdminLink('AdminModules', false).
                    '&configure=idnkcategoryshowproducts&cb_edited=true&token=' . Tools::getAdminTokenLite('AdminModules');
            }
        }
    }

    public function init()
    {
        parent::init();
    }

    private function fileUploadMaxSize()
    {
        static $max_size = -1;
        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }
            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    public function getSelection()
    {
        if (!empty(Tools::getValue('id_category'))) {
            $get = Tools::getValue('id_category');
            $db = Db::getInstance();
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'idnk_csp WHERE id_idnk_csp = ' . (int)$get;
            $results = $db->ExecuteS($sql);
            if (!empty($results)) {
                return $results[0];
            }
        }
        // Devuelve un array vacío si no se encuentra ningún registro
        return [];
    }

    public function getCategoryName($id)
    {
        $category = new Category($id);
        return $category->name;
    }

    public function initContent()
    {
        parent::initContent();

        $selection = $this->getSelection();

        if (!empty($selection)) {
            $this->context->smarty->assign([
                'id'=> isset($selection['id_idnk_csp']) ? $selection['id_idnk_csp'] : null,
                'name' => isset($selection['idnkcategory_id']) ? $this->getCategoryName((int)$selection['idnkcategory_id'])[1] : '',
                'image_url' => isset($selection['idnkcategory_img']) ? $selection['idnkcategory_img'] : '',
                'color' => isset($selection['idnkcategory_color']) ? $selection['idnkcategory_color'] : '',
                'base_url' => Context::getContext()->shop->getBaseURL(true),
                'max_upload_size' => $this->fileUploadMaxSize(),
            ]);
        } else {
            // Maneja el caso cuando no hay selección
            $this->context->smarty->assign([
                'id' => null,
                'name' => '',
                'image_url' => '',
                'color' => '',
                'base_url' => Context::getContext()->shop->getBaseURL(true),
                'max_upload_size' => $this->fileUploadMaxSize(),
            ]);
        }

        $this->setTemplate('category.tpl');
    }

}
