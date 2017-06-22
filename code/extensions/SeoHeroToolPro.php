<?php
class SeoHeroToolPro extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $pageid = $this->owner->ID;
        $shtpro_analyse_link = "/admin/shtpro-admin/Analyse/".$pageid;
        $shtproLinkField = '<br> <a href="'.$shtpro_analyse_link.'" target="_blank">Seo Hero Tool Pro Analyse</a>.';
        $profield = LiteralField::create('SHTPAnalyse', $shtproLinkField);

        $fields->addFieldToTab('Root.SeoHeroTool', $profield);
        return $fields;
    }
}
