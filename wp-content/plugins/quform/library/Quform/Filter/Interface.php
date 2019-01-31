<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (http://www.themecatcher.net)
 */
interface Quform_Filter_Interface
{
    public function filter($value);
    public static function getDefaultConfig();
}
