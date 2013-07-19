<?php

class MC_Models_Html_Link
{

    public function __construct($text, $href = '',  $attributes = array())
    {
        

        if (!empty($href))
        {
            $attributes['href'] = $href;
        }

        $attr = array();

        foreach ($attributes as $key => $val)
        {
            $attr[] = $key . '="' . $val . '"';
        }

        $newAttr = implode(' ', $attr);

        echo "<a " . $newAttr . ">" . $text . "</a>";

    }


}