<?php
class SeoHeroToolPro extends DataExtension
{
    public function updateCMSActions(FieldList $actions)
    {
        $pagelink = "/admin/shtpro-admin/Analyse/".$this->owner->ID;
        $analysefield = $this->owner->customise(array(
        'Link'=> $pagelink,
    'SeoHeroToolProPath' => SEO_HERO_TOOL_PRO_PATH
  ))->renderWith('SeoHeroToolAnalyseCMSAction');
        $info = LiteralField::create('SeoHeroToolAnalyseShortInfo', $analysefield);
        $actions->push($info);
    }
}
