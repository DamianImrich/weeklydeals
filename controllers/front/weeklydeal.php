<?php

class weeklydealsWeeklydealModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        $this->page_name = 'module-weeklydeals-weeklydeal'; // page_name and body id
        $this->display_column_left = false; // hides left column
        $this->display_column_right = false; // hides left column
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate("module:weeklydeals/views/templates/front/weeklyDeal.tpl");
    }
}