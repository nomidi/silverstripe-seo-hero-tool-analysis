<?php
if (!defined('SEO_HERO_TOOL_ANALYSIS_PATH')) {
    define('SEO_HERO_TOOL_ANALYSIS_PATH', rtrim(basename(dirname(__FILE__))));
}

Config::inst()->update('LeftAndMain', 'extra_requirements_css', array(SEO_HERO_TOOL_ANALYSIS_PATH.'/css/cms.css'));
