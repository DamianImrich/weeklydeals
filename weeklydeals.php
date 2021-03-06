<?php

/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 6.3.2019
 * Time: 13:24
 */

require_once(_PS_MODULE_DIR_ . 'weeklydeals' . DIRECTORY_SEPARATOR . 'classes/WeeklyDeal.php');

use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class WeeklyDeals extends Module
{
    public function __construct()
    {

        $this->name = 'weeklydeals';
        $this->tab = 'front_office_features';
        $this->version = '0.1';
        $this->author = 'Damian Imrich';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Weekly Deals');
        $this->description = $this->l('Deal for every week.');

        $this->hooks = [
			'displayHeader',
            'moduleRoutes',
            'displayWeeklyDealBlock',
        ];

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall weekly deals module?');
        //$this->installDB();

        if(false){
            foreach($this->hooks as $hook){
                if (!$this->isRegisteredInHook($hook)){
                    $this->registerHook($hook);
                }
            }
        }

        //$this->addTabs();
    }

    public function install()
    {
        if(!parent::install())
            return false;

        $this->addTab();
        $this->installDB();

        foreach($this->hooks as $hook){
            if(!$this->registerHook($hook))
                return false;
        }


        Configuration::set("WeeklyDLS_INSTALLED", true);

        return true;
    }

    public function installDB()
    {
        $a = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'weeklydeals_subscribers` (
			`id_dd_subscriber` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`email` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
            `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id_dd_subscriber`)
		) DEFAULT CHARSET=utf8;');

        $b = Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.'weekly_deals` (
			`id_weekly_deal` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`active` int(11) NOT NULL,
            `position` int(11) NOT NULL,
            `product_ids` VARCHAR(255) NOT NULL,
            `specific_price_ids` VARCHAR(255) DEFAULT NULL,
            `discount` int(11) NOT NULL,
			PRIMARY KEY (`id_weekly_deal`)
		) DEFAULT CHARSET=utf8;');

        return $a && $b;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('WeeklyDLS_INSTALLED')
        )
            return false;

        return true;
    }

    protected function addTab()
    {
        $name = "Weekly deals";

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminWeekDeals";
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = isset($this->lang[$lang['iso_code']]) ? $this->lang[$lang['iso_code']][$name] : $name;
        }
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT');
        $tab->add();

    }
    public function hookDisplayHeader($params)
    {
        $_controller = $this->context->controller;
        if ((isset($_controller->php_self) && $_controller->php_self == 'index') || $_controller->page_name == "module-weeklydeals-weeklydeal") {
            $this->context->controller->addCSS($this->_path.'css/weeklydeals.css');
            WeeklyDeals::assignSmartyVariables($this->context);
        }
    }
    public function hookModuleRoutes()
    {
        return array(
            'module-weekly-deals' => array(
                'controller' => 'weeklydeal',
                'rule' =>  $this->l('weekly-deal'),
                'keywords' => [],
                'params' => array(
                    'fc' => 'module',
                    'module' => 'weeklydeals',
                )
            )
        );
    }

    public static function assignSmartyVariables($ctx)
    {
        if(!$deal = WeeklyDeal::getActivatedDeal()){
            return false;
        }


        $last_date = strtotime("+ 7 days",strtotime("Monday this week"));
				
				$rem = $last_date - time();
				$day = floor($rem / 86400);
				$hr  = floor(($rem % 86400) / 3600);
				$min = floor(($rem % 3600) / 60);
				$sec = ($rem % 60);	
     

        $assembler = new ProductAssembler(Context::getContext());
        $presenterFactory = new ProductPresenterFactory(Context::getContext());
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                Context::getContext()->link
            ),
            Context::getContext()->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            Context::getContext()->getTranslator()
        );

        if(!$products = $deal->getProducts())
            return false;

        $presentedProducts = [];
        foreach($products as $product){
            $p = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($product),
                Context::getContext()->language
            );
            $p['quantity_wanted'] = 1;

            $presentedProducts[]=$p;
        }


        Media::addJsDef([
            "weeklydeal" => [
                'presentedProducts' => $presentedProducts,
                'currency' => ['sign' => $ctx->currency->sign]
            ]
        ]);

        global $smarty;
        $smarty->assign("countdown", join(":", [$day,$hr, $min,$sec]));
        $smarty->assign("presentedProducts", $presentedProducts);
        return true;
    }

   public function hookDisplayWeeklyDealBlock(){
       return $this->fetch('module:weeklydeals/views/templates/hook/displayWeeklyDealBlock.tpl');
   }


}
