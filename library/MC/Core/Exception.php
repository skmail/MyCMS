<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mac
 * Date: 6/29/13
 * Time: 2:00 AM
 * To change this template use File | Settings | File Templates.
 */

class MC_Core_Exception extends Zend_Exception {


    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($msg,$code,$previous);
    }

}
