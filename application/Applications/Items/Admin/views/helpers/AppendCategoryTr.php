<?php

class App_Items_Admin_View_Helper_AppendCategoryTr {

    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }
    public function AppendCategoryTr($categories,$level = 0) {

        $categoriesTr = '';
        foreach($categories as $category)
        {
            if($category['parent_id'] == 0)
            {
                $level = 0;
            }
            $category['tr_level'] = $level;
            $categoriesTr.=$this->tr($category,$level);

            if(isset($category['childs']) && is_array($category['childs']))
            {
                $level++;
                $categoriesTr.=$this->AppendCategoryTr($category['childs'],$level);
            }
        }

        return $categoriesTr;
    }



    protected function tr($category,$level)
    {
        $levels = '';
        if($level!=0)
        {
            for($i = 0 ;$i<=$level;$i++)
            {
                $levels.="&nbsp;&nbsp;&nbsp;";
            }
            $levels = $levels.'<img src="contents/admin/themes/default/_layout/images/subcat-arrow.png"/>&nbsp;';
        }
        $tr = '<tr>';
        $tr.='<td><input type="checkbox" class="category" name="cat_id[]" value="'. $category['cat_id'].'"></td>';
        $tr.= '<td><a href="'.$this->MC->application['url'].'window/items/cat_id/'.$category['cat_id'].'">'.$levels.$category['cat_name'].'</a></td>';

        $tr.="<td align='center'>".$this->MC->model->lang->translate('item_cat_status_'.$category['cat_status'])."</td>";
        $tr.="<td align='center'>

        <a href='".$this->MC->application['url']."window/category/do/edit/cat_id/".$category['cat_id']."' title='".$this->MC->model->lang->translate('edit')."' class='icon-25x25 edit'></a>
        <a href='".$this->MC->application['url']."window/deleteCat/catId/".$category['cat_id']."/folderId/".$category['folder_id']."' title='".$this->MC->model->lang->translate('edit')."' class='icon-25x25 delete disState' data-modal-type='confirm' data-modal-msg ='".$this->MC->model->lang->translate('do_you_want_delete_category')."'></a>

        </td>";
        $tr.="</tr>";
        return $tr;
    }
    
}