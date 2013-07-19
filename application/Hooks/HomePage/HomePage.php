<?php

class Hooks_HomePage_HomePage extends  App_Hooks_Shared_HooksAbstract
{

    public function admin_homepage_blocks($data)
    {
        $data[] = $this->viewRender('homepageBlock.phtml');
    }

}