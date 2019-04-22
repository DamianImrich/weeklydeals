<?php

/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 24.11.2017
 * Time: 18:33
 */

use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class WeeklyDeal extends ObjectModel
{
    public $id_weekly_deal;
    public $active;
    public $position;
    public $product_ids;
    public $specific_price_ids = null;
    public $discount;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'weekly_deals',
        'primary' => 'id_weekly_deal',
        'multilang' => false,
        'fields' => array(
            'active' => array('type' => self::TYPE_BOOL),
            'position' => array('type' => self::TYPE_INT),
            'product_ids' => array('type' => self::TYPE_STRING, 'required' => true),
            'specific_price_ids' => array('type' => self::TYPE_STRING),
            'discount' => array('type' => self::TYPE_INT, 'required' => true),
        ),
    );

    public static function getNextAvailablePosition()
    {
        $sql = 'SELECT position FROM '._DB_PREFIX_.self::$definition['table'].' ORDER BY position DESC';

        $position = (int)Db::getInstance()->getValue($sql, false);
        return $position + 1;
    }

    public function updatePosition($way, $position)
    {
        if ($this->id_weekly_deal) {
            $id = (int)$this->id_weekly_deal;
            $tblname = _DB_PREFIX_ . self::$definition['table'];

            if (!$res = Db::getInstance()->executeS('
            SELECT `'.self::$definition['primary'].'`, `position`
            FROM `' . $tblname . '`
            ORDER BY `position` ASC'
            )
            )
                return false;

            foreach ($res as $deals)
                if ((int)$deals[self::$definition['primary']] == (int)$id)
                    $moved_deals = $deals;

            if (!isset($moved_deals) || !isset($position))
                return false;


            return (Db::getInstance()->execute('
            UPDATE `' . $tblname . '`
            SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
            WHERE `position`
            ' . ($way
                        ? '> ' . (int)$moved_deals['position'] . ' AND `position` <= ' . (int)$position
                        : '< ' . (int)$moved_deals['position'] . ' AND `position` >= ' . (int)$position . '
            '))
                && Db::getInstance()->execute('
            UPDATE `' . $tblname . '`
            SET `position` = ' . (int)$position . '
            WHERE `'.self::$definition['primary'].'` = ' . (int)$moved_deals[self::$definition['primary']]));
        } else {
            return false;
        }
    }


    public function activate()
    {
        $exp = explode(",", $this->product_ids);
        $productIds = [];
        foreach($exp as $id){
            $productIds[] = trim($id);
        }
        $shopID = Context::getContext()->shop->id;
        $reduction = $this->discount/100;

        $specific_price_ids = [];
        foreach($productIds as $i => $productId){

            $sPrice = new SpecificPrice;
            $sPrice->id_product = $productId;
            $sPrice->id_shop = $shopID;
            $sPrice->id_shop_group = 0;
            $sPrice->id_specific_price_rule = 0;
            $sPrice->id_cart = 0;
            $sPrice->id_currency = 0;
            $sPrice->id_country = 0;
            $sPrice->id_group = 0;
            $sPrice->id_customer = 0;
            $sPrice->id_product_attribute = 0;
            $sPrice->from_quantity = 1;
            $sPrice->reduction = $reduction;
            $sPrice->price = -1;
            $sPrice->reduction_tax = 1;
            $sPrice->reduction_type = "percentage";

            $sPrice->from = (new DateTime('Monday this week'))->format('Y-m-d')." 00:00:00";
            $sPrice->to = (new DateTime('Monday this week + 7 days'))->format('Y-m-d')." 00:00:00";

            $idSale = explode(":", $productId);
            if(count($idSale) > 1){
                $sPrice->id_product = $idSale[0];
                $sPrice->reduction = floatval($idSale[1])/100;
            }

            if(!$sPrice->add())
                return false;
								

            $specific_price_ids[] = $sPrice->id;
        }
				
		$this->specific_price_ids = json_encode($specific_price_ids);

        if(!$this->update())
            return false;

        return true;
    }

    public function deactivate()
    {
        $specificPriceIds = json_decode($this->specific_price_ids);
				if($specificPriceIds){
					foreach($specificPriceIds as $specificPriceId){
							$sprice = new SpecificPrice($specificPriceId);
							if(!$sprice->delete())
									return false;
					}

					$this->specific_price_ids = null;

					if(!$this->update())
							return false;
				}

        return true;
    }

    public function getProducts()
    {
        $exp = explode(",", $this->product_ids);
        $productIds = [];
        foreach($exp as $id){
            $productIds[] = trim($id);
        }
        //die(var_dump($productIds));
        if(!$res = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_ .'product WHERE id_product IN ('.implode(",", $productIds).")"))
            return false;

        return (isset($res)) ? $res : null;
    }

    public static function deactivateDeals()
    {
        if(!$res = Db::getInstance()->executeS('SELECT id_weekly_deal, specific_price_ids, position FROM '._DB_PREFIX_ . self::$definition["table"].' ORDER BY position ASC'))
            return false;
				//die(var_dump($res));

        foreach($res as $deal){
            $weeklyDeal = new WeeklyDeal($deal["id_weekly_deal"]);
						if($weeklyDeal)
							$weeklyDeal->deactivate();
        }

        return true;
    }

    public static function getActivatedDeal()
    {
        if(!$res = Db::getInstance()->executeS('SELECT id_weekly_deal, specific_price_ids, position FROM '._DB_PREFIX_ . self::$definition["table"].' WHERE specific_price_ids IS NOT NULL and specific_price_ids != "" AND active = 1 ORDER BY position ASC LIMIT 1'))
            return false;

        return (isset($res[0])) ? new WeeklyDeal($res[0]["id_weekly_deal"]) : null;
    }

    public static function getFirstActive()
    {
        if(!$res = Db::getInstance()->executeS('SELECT id_weekly_deal, position FROM '._DB_PREFIX_ . self::$definition["table"].' WHERE active = 1 ORDER BY position ASC LIMIT 1'))
            return false;

        return (isset($res[0])) ? new WeeklyDeal($res[0]["id_weekly_deal"]) : null;
    }

    public static function refreshWeeklyDeal(){
				if(Configuration::get("WeeklyDLS_CurrDealWeekNumber") != date("W")) {
						$deal = WeeklyDeal::getActivatedDeal();
						if($deal){
							$deal->deactivate();
							$deal->delete();
						}

						WeeklyDeal::deactivateDeals();

						$nextDeal = WeeklyDeal::getFirstActive();
						$nextDeal->activate();

						Configuration::updateValue("WeeklyDLS_CurrDealWeekNumber", date("W"));
				} else {
					self::deactivateDeals();

					$firstDeal = self::getFirstActive();
				
					if($firstDeal)
						$firstDeal->activate();
				}
    }
}