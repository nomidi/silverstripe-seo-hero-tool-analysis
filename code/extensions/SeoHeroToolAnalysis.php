<?php
class SeoHeroToolAnalysis extends DataExtension implements PermissionProvider
{
    public function providePermissions()
    {
        return array(
      'SHT_ANALYSIS_VIEW' => array(
            'name' => _t('SeoHeroToolAnalysis.View', 'Access Seo Hero Tool Analysis Results'),
            'category' => _t('Block.PermissionCategory', 'Seo Hero Tool'),
        ),
    );
    }


    public function updateCMSActions(FieldList $actions)
    {
        if (Permission::check('ADMIN') || Permission::check('SHT_ANALYSIS_VIEW')) {
            $pagelink = "/admin/shtpro-admin/Analysis/".$this->owner->ID;
            $analysefield = $this->owner->customise(array(
        'Link'=> $pagelink,
    'SeoHeroToolAnalysisPath' => SEO_HERO_TOOL_ANALYSIS_PATH
  ))->renderWith('SeoHeroToolAnalysisCMSAction');
            $info = LiteralField::create('SeoHeroToolAnalysisShortInfo', $analysefield);
            $actions->push($info);
        }
    }
}
