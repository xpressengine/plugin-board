<?php
namespace Xpressengine\Plugins\Board;

use HTMLPurifier;
use HTMLPurifier_Config;

class Purifier
{
    /**
     * @var HTMLPurifier_Config
     */
    protected $config;

    public $default = [

    ];

    public function __construct()
    {
        $this->config = HTMLPurifier_Config::createDefault();

        $this->config->loadArray([
            'Core.Encoding' => 'UTF-8',
            //'Cache.SerializerPath' => storage_path('framework/htmlpurifier'),
            'HTML.Doctype'             => 'XHTML 1.0 Strict',
            'HTML.Allowed'             => 'div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style|class|data-download-link|contenteditable],img[width|height|alt|src],table[summary],tbody,th[abbr],tr,td[abbr]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true
        ]);

        $def = $this->config->getHTMLDefinition(true);
        $def->addAttribute('span', 'data-download-link', 'Text');
        $def->addAttribute('span', 'contenteditable', 'Text');
    }

    public function get()
    {
        return $this->config;
    }

    public function set(HTMLPurifier_Config $config)
    {
        return $this->config = $config;
    }

    /**
     * purify
     *
     * @param $content
     * @return string
     */
    public function purify($content)
    {
        $purifier = new HTMLPurifier($this->config);
        return $purifier->purify($content);
    }
}
