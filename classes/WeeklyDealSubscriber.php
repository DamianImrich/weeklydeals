<?php

/**
 * Created by Damián Imrich / HAZE s.r.o.
 * Date: 24.11.2017
 * Time: 18:33
 */
class WeeklyDealSubscriber
{

    public $id_dd_subscriber;
    public $email;
    public $subscribed_at;

    public static $definition = array(
        'table' => 'weeklydeals_subscribers',
        'primary' => 'id_dd_subscriber',
        'multilang' => false,
        'fields' => array(
            'email' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 255),
            'subscribed_at' => array('type' =>  self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
}