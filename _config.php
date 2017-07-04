<?php
if (!defined('SEO_HERO_TOOL_PRO_PATH')) {
    define('SEO_HERO_TOOL_PRO_PATH', rtrim(basename(dirname(__FILE__))));
}

Config::inst()->update('LeftAndMain', 'extra_requirements_css', array(SEO_HERO_TOOL_PRO_PATH.'/css/cms.css'));
