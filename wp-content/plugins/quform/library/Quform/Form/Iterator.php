<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Form_Iterator extends RecursiveArrayIterator implements RecursiveIterator
{
    public function __construct(Quform_Form $form)
    {
        parent::__construct($form->getPages());
    }

    public function hasChildren()
    {
        return $this->current() instanceof Quform_Element_Container;
    }

    public function getChildren()
    {
        return new Quform_Element_Container_Iterator($this->current());
    }
}