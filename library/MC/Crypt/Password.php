<?php
class MC_Crypt_Password{

    protected $_salt = '_!@3%s$pAsSMyCMS';

    public function create($password)
    {
        return md5($password);
        return md5(sprintf($this->_salt,$password));
    }
}