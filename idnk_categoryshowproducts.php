<?php

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Idnk_Categoryshowproducts extends Module
{
    protected $config_form = false;



    public function __construct()
    {
        $this->name = 'idnk_categoryshowproducts';
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
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        if (!$this->isRegisteredInHook('displayHome')) {
            $this->registerHook('displayHome');
        }

    }
    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
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
            $catnum = 0;
            $category_array = array(array());
            foreach ($selected as $cat) {
                $category = new Category($cat);
                $current_name = implode(',', $category->name);
                $category_array[$catnum]['category_name'] = $current_name;
                $catnum++;
            }
            $this->context->smarty->assign(array(
                'category' => $category_array,
            ));
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
        if (((bool)Tools::isSubmit('submitIdnk_categoryshowproductsModule')) == true) {
            $this->postProcess();
        }
        $this->processConfiguration();
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $message = "";
        if ((bool)Tools::getValue('cb_edited') == true) {
            $message = $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
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
        $helper->submit_action = 'submitIdnk_categoryshowproductsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        $output .= $helper->generateForm(array($this->getConfigForm()));
        return $output;
    }

    public function renderList()
    {
        // Select all available extra info tabs
        $db = Db::getInstance();
        $sql='SELECT * FROM ' . _DB_PREFIX_ . 'idnk_csp ORDER BY idnk_category_id ASC';
        $results=$db->ExecuteS($sql);
        $index = 0;
        foreach ($results as $row) {
            $category = new Category($row['idnk_category_id']);
            $results[$index]['idnk_category_name'] = $category->name[1];
            $index++;
        }
        if ($results != null) {
            $fields_list = array(
                'id_idnk_csp' => array(
                    'title' => 'ID',
                    'width' => '100',
                    'type' => 'text',
                    'visible' => 'false'
                ),
                'idnk_category_name' => array(
                    'title' => 'Category name',
                    'width' => 'auto',
                    'type' => 'text'
                ),
                'idnk_category_id' => array(
                    'title' => 'Category ID',
                    'width' => 'auto',
                    'type' => 'text'
                ),
                'idnk_category_img' => array(
                    'title' => 'Image',
                    'width' => 'auto',
                    'type' => 'text',
                    'prefix' => '<img class="image-box" src="../modules/idnk_categoryshowproducts/views/img/',
                    'suffix' => '" alt="Category image" />',
                ),
                'idnk_category_color' => array(
                    'title' => 'Color',
                    'width' => 'auto',
                    'type' => 'text',
                    'maxlength' => 7,
                    'prefix' => '<div class="color-box" style="background-color:',
                    'suffix' => '"></div>',
                ),
            );
            $helper_list = new HelperList();
            $helper_list->module = $this;
            $helper_list->shopLinkType = '';
            $helper_list->no_link = true;
            $helper_list->simple_header = true;
            $helper_list->identifier = 'id_idnk_csp';
            $helper_list->actions = array('editCategory');
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

    public function displayEditCategoryLink($id, $name = null, $token = null)
    {
        $this->smarty->assign(array(
            'href' => 'index.php?controller=AdminCatblocks&id_category=' . (int) $id . '&updatecatblocks&token=' . Tools::getAdminTokenLite('AdminCatblocks'),
            'action' => $this->trans('Configure', array(), 'Admin.Actions'),
            'disable' => !((int) $id > 0),
        ));
        return $this->display(__FILE__, 'views/templates/admin/catblocks/editcategory.tpl');
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'configuration WHERE name = "IDNK_CSP_SELECTED_CAT"';
        $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        // Verifica si se encontró la configuración
        if ($value && isset($value['value'])) {
            $selected = explode(',', $value['value']);
        } else {
            $selected = array(); // O cualquier otro valor predeterminado que desees asignar
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

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'IDNK_CSP_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable this module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'categories_select',
                        'label' => $this->l('Categories'),
                        'desc' => $this->l('Select categories which will be displayed in home page. Save the selection for categories to appear in the list.'),
                        'name' => 'IDNK_CSP_SELECTED_CAT',
                        'category_tree' => $categoryTreeCol1
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $categories = explode(",", Configuration::get('IDNK_CSP_SELECTED_CAT'));
        return array(
            'IDNK_CSP_LIVE_MODE' => Configuration::get('IDNK_CSP_LIVE_MODE', true),
            'IDNK_CSP_SELECTED_CAT' => $categories,
        );
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
                    if (count(Tools::getValue($key))>1) {
                        $categories = implode(",", Tools::getValue($key));
                    } else {
                        $categories = Tools::getValue($key)[0].",";
                    }
                    Configuration::updateValue('IDNK_CSP_SELECTED_CAT', $categories);
                    $db = Db::getInstance();
                    $sql='SELECT idnk_category_id FROM ' . _DB_PREFIX_ . 'idnk_csp';
                    $results=$db->ExecuteS($sql);
                    $db_categories = [];
                    foreach ($results as $res) {
                        foreach ($res as $sel) {
                            array_push($db_categories, $sel);
                        }
                    }
                    $toDelete = array_diff($db_categories, Tools::getValue($key));
                    $toCreate = array_diff(Tools::getValue($key), $db_categories);
                    if (count($toDelete)>0) {
                        $q_delete_ids = implode(', ', array_map('intval', $toDelete));
                        $sql='DELETE FROM '. _DB_PREFIX_ . 'idnk_csp WHERE idnk_category_id IN ('.$q_delete_ids.')';
                        $db->Execute($sql);
                    }
                    $_sql = array();
                    foreach ($toCreate as $row) {
                        $_sql[] = '('.$db->escape($row).', "default.jpg", "#333333")';
                    }
                    if (count($toCreate)>0) {
                        $sql='INSERT INTO '. _DB_PREFIX_ . 'idnk_csp 
                        (idnk_category_id, idnk_category_img, idnk_category_color)
                        VALUES
                        ' .implode(',', $_sql);
                        $db->Execute($sql);
                    }
                } else {
                    Configuration::updateValue('IDNK_CSP_SELECTED_CAT', '');
                    $db = Db::getInstance();
                    $sql='DELETE FROM '. _DB_PREFIX_ . 'idnk_csp';
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
            $db = Db::getInstance();
            $sql='SELECT * FROM ' . _DB_PREFIX_ . 'idnk_csp ORDER BY idnk_category_id ASC';
            $results=$db->ExecuteS($sql);
            $id_lang = (int)$this->context->cookie->id_lang;
            $id_shop = (int)$this->context->shop->id;
            $catnum = 0;
            $category_array = array(array());
            foreach ($results as $row) {
                $category = new Category($row['idnk_category_id']);
                $current_name = implode(',', $category->name);
                $category_array[$catnum]['category_name'] = $current_name;
                $category_array[$catnum]['category_color'] = $row['idnk_category_color'];
                $category_array[$catnum]['category_image'] = Context::getContext()->shop->getBaseURL(true) . "modules/idnk_categoryshowproducts/views/img/" . $row['idnk_category_img'];
                $category_array[$catnum]['category_children'] = Category::getChildren((int)$row['idnk_category_id'], (int)$id_lang, true, (int)$id_shop);
                $category_array[$catnum]['category_product'] = $this->prepareBlocksProducts($category->getProducts((int)Context::getContext()->language->id, 1, 6, 'position'));
                $catnum++;
            }
            $theme = $this->context->shop->theme->getName();
            $this->context->smarty->assign('theme_name', $theme);
            $this->context->smarty->assign(array(
                'category' => $category_array,
            ));

            if ($theme === 'classic') {
                return $this->display(__FILE__, 'catblocks_c.tpl');
                } else if ($theme === 'hummingbird') {
                    return $this->display(__FILE__, 'catblocks_h.tpl');
                } else {
                    return $this->display(__FILE__, 'catblocks_no.tpl');
                }
            
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
