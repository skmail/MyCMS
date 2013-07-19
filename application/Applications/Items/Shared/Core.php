<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mac
 * Date: 5/20/13
 * Time: 9:43 PM
 * To change this template use File | Settings | File Templates.
 */

class App_Items_Shared_Core {

    public static function itemStatus()
    {
        $statusArray = array(
            1 => 'item_status_1',//Published
            2 => 'item_status_2',   //Drafts
            3 => 'item_status_3',    //Trash
            4 => 'item_status_4', // Pending
        );
        return $statusArray;
    }

}