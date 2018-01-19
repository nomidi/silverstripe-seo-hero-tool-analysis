<?php
class SeoHeroToolAnalysis extends DataExtension
{
    public function updateCMSActions(FieldList $actions)
    {
        $pagelink = "/admin/shtpro-admin/Analysis/".$this->owner->ID;
        $analysefield = $this->owner->customise(array(
        'Link'=> $pagelink,
    'SeoHeroToolAnalysisPath' => SEO_HERO_TOOL_ANALYSIS_PATH
  ))->renderWith('SeoHeroToolAnalysisCMSAction');
        $info = LiteralField::create('SeoHeroToolAnalysisShortInfo', $analysefield);
        $actions->push($info);
    }
}
