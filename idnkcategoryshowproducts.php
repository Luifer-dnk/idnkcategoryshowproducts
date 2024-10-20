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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class IdnkCategoryshowproducts extends Module
{
    protected $config_form = false;



    public function __construct()
    {
        $this->name = 'idnkcategoryshowproducts';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'IDNK Soft';
        $this->need_instance = 1;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Responsive category blocks');
        $this->description = $this->l('Responsive category blocks are displayed anywhere in your homepage. Customize the blocks to your taste and increase your client user experience.');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        if (!$this->isRegisteredInHook('displayHome')) {
            $this->registerHook('displayHome');
        }

    }

    public function install()
    {
        Configuration::updateValue('IDNK_CSP_LIVE_MODE', true);
        include(__DIR__ .'/sql/install.php');
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('IDNK_CSP_LIVE_MODE');
        Configuration::deleteByName('IDNK_CSP_SELECTED_CAT');
        include(__DIR__ .'/sql/uninstall.php');
        return parent::uninstall();
    }

    public function processConfiguration()
    {
        $sql = 'SELECT value FROM '._DB_PREFIX_.'configuration WHERE name = "IDNK_CSP_SELECTED_CAT"';
        $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!empty($value[0])) {
            $selected = explode(',', $value['value']);
            $first_cat_id = array_shift($selected);

            $category = new Category($first_cat_id);
            $category_name = $category->getName((int)Context::getContext()->language->id);

            $this->context->smarty->assign([
                'category' => $category_name,
            ]);
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitIdnkcategoryshowproductsModule'))) {
            $this->postProcess();
        }
        $this->processConfiguration();
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $message = "";
        if ((bool)Tools::getValue('cb_edited')) {
            $message = $this->displayConfirmation($this->trans('The settings have been updated.', [], 'Admin.Notifications.Success'));
        }
        if (!empty(Tools::getValue('cb_error'))) {
            $error = (int)Tools::getValue('cb_error');
            switch ($error) {
                case 1:
                    $message = $this->displayError($this->trans('Selected image is not an image file. Please choose only .jpg, .png or .gif images'));
                    break;
                case 2:
                    $message = $this->displayError($this->trans('Selected image failed to upload to server. Please try again'));
                    break;
                case 3:
                    $message = $this->displayError($this->trans('Selected image file size is too big'));
                    break;
                case 4:
                    $message = $this->displayError($this->trans('There was an issue processing your image. Please try again'));
                    break;
            }
        }
        return $message.$this->renderList().$this->renderForm().$output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $output = "";
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitIdnkcategoryshowproductsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];
        $output .= $helper->generateForm([$this->getConfigForm()]);
        return $output;
    }

    public function renderList()
    {
        // Select all available extra info tabs
        $db = Db::getInstance();
        $sql='SELECT * FROM ' . _DB_PREFIX_ . 'idnk_csp ORDER BY idnkcategory_id ASC';
        $results=$db->ExecuteS($sql);
        $index = 0;
        foreach ($results as $row) {
            $category = new Category($row['idnkcategory_id']);
            $results[$index]['idnkcategory_name'] = $category->name[1];
            $index++;
        }
        if ($results != null) {
            $fields_list = [
                'id_idnk_csp' => [
                    'title' => 'ID',
                    'width' => '100',
                    'type' => 'text',
                    'visible' => 'false'
                ],
                'idnkcategory_name' => [
                    'title' => 'Category name',
                    'width' => 'auto',
                    'type' => 'text'
                ],
                'idnkcategory_id' => [
                    'title' => 'Category ID',
                    'width' => 'auto',
                    'type' => 'text'
                ],
                'idnkcategory_img' => [
                    'title' => 'Image',
                    'width' => 'auto',
                    'type' => 'text',
                    'prefix' => '<img class="image-box" src="../modules/idnkcategoryshowproducts/views/img/',
                    'suffix' => '" alt="Category image" />',
                ],
                'idnkcategory_color' => [
                    'title' => 'Color',
                    'width' => 'auto',
                    'type' => 'text',
                    'maxlength' => 7,
                    'prefix' => '<div class="color-box" style="background-color:',
                    'suffix' => '"></div>',
                ],
                'idnkcategory_shadow' => [
                    'title' => 'Shadow',
                    'width' => 'auto',
                    'type' => 'text',
                    'maxlength' => 7,
                    'prefix' => '<div class="color-box" style="text-shadow:',
                    'suffix' => '"></div>',
                ],
                'idnkcategory_txtsize' => [
                    'title' => 'Text Size',
                    'width' => 'auto',
                    'type' => 'text',
                    'maxlength' => 7,
                ],
            ];
            $helper_list = new HelperList();
            $helper_list->module = $this;
            $helper_list->shopLinkType = '';
            $helper_list->no_link = true;
            $helper_list->simple_header = true;
            $helper_list->identifier = 'id_idnk_csp';
            $helper_list->actions = ['editCategory'];
            $helper_list->search = true;
            $helper_list->show_toolbar = false;
            $helper_list->title = 'Active categories';
            $helper_list->table = 'categories';
            $helper_list->token = Tools::getAdminTokenLite('AdminModules');
            $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
            return $helper_list->generateList($results, $fields_list);
        }
        return false;
    }

    public function displayEditCategoryLink($name = null, $id = null, $token = null)
    {
        $this->smarty->assign([
            'href' => 'index.php?controller=AdminCblocks&id_category=' . (int) $id . '&updatecblocks&token=' . Tools::getAdminTokenLite('AdminCblocks'),
            'action' => $this->trans('Configure',[], 'Admin.Actions'),
            'disable' => !((int) $id > 0),
        ]);
        return $this->display(__FILE__, 'views/templates/admin/cblocks/editcategory.tpl');
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'configuration WHERE name = "IDNK_CSP_SELECTED_CAT"';
        $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if ($value && isset($value['value'])) {
            $selected = explode(',', $value['value']);
        } else {
            $selected = [];
        }

        $root = Category::getRootCategory();
        $tree = new HelperTreeCategories('categories_col1');
        $tree->setUseCheckBox(true)
            ->setAttribute('is_category_filter', $root->id)
            ->setRootCategory($root->id)
            ->setFullTree(true)
            ->setSelectedCategories($selected)
            ->setInputName('IDNK_CSP_SELECTED_CAT');
        $categoryTreeCol1 = $tree->render();

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'IDNK_CSP_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable this module'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            ]
                        ],
                    ],
                    [
                        'type' => 'categories_select',
                        'label' => $this->l('Categories'),
                        'desc' => $this->l('Select categories which will be displayed in home page. Save the selection for categories to appear in the list.'),
                        'name' => 'IDNK_CSP_SELECTED_CAT',
                        'category_tree' => $categoryTreeCol1
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $categories = explode(",", Configuration::get('IDNK_CSP_SELECTED_CAT'));
        return [
            'IDNK_CSP_LIVE_MODE' => Configuration::get('IDNK_CSP_LIVE_MODE', true),
            'IDNK_CSP_SELECTED_CAT' => $categories,
        ];
    }

    /**
     * Save form data.
     */
protected function postProcess()
{
    $form_values = $this->getConfigFormValues();
    foreach (array_keys($form_values) as $key) {
        Configuration::updateValue($key, Tools::getValue($key));

        if ($key == 'IDNK_CSP_SELECTED_CAT') {
            if (!empty(Tools::getValue($key))) {
                if (count(Tools::getValue($key)) > 1) {
                    $categories = implode(",", Tools::getValue($key));
                } else {
                    $categories = Tools::getValue($key)[0] . ",";
                }

                Configuration::updateValue('IDNK_CSP_SELECTED_CAT', $categories);
                $db = Db::getInstance();

                // Obtener los IDs de las categorías existentes
                $sql = 'SELECT idnkcategory_id FROM ' . _DB_PREFIX_ . 'idnk_csp';
                $results = $db->ExecuteS($sql);
                $db_categories = [];
                foreach ($results as $res) {
                    foreach ($res as $sel) {
                        array_push($db_categories, $sel);
                    }
                }

                // Calcular categorías a eliminar y a crear
                $toDelete = array_diff($db_categories, Tools::getValue($key));
                $toCreate = array_diff(Tools::getValue($key), $db_categories);

                // Eliminar categorías que ya no están seleccionadas
                if (count($toDelete) > 0) {
                    $q_delete_ids = implode(', ', array_map('intval', $toDelete));
                    $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'idnk_csp WHERE idnkcategory_id IN (' . $q_delete_ids . ')';
                    $db->Execute($sql);
                }

                // Preparar nuevas categorías para insertar
                $_sql = [];
                foreach ($toCreate as $row) {
                    $defaultImage = "default.jpg"; 
                    $defaultColor = "#333333"; 
                    $defaultShadow = "#000000";
                    $defaultTxtSize = "16px";

                    $_sql[] = '(' . $db->escape($row) . ', "' . $defaultImage . '", "' . $defaultColor . '", "' . $defaultShadow . '", "' . $defaultTxtSize . '")';
                }

                if (count($toCreate) > 0) {
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'idnk_csp 
                            (idnkcategory_id, idnkcategory_img, idnkcategory_color, idnkcategory_shadow, idnkcategory_txtsize)
                            VALUES ' . implode(',', $_sql);
                    $db->Execute($sql);
                }

                // Actualizar los campos shadow y txtsize si es necesario
                foreach (Tools::getValue($key) as $categoryId) {
                    $shadowColor = Tools::getValue('shadow_color_' . $categoryId);
                    $txtSize = Tools::getValue('txt_size_' . $categoryId); // Asumiendo que tienes un campo para el tamaño de texto por categoría
                    
                    // Actualizar sombra si es necesario
                    if ($shadowColor) {
                        $sql = 'UPDATE ' . _DB_PREFIX_ . 'idnk_csp
                                SET idnkcategory_shadow = "' . $db->escape($shadowColor) . '"
                                WHERE idnkcategory_id = ' . (int)$categoryId;
                        $db->Execute($sql);
                    }

                    // Actualizar tamaño de texto si es necesario
                    if ($txtSize) {
                        $sql = 'UPDATE ' . _DB_PREFIX_ . 'idnk_csp
                                SET idnkcategory_txtsize = "' . $db->escape($txtSize) . '"
                                WHERE idnkcategory_id = ' . (int)$categoryId;
                        $db->Execute($sql);
                    }
                }

            } else {
                // Si no se seleccionan categorías, limpiar la tabla
                Configuration::updateValue('IDNK_CSP_SELECTED_CAT', '');
                $db = Db::getInstance();
                $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'idnk_csp';
                $db->Execute($sql);
            }
        }
    }

    return $this->displayConfirmation($this->l('The settings have been updated.'));
}

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path.'views/js/back.js');
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
        $this->context->controller->addJS($this->_path.'views/js/spectrum.min.js');
        $this->context->controller->addCSS($this->_path.'views/css/spectrum.min.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookdisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

public function hookDisplayHome()
{
    $enabled = Configuration::get('IDNK_CSP_LIVE_MODE', true);
    if ($enabled == 1) {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        $this->context->smarty->assign([
            'categories' => Configuration::get('IDNK_CSP_SELECTED_CAT'),
            'megamenulink' => $this->context->link->getModuleLink('idnk_csp', 'display')
        ]);

        // Detectar el tema activo
        $theme = $this->context->shop->theme->get('name');

        // Seleccionar plantilla de bloques de categorías según el tema
        if ($theme == 'classic') {
            $template = 'catblocks_c.tpl';
            $product_template = 'catalog/_partials/miniatures/product.tpl'; // Plantilla de miniaturas en el tema classic
        } elseif ($theme == 'child_classic') {
            $template = 'catblocks_c.tpl';
            $product_template = 'catalog/_partials/miniatures/product.tpl'; // Plantilla de miniaturas en child_classic
        } elseif ($theme == 'hummingbird') {
            $template = 'catblocks_h.tpl';
            $product_template = 'catalog/_partials/miniatures/product.tpl'; // Plantilla de miniaturas en hummingbird
        } else {
            $template = 'cblocks.tpl'; // Cargar por defecto
            $product_template = 'catalog/_partials/miniatures/product.tpl'; // Plantilla por defecto
        }

        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'idnk_csp ORDER BY idnkcategory_id ASC';
        $results = $db->ExecuteS($sql);
        $id_lang = (int)$this->context->cookie->id_lang;
        $id_shop = (int)$this->context->shop->id;
        $catnum = 0;
        $category_array = [[]];
        foreach ($results as $row) {
            $category = new Category($row['idnkcategory_id']);
            $current_name = $category->getName((int)$id_lang);
            $category_array[$catnum]['category_name'] = $current_name;
            $category_array[$catnum]['category_color'] = $row['idnkcategory_color'];
            $category_array[$catnum]['category_shadow'] = $row['idnkcategory_shadow'];
            $category_array[$catnum]['category_txtsize'] = $row['idnkcategory_txtsize'];
            $category_array[$catnum]['category_image'] = Context::getContext()->shop->getBaseURL(true) . "modules/idnkcategoryshowproducts/views/img/" . $row['idnkcategory_img'];
            $category_array[$catnum]['category_children'] = Category::getChildren((int)$row['idnkcategory_id'], (int)$id_lang, true, (int)$id_shop);
            $category_array[$catnum]['category_product'] = $this->prepareBlocksProducts($category->getProducts((int)Context::getContext()->language->id, 1, 6, 'position'));
            $catnum++;
        }

        // Pasar la plantilla de miniaturas del tema activo
        $this->context->smarty->assign([
            'category' => $category_array,
            'product_template' => $product_template, // Asignar plantilla de miniaturas
        ]);

        // Retornar la plantilla seleccionada
        return $this->display(__FILE__, $template);
    }
    return false;
}

    public function prepareBlocksProducts($products)
    {
        $products_for_template = [];
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(new ImageRetriever($this->context->link), $this->context->link, new PriceFormatter(), new ProductColorsRetriever(), $this->context->getTranslator());
        $products_for_template = [];
        foreach ($products as $rawProduct) {
            $products_for_template[] = $presenter->present($presentationSettings, $assembler->assembleProduct($rawProduct), $this->context->language);
        }
        return $products_for_template;
    }
}
