<?php
class App_Widgets_Shared_Libraries_Grids
{
    public function __construct()
    {
        $this->MC =& MC_Core_Instance::getInstance();
    }

    public function getGrids($gridName)
    {
        $simpleXml  = $this->gridDom($gridName);
        $gridsArray = array();
        foreach($simpleXml as $gridRecord)
        {
            foreach ($gridRecord->grid as $grid)
            {
                $gridsArray["$grid->class"] = $grid;
            }
        }
        return $gridsArray;
    }

    public function gridsList()
    {
        $gridsPath = rtrim(PUBLIC_PATH,'/').'/Grids';
        $gridsDir = new MC_File_Iterator_Directory($gridsPath);
        $grids = array();

        foreach($gridsDir as $gridFile)
        {
            if($gridFile->isDot() || !$gridFile->isDir()){
                continue;
            }
            if(file_exists($gridFile->getPathname().'/grid.xml')){
                $grids[] = (string) $gridFile;
            }
        }
        return $grids;
    }

    public function gridDom($gridName)
    {
        $xmlFileContent = file_get_contents(rtrim(PUBLIC_PATH,'/') . '/Grids/'.$gridName.'/grid.xml');
        $vars = array();
        $vars['PUBLIC_PATH'] = PUBLIC_PATH;
        foreach($vars as $varName=>$varValue)
        {
            $xmlFileContent = str_replace('{'.$varName.'}', $varValue, $xmlFileContent);
        }
        $simpleXml = simplexml_load_string($xmlFileContent);
        return $simpleXml;
    }
}