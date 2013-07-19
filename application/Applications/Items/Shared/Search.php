<?php
class App_Items_Shared_Search extends App_Search_Shared_SearchAbstract
{
    public function search(array $cretiria = array())
    {
        $query = new App_Items_Admin_Queries();
        if(isset($cretiria['keyword']))
        {
            $cretiria['item_title'] = $cretiria['keyword'];
        }
        $cretiria['lang_id'] = App_Language_Shared_Lang::currentLang();
        $this->setMapper('item_title','title');
        $this->setMapper('item_content','content');

        return $query->itemQuery($cretiria);
    }
}