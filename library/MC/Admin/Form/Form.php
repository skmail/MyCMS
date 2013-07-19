<?php

class MC_Admin_Form_Form extends Zend_Form_SubForm{

    /** @var array Decorators to use for standard form elements */
    // these will be applied to our text, password, select, checkbox and radio elements by default
    public static $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array('Description', array('tag' => 'p', 'class' => 'description')),
        array('HtmlTag', array('class' => 'form-div')),
        array('Label', array('class' => 'form-label', 'requiredSuffix' => '*')),
        array(array('elementDiv' => 'HtmlTag'),
        array('tag' => 'div', 'class' => 'form-element'))
    );

    /** @var array Decorators for File input elements */
    // these will be used for file elements
    public $fileDecorators = array(
        'File',
        'Errors',
        array('Description', array('tag' => 'p', 'class' => 'description')),
        array('HtmlTag', array('class' => 'form-div')),
        array('Label', array('class' => 'form-label', 'requiredSuffix' => '*')),
        array(array('elementDiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-element'))
    );

    /** @var array Decorator to use for standard for elements except do not wrap in HtmlTag */
    // this array gets set up in the constructor 
    // this can be used if you do not want an element wrapped in a div tag at all
    public $elementDecoratorsNoTag = array();

    /** @var array Decorators for button and submit elements */
    // decorators that will be used for submit and button elements
    public $buttonDecorators = array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'span'))
    );

    protected $_hiddenElementDecorator = array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'hidden clear'))
    );

    public function __construct($options = array()) {
         // first set up the $elementDecoratorsNoTag decorator, this is a copy of our regular element decorators, but do not get wrapped in a div tag
        foreach (self::$elementDecorators as $decorator) {
            if (is_array($decorator) && $decorator[0] == 'HtmlTag') {
                continue; // skip copying this value to the decorator
            }
            $this->elementDecoratorsNoTag[] = $decorator;
        }
        // set the default decorators to our element decorators, any elements added to the form
        // will use these decorators
        $this->setElementDecorators(self::$elementDecorators);
        parent::__construct($options);
    }

    public function loadDefaultDecorators() {
        foreach ($this->getElements() as $element) {
            if ($element->getType() === "Zend_Form_Element_Hidden") {
                  $element->setDecorators($this->_hiddenElementDecorator);
            }
            if ($element->getType() == "Zend_Form_Element_Submit" || $element->getType() === "Zend_Form_Element_Button") {
                $element->setAttrib('class', 'btn btn-larg');
                $element->setAttrib('data-loading-text', 'Please Wait');
                $element->setDecorators($this->buttonDecorators);

                $element->removeDecorator('Label');
            }
        }




        $this->setDisplayGroupDecorators(array(
                             'FormElements',
                             array('Fieldset',array('class'=>'display-group'))
                            ));
        parent::loadDefaultDecorators();
    }


    public function tabContentDeco($options)
    {
        return array('FormElements',
            array('HtmlTag', array('tag' => 'div'))
        , array('Tab_Content', array('placement' => 'prepend', 'options'   => $options))
        );
    }

    public function tabDeco($tabs)
    {
        return
            array('FormElements',
                   array('HtmlTag', array('tag' => 'div')
            ),
            array('Tab_Tab', array('placement' => 'prepend', 'nav' => $tabs)));
    }
}