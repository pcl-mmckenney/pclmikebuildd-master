<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Element_Html extends Quform_Element
{
    /**
     * Render this element and return the HTML
     *
     * @param   array   $context
     * @return  string
     */
    public function render(array $context = array())
    {
        $output = '';

        if ($this->isVisible()) {
            $output .= sprintf('<div class="quform-element quform-element-html quform-element-%s quform-cf">', $this->getIdentifier());
            $output .= sprintf('<div class="quform-spacer">%s</div>', $this->getContent());
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML content
     *
     * @param   string  $format  The format, 'html' (default) or 'text' for plain text
     * @return  string
     */
    public function getContent($format = 'html')
    {
        if ($format == 'text' && Quform::isNonEmptyString($this->config('plainTextContent'))) {
            $content = $this->config('plainTextContent');
        } else {
            $content = $this->config('content');

            if ($this->config('autoFormat')) {
                $content = nl2br($content);
            }
        }

        $content = $this->form->replaceVariablesPreProcess($content);

        $content = do_shortcode($content);

        return $content;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getContent() === '';
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array(
            'element' => '%s .quform-element-%s',
            'elementSpacer' => '%s .quform-element-%s > .quform-spacer'
        );
    }

    /**
     * Get the default element configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_html', array(
            // Basic
            'label' => __('HTML', 'quform'),
            'content' => '',
            'autoFormat' => false,

            // Styles
            'styles' => array(),

            // Logic
            'logicEnabled' => false,
            'logicAction' => true,
            'logicMatch' => 'all',
            'logicRules' => array(),

            // Data
            'showInEmail' => false,
            'plainTextContent' => '',
            'showInEntry' => false,

            // Advanced
            'visibility' => ''
        ));

        $config['type'] = 'html';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
