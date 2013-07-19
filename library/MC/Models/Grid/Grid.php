<?php

class MC_Models_Grid_Grid {


    public function getGrids()
    {

        $gridSystem = new MC_Models_Grid_Grid960_Grid960();

        return $gridSystem->getGrids();

    }

}