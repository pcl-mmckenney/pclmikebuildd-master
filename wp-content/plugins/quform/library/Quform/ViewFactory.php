<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_ViewFactory
{
    public function create($template, array $data = array())
    {
        return new Quform_View($template, $data);
    }
}
