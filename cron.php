<?php
/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 26.11.2017
 * Time: 18:38
 */

if (!file_exists('../../init.php') || !file_exists('../../config/config.inc.php')) {
    die('An include file not found. Check the path.');
}

include('../../config/config.inc.php');
require_once('../../init.php');
require_once(_PS_MODULE_DIR_ . 'weeklydeals' . DIRECTORY_SEPARATOR . 'classes/WeeklyDeal.php');

const dateKey = "WeeklyDLS_CurrDealWeekNumber";

if(Configuration::get(dateKey) != date("W")) {
    $deal = WeeklyDeal::getActivatedDeal();
    $deal->deactivate();
    $deal->delete();

    WeeklyDeal::deactivateDeals();

    $nextDeal = WeeklyDeal::getFirstActive();
    $nextDeal->activate();

    Configuration::updateValue(dateKey, date("W"));

    echo json_encode([
        "success" => true
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Cron was already executed this week!"
    ]);
}
