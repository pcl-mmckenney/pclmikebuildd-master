<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform_Themes
{
    /**
     * @var array
     */
    protected $themes = array();

    /**
     * @var array
     */
    protected $coreThemes;

    public function __construct()
    {
        $this->coreThemes = array(
            'minimal' => array(
                'name' => 'Minimal',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.minimal.min.css',
                'cssUrl' => Quform::url('css/theme.minimal.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'light' => array(
                'name' => 'Quform Light',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.light.min.css',
                'cssUrl' => Quform::url('css/theme.light.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'dark' => array(
                'name' => 'Quform Dark',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.dark.min.css',
                'cssUrl' => Quform::url('css/theme.dark.min.css'),
                'previewColor' => '#0d0d0c'
            ),
            'hollow' => array(
                'name' => 'Hollow',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.hollow.min.css',
                'cssUrl' => Quform::url('css/theme.hollow.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'underlined' => array(
                'name' => 'Underline',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.underlined.min.css',
                'cssUrl' => Quform::url('css/theme.underlined.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'simple' => array(
                'name' => 'Simple',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.simple.min.css',
                'cssUrl' => Quform::url('css/theme.simple.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'react' => array(
                'name' => 'React',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.react.min.css',
                'cssUrl' => Quform::url('css/theme.react.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'bootstrap' => array(
                'name' => 'Bootstrap',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.bootstrap.min.css',
                'cssUrl' => Quform::url('css/theme.bootstrap.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'shine-gradient' => array(
                'name' => 'Shine Gradient',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.shine-gradient.min.css',
                'cssUrl' => Quform::url('css/theme.shine-gradient.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'blend-gradient' => array(
                'name' => 'Blend Gradient',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.blend-gradient.min.css',
                'cssUrl' => Quform::url('css/theme.blend-gradient.min.css'),
                'previewColor' => '#FFFFFF'
            ),
            'storm' => array(
                'name' => 'Storm',
                'version' => '1.0.0',
                'cssPath' => QUFORM_PATH . '/css/theme.storm.min.css',
                'cssUrl' => Quform::url('css/theme.storm.min.css'),
                'previewColor' => '#0d0d0c'
            )
        );
    }

    /**
     * @param  string  $key   Unique theme key
     * @param  array   $data  Theme data
     */
    public function register($key, array $data)
    {
        $this->themes[$key] = $data;
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * Get the theme with the given key
     *
     * @param   string      $key
     * @return  array|null
     */
    public function getTheme($key)
    {
        return isset($this->themes[$key]) ? $this->themes[$key] : null;
    }

    /**
     * Register the themes that are included with the plugin
     */
    public function registerCoreThemes()
    {
        foreach ($this->coreThemes as $key => $data) {
            $this->register($key, $data);
        }
    }

    /**
     * @param   string  $key
     * @return  bool
     */
    public function isCoreTheme($key)
    {
        return array_key_exists($key, $this->coreThemes);
    }
}
