<?php

class Custom_Forms_Text extends Zend_Form_Element_Text
{
    protected $_postValidateFilters = array();

    public function setPostValidateFilters(array $filters)
    {
        $this->_postValidateFilters = $filters;
        return $this;
    }

    public function getPostValidateFilters()
    {
        return $this->_postValidateFilters;
    }

    public function isValid($value, $context = null)
    {
        $isValid = parent::isValid($value, $context);
        if ($isValid){
            foreach ($this->getPostValidateFilters() as $filter){
                $value = $filter->filter($value);
            }
            $this->setValue($value);
        }
        return $isValid;
    }
}