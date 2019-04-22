<?php

/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 27.11.2017
 * Time: 18:02
 */
class ProductController extends ProductControllerCore
{
    public function setProduct($p)
    {
        $this->product = $p;
    }
    function assignGeneralPurposeVariables()
    {
        $templateVars = array(
            'cart' => $this->cart_presenter->present($this->context->cart),
            'currency' => $this->getTemplateVarCurrency(),
            'customer' => $this->getTemplateVarCustomer(),
            'language' => $this->objectPresenter->present($this->context->language),
            'page' => $this->getTemplateVarPage(),
            'shop' => $this->getTemplateVarShop(),
            'urls' => $this->getTemplateVarUrls(),
            'configuration' => $this->getTemplateVarConfiguration(),
            'field_required' => $this->context->customer->validateFieldsRequiredDatabase(),
            'breadcrumb' => $this->getBreadcrumb(),
            'link' => $this->context->link,
            'time' => time(),
            'static_token' => Tools::getToken(false),
            'token' => Tools::getToken(),
            'productDiscountRules' => $this->getTemplateVarProductDiscountRules()
        );

        $this->context->smarty->assign($templateVars);

        $js = ['prestashop' => $templateVars];

        $product = $this->getTemplateVarProduct();
        $js['prestashop']['product'] = $product;

        Media::addJsDef($js);
    }

    public function getTemplateVarProductDiscountRules()
    {
        $rules = [];
        $product = $this->getTemplateVarProduct();
        foreach(explode("qDiscountRuleID=", Hook::exec("displayQuantityDiscountProCustom1", ['product' => $product])) as $part){
            $id = intval(explode("}", $part)[0]);
            if($id != 0){
                $qdr = new QuantityDiscountRule($id);
                $action = $qdr->getActions()[0];

                $item = [];
                $item['products_nb_each'] = (int)$action['products_nb_each'];
                $item['name'] = $qdr->name[1];

                switch($action["id_type"]){
                    case 33:
                        $item["reducedPrice"] = number_format(($product['price_amount']/100)*(100-$action['reduction_percent']), 2, '.', ',');
                        break;
                    case 34:
                        $item["reducedPrice"] = number_format($action['reduction_amount'], 2, '.', ',');
                        break;
                    case 32:
                        $item["reducedPrice"] = number_format($product['price_amount']-$action['reduction_amount'], 2, '.', ',');
                        break;
                }

                $rules[] = $item;
            }
        }
        return array_reverse($rules);
    }
}