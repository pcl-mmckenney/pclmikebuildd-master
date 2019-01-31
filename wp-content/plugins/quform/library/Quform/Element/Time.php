<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Time extends Quform_Element_Field implements Quform_Element_Editable
{
    /**
     * Get the value formatted in HTML
     *
     * @return  string
     */
    public function getValueHtml()
    {
        $value = Quform::escape($this->getValueText());

        $value = apply_filters('quform_get_value_html_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Get the value formatted in plain text
     *
     * @param   string  $separator
     * @return  string
     */
    public function getValueText($separator = ', ')
    {
        $value = $this->isEmpty() ? '' : date_i18n($this->config('timeFormat'), strtotime($this->getValue()));

        $value = apply_filters('quform_get_value_text_' . $this->getIdentifier(), $value, $this, $this->getForm());

        return $value;
    }

    /**
     * Is the given value valid for this element type
     *
     * @param   string  $value
     * @return  bool
     */
    protected function isValidValue($value)
    {
        if ( ! is_string($value)) {
            return false;
        }

        return preg_match('/^([0-9]{2}):([0-9]{2})$/', $value);
    }

    /**
     * Prepare the dynamic default value
     *
     * Takes a time string e.g. 1:12, if it's valid it will be converted into the format of the timepicker
     *
     * @param   string  $value
     * @return  string
     */
    public function prepareDynamicValue($value)
    {
        $parts = explode(':', $value);
        $value = '';

        if (isset($parts[0], $parts[1])) {
            $hour = (int) $parts[0];
            $minute = (int) $parts[1];

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                $value = date('H:i', strtotime("$hour:$minute"));
            }
        }

        return $value;
    }

    /**
     * Get the HTML attributes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldAttributes(array $context = array())
    {
        $attributes = array(
            'type' => 'text',
            'id' => $this->getUniqueId(),
            'name' => $this->getFullyQualifiedName(),
            'class' => Quform::sanitizeClass($this->getFieldClasses($context)),
            'placeholder' => 'HH:MM',
            'data-options' => wp_json_encode($this->getTimepickerOptions())
        );

        if ( ! $this->isEmpty()) {
            $attributes['value'] = $this->getValue();
        }

        if ($this->config('readOnly')) {
            $attributes['readonly'] = true;
        }

        $attributes = apply_filters('quform_field_attributes', $attributes, $this, $this->form, $context);
        $attributes = apply_filters('quform_field_attributes_' . $this->getIdentifier(), $attributes, $this, $this->form, $context);

        return $attributes;
    }

    /**
     * Get the timepicker options for the field
     *
     * @return array
     */
    protected function getTimepickerOptions()
    {
        $options = array(
            'min' => $this->config('timeMin'),
            'max' => $this->config('timeMax'),
            'interval' => $this->config('timeInterval'),
            'locale' => $this->config('timeLocale'),
            'format' => $this->config('timeFormatJs'),
            'placeholder' => $this->config('placeholder'),
            'autoOpen' => $this->config('timeAutoOpen'),
            'identifier' => $this->getIdentifier()
        );

        return $options;
    }

    /**
     * Get the classes for the field
     *
     * @param   array  $context
     * @return  array
     */
    protected function getFieldClasses(array $context = array())
    {
        $classes = array(
            'quform-field',
            'quform-field-time',
            sprintf('quform-field-%s', $this->getIdentifier())
        );

        if ($this->form->config('tooltipsEnabled') && Quform::isNonEmptyString($this->config('tooltip')) && Quform::get($context, 'tooltipType') == 'field') {
            $classes[] = sprintf('quform-tooltip-%s', Quform::get($context, 'tooltipEvent'));
        }

        if (Quform::isNonEmptyString($this->config('customClass'))) {
            $classes[] = $this->config('customClass');
        }

        $classes = apply_filters('quform_field_classes', $classes, $this, $this->form, $context);
        $classes = apply_filters('quform_field_classes_' . $this->getIdentifier(), $classes, $this, $this->form, $context);

        return $classes;
    }

    /**
     * Get the HTML for the field
     *
     * @param   array   $context
     * @return  string
     */
    protected function getFieldHtml(array $context = array())
    {
        return Quform::getHtmlTag('input', $this->getFieldAttributes($context));
    }

    /**
     * Get the field HTML when editing
     *
     * @return string
     */
    public function getEditFieldHtml()
    {
        return $this->getFieldHtml();
    }

    /**
     * Render the CSS for this element
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = parent::renderCss($context);

        if ($context['fieldWidth'] == 'custom' && Quform::isNonEmptyString($context['fieldWidthCustom'])) {
            $css .= sprintf('.quform-input-time.quform-input-%s { width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
            $css .= sprintf('.quform-inner-%s > .quform-error > .quform-error-inner { float: left; min-width: %s; }', $this->getIdentifier(), Quform::addCssUnit($context['fieldWidthCustom']));
        }

        return $css;
    }

    /**
     * Does the given logic rule match the given value?
     *
     * @param   mixed  $value
     * @param   array  $rule
     * @return  bool
     */
    protected function isLogicValueMatch($value, array $rule)
    {
        if ($rule['operator'] == 'gt') {
            return ! $this->isEmpty() && strtotime($value) > strtotime($rule['value']);
        } else if ($rule['operator'] == 'lt') {
            return ! $this->isEmpty() && strtotime($value) < strtotime($rule['value']);
        }

        return parent::isLogicValueMatch($value, $rule);
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_time', array(
            // Basic
            'label' => __('Time', 'quform'),
            'description' => '',
            'descriptionAbove' => '',
            'required' => false,

            // Styles
            'labelIcon' => '',
            'fieldIconLeft' => '',
            'fieldIconRight' => 'qicon-schedule',
            'fieldSize' => 'inherit',
            'fieldWidth' => 'inherit',
            'fieldWidthCustom' => '',
            'customClass' => '',
            'styles' => array(),

            // Labels
            'placeholder' => '',
            'subLabel' => '',
            'subLabelAbove' => '',
            'adminLabel' => '',
            'tooltip' => '',
            'tooltipType' => 'inherit',
            'tooltipEvent' => 'inherit',
            'labelPosition' => 'inherit',
            'labelWidth' => '',

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Data
            'defaultValue' => '',
            'dynamicDefaultValue' => false,
            'dynamicKey' => '',
            'timeMin' => '',
            'timeMax' => '',
            'timeInterval' => '',
            'timeLocale' => '',
            'timeFormatJs' => '',
            'timeFormat' => '',
            'timeAutoOpen' => true,
            'readOnly' => false,
            'showInEmail' => true,
            'saveToDatabase' => true,

            // Advanced
            'visibility' => '',
            'validators' => array(),

            // Translations
            'messageRequired' => '',
            'messageTimeInvalidTime' => '',
            'messageTimeTooEarly' => '',
            'messageTimeTooLate' => ''
        ));

        $config['type'] = 'time';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
