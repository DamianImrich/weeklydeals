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
}