<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Student extends Module
{
    protected $config_form   = false;

    protected $resultMessage = '';

    public function __construct()
    {
        $this->name          = 'student';
        $this->tab           = 'administration';
        $this->version       = '0.1.0';
        $this->author        = 'Bondrus';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('АдминКонтроллер в модуле с хелпером');
        $this->description = $this->l('АдминКонтроллер в модуле с хелпером');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'student` (
             `id_student` int(11) NOT NULL AUTO_INCREMENT,
             `name` varchar(255) NOT NULL,
             `birthday` date DEFAULT NULL,
             `is_study` tinyint(1) DEFAULT 1,
             `rate` float NOT NULL    
             PRIMARY KEY  (`id_student`)
             ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }

        return parent::install()
                && $this->registerHook('header')
                && $this->registerHook('backOfficeHeader')
                && $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        $sql = array();

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitStudentModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitStudentModule';
        $helper->currentIndex  = $this->context->link->getAdminLink(
                'AdminModules',
                false
            ) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token         = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings') . str_repeat('-', 50) . $this->resultMessage,
                ],
                'input'  => [
                    [
                        'col'   => 3,
                        'type'  => 'text',
                        'desc'  => $this->l('Enter name'),
                        'name'  => 'STUDENT_NAME',
                        'label' => $this->l('Name'),
                    ],
                    [
                        'type' => 'date',
                        'desc'  => $this->l('Enter birthday'),
                        'name' => 'STUDENT_BIRTHDAY',
                        'label' => $this->l('Birthday'),
                        'filter_key' => 'a!data_inicial',
                        'size' => 10,
                        'required' => true
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Study'),
                        'name'    => 'STUDENT_STUDY',
                        'is_bool' => true,
                        'desc'    => $this->l('Study or not?'),
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Study')
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Not study')
                            ]
                        ],
                    ],
                    [
                        'col'   => 3,
                        'type'  => 'text',
                        'desc'  => $this->l('Enter rate'),
                        'cast'  => 'strval',
                        'name'  => 'STUDENT_RATE',
                        'label' => $this->l('Rate'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Add student'),
                ],
            ],
        ];
    }

    /**
     * Set values for db.
     */
    protected function getConfigFormValues()
    {
        return [
            'STUDENT_NAME'     => Configuration::get('STUDENT_NAME', 'Ivan'),
            'STUDENT_BIRTHDAY' => Configuration::get('STUDENT_BIRTHDAY', '1978-01-01'),
            'STUDENT_STUDY'    => Configuration::get('STUDENT_STUDY', 1),
            'STUDENT_RATE'     => Configuration::get('STUDENT_RATE', 0),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $isValide    = true;
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        $form_values = $this->getConfigFormValues();
        try {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'student` (name, birthday, is_study, rate)
                    VALUES ("' . $form_values['STUDENT_NAME'] . '" , "' . $form_values['STUDENT_BIRTHDAY'] . '", ' . $form_values['STUDENT_STUDY'] . ',' . $form_values['STUDENT_RATE'] . ')'
            );
            $this->resultMessage = 'Студент добавлен';
        } catch (Exception $e) {
            $this->resultMessage = 'Ошибка добавления';
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayHome()
    {
        /* Place your code here. */
    }
}
