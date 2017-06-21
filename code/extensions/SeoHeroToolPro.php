<?php
class SeoHeroToolPro extends Extension
{
    public function updateCMSActions(FieldList $actions)
    {
        $field = $this->owner->customise(array(
            'SeoHeroToolPath' => SEO_HERO_TOOL_PATH,
        ))->renderWith('SeoHeroToolProAnalyseCMSAction');

        $info = LiteralField::create('SeoHeroToolAnalyseShortInfo', $field);
        $actions->push($info);
    }
}
