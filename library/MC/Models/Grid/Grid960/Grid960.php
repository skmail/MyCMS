<?php

class MC_Models_Grid_Grid960_Grid960
{

    protected $_grids;

    public function __construct()
    {

        $this->init();

    }

    public function init()
    {

        $simpleXml = simplexml_load_file(LIBRARY_PATH . '/MC/Models/Grid/Grid960/grid.xml');

        $gridsArray = array();

        foreach ($simpleXml->grid as $grid)
        {
            $gridsArray["$grid->class"] = $grid;
        }

        $this->_grids = $gridsArray;

    }

    public function getGrids()
    {

        return $this->_grids;

    }

    public function getGrid($grid_class)
    {

        return $this->_grids["$grid_class"];

    }

}