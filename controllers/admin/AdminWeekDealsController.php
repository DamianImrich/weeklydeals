<?php

/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 23.11.2017
 * Time: 20:16
 */

defined('_PS_VERSION_') or exit;
require_once(_PS_MODULE_DIR_ . 'weeklydeals' . DIRECTORY_SEPARATOR . 'classes/WeeklyDeal.php');

class AdminWeekDealsController extends ModuleAdminController
{
    public function __construct()
    {
        die(WeeklyDeal::$definition["table"]);
        $this->bootstrap = true;
        $this->table = WeeklyDeal::$definition["table"];
        $this->className = WeeklyDeal::class;
        $this->identifier = WeeklyDeal::$definition["primary"];
        $this->position_identifier = $this->identifier;
        $this->lang = false;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->_defaultOrderBy = 'position';
        $this->allow_export = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->fields_list = array(
            'id_weekly_deal' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'product_ids' => array(
                'title' =>  $this->l('Product IDs'),
            ),
            'discount' => array(
                'title' => $this->l('Discount ( % )'),
            ),
            'position' => array(
                'title' => $this->trans('Position', array(), 'Admin.Global'),
                'filter_key' => 'a!position',
                'position' => 'position',
                'class' => 'fixed-width-sm',
                'align' => 'center'
            ),
            'active' => array(
                'title' => $this->trans('Active', array(), 'Admin.Global'),
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'ajax' => true,
                'orderby' => false
            )
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected', array(), 'Admin.Actions'),
                'icon' => 'icon-trash',
                'confirm' => $this->trans('Delete selected items?', array(), 'Admin.Notifications.Warning')
            )
        );
        $this->specificConfirmDelete = false;

        if(isset($_GET["refresh_weekly_deal"])){
            WeeklyDeal::refreshWeeklyDeal();
            $this->confirmations[] = $this->l("Weekly deal was refreshed.");
        }
    }

    public function delete()
    {
        $position = $this->position;

        if ($result = parent::delete()) {
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . self::$definition['table'] . ' SET position = position-1 WHERE position > ' . (int)$position);
        }

        return $result;
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_weekly_deal'] = array(
                'href' => self::$currentIndex . '&addweekly_deals&token=' . $this->token,
                'desc' => $this->l('Add deal'),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['refresh_today_deal'] = array(
                'href' => self::$currentIndex . '&refresh_weekly_deal&token=' . $this->token,
                'desc' => $this->l('Refresh today\'s deal'),
                'icon' => 'process-icon-refresh'
            );
        }

        parent::initPageHeaderToolbar();
    }


    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Weekly deals'),
                'icon' => 'icon-bullseye'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'ps_version'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product IDs'),
                    'name' => 'product_ids',
                    'maxlength' => 255,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Discount ( % )'),
                    'name' => 'discount',
                    'required' => true,
                    'maxlength' => 2,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Status', array(), 'Admin.Global'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Admin.Global')
                        )
                    ),
                    'hint' => $this->trans('Zobraziť na webe.')
                )
            )
        );


        $this->fields_form['submit'] = array(
            'title' => $this->trans('Save', array(), 'Admin.Actions'),
        );
        $this->fields_form['submit-and-stay'] = array(
            'title' => $this->trans('Save', array(), 'Admin.Actions'),
            'name' => 'submitAdd'.$this->table.'AndStay',
            'type' => 'submit',
            'class' => 'btn btn-default pull-right',
            'icon' => 'process-icon-save',
        );

        $this->fields_value = array('ps_version' => _PS_VERSION_);

        return parent::renderForm();
    }


    public function ajaxProcessStatusWeeklyDeals()
    {
        if (!$id_weekly_deal = (int)Tools::getValue('id_weekly_deal')) {
            die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Admin.Notifications.Error'))));
        } else {
            $weeklydeal = new WeeklyDeal((int)$id_weekly_deal);
            if (Validate::isLoadedObject($weeklydeal)) {
                $weeklydeal->active = $weeklydeal->active == 1 ? 0 : 1;
                $weeklydeal->save() ?
                    die(json_encode(array('success' => true, 'text' => $this->trans('The status has been updated successfully', array(), 'Admin.Notifications.Success')))) :
                    die(json_encode(array('success' => false, 'error' => true, 'text' => $this->trans('Failed to update the status', array(), 'Admin.Notifications.Success'))));
            }
        }
    }

    public function beforeAdd($object)
    {
        $object->position = WeeklyDeal::getNextAvailablePosition();
        return true;
    }

  /*  public function processSave()
    {
        $exp = explode(",", $_POST["product_ids"]);
        $ids = [];
        foreach($exp as $id){
            $ids[] = trim($id);
        }
        $_POST["product_ids"] = json_encode($ids);
        return $this->processSave();
    }*/

    public function ajaxProcessUpdatePositions()
    {
        $way = (int)(Tools::getValue('way'));
        $id_deal = (int)(Tools::getValue('id'));
        $positions = Tools::getValue('weekly_deal');

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int)$pos[2] === $id_deal) {
                if ($deal = new WeeklyDeal((int)$pos[2])) {
                    if (isset($position) && $deal->updatePosition($way, $position)) {
                        echo 'ok position '.(int)$position.' for deal '.(int)$pos[2].'\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update deal '.(int)$id_deal.' to position '.(int)$position.' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This deal ('.(int)$id_deal.') can t be loaded"}';
                }

                break;
            }
        }
    }

}