<?php

class SeoHeroToolProAdmin extends LeftAndMain
{
    private static $url_segment = 'shtpro-admin';
    private static $allowed_actions = array('Analyse');

    public $dom;
    public $rules = array('good' => 0, 'notice' => 0, 'wrong' => 0, 'total' => 0);
    public $pageHtml;
    public $pageBody;
    public $pageImages;
    public $pageTitle;
    public $wordCount;

    public function Analyse()
    {
        $PageID = $this->request->param('ID');
        $Page = Page::get()->byID($PageID);
        if (!$Page->ID) {
            return false;
        }
        $URL = $Page->AbsoluteLink();
        $versions = $Page->allVersions();

        if ($this->loadPage($URL, $Page) == false) {
            return false;
        }

        $contentID = Config::inst()->get('SeoHeroToolPro', 'contentID');
        if ($contentID) {
            $this->wordCount = str_word_count(strip_tags($this->dom->getElementByID($contentID)->nodeValue));
        } else {
            $this->wordCount = str_word_count(strip_tags($this->pageBody));
        }
        if ($contentID == '') {
            $contentID = false;
        }

      //  $shtpMeta = $this->checkMeta($Page);

        $shtpTitle = $this->checkTitle();
        $shtpMeta = $this->checkMeta($Page);
        $shtpURL = $this->checkURL($Page);
        $shtpWordCount = $this->checkWordCount();
        $shtpDirectoryDepth = $this->checkLinkDirectoryDepth($Page);
        $shtpHeadlineStructure = $this->checkHeadlineStructure();

        $render = $this->owner->customise(array(
          'WordCount' => $this->wordCount,
          'PageLink' => $URL,
          'TitleResults' => $shtpTitle,
          'MetaResults' => $shtpMeta,
          'URLResults' => $shtpURL,
          'DirectoryDepthResults' => $shtpDirectoryDepth,
          'WordCountResults' => $shtpWordCount,
          'HeadlineResults' => $shtpHeadlineStructure,
          'RulesWrong' => $this->rules['wrong'],
          'RulesNotice' => $this->rules['notice'],
          'RulesGood' => $this->rules['good'],
          'RulesTotal' => $this->rules['total'],
        ))->renderWith('SeoHeroToolProAnalysePage');
        return $render;
    }

    private function updateRules($type = 3)
    {
        $this->rules['total']++;
        switch ($type) {
        case '1':
            $this->rules['wrong']++;
            break;
        case '2':
            $this->rules['notice']++;
            break;
        default:
            $this->rules['good']++;
        }
    }

    private function checkTitle()
    {
        $lengthOfTitle = strlen($this->pageTitle);
        $UnsortedListEntries = new ArrayList();
        $titleHelpLink = 'https://seo-summary.de/title-tag-der-optimale-seitentitel/';
        $lengthRecommendation =  _t('SeoHeroToolProAnalyse.TitleLengthRecommendation', 'Recommendation 44 - 61 Characters');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $lengthOfTitle;
        if ($lengthOfTitle < 8) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthShort', 'The title of this site is too short! ').$returnLength,
                  'IconMess' => '1',
                  'HelpLink' => $titleHelpLink,
              )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfTitle >= 8 && $lengthOfTitle < 44) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.TitleLengthOK', 'The title of this site is fine, but it could be longer. '). $returnLength,
                'IconMess' => '2',
                'HelpLink' => $titleHelpLink,
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfTitle >= 44 && $lengthOfTitle < 56) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthGood', 'The title of this site is perfect. Well Done! '). $returnLength,

                  'IconMess' => '3',
                  'HelpLink' => $titleHelpLink,
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthLong', 'The title of this site is too long. '). $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => $titleHelpLink,
                )
            ));
            $this->updateRules(1);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.Title', 'Title'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkMeta($Page)
    {
        $metaDescription = $Page->BetterMetaDescription();
        $lengthOfMetaDescription = strlen($metaDescription);
        $metaDescHelpLink = 'http://www.searchmetrics.com/de/glossar/meta-description/';
        $UnsortedListEntries = new ArrayList();
        $lengthRecommendation =  _t('SeoHeroToolProAnalyse.MetaLengthRecommendation', '(Optimal length is between 120 - 140 Characters)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $lengthOfMetaDescription;

        if ($lengthOfMetaDescription < 79) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.MetaLengthShort', 'The Meta-Description is too short.'). $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => $metaDescHelpLink,
                )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfMetaDescription >= 80 && $lengthOfMetaDescription < 120) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.MetaLengthOK', 'The Meta-Description is fine, but it could be longer. '). $returnLength,
                    'IconMess' => '2',
                    'HelpLink' => $metaDescHelpLink,
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfMetaDescription >= 120 && $lengthOfMetaDescription <= 140) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.MetaLengthGood', 'The length of the Meta-Description is perfect. Well Done! '). $returnLength,
                    'IconMess' => '3',
                    'HelpLink' => $metaDescHelpLink,
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.MetaLengthLong', 'The Meta-Description is too long. ') . $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => $metaDescHelpLink,
                )
            ));
            $this->updateRules(1);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.Metadescription', 'Meta-Description'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkURL($Page)
    {
        $URL = $Page->URLSegment;
        $UnsortedListEntries = new ArrayList();
        $lengthOfURL = strlen($URL);
        $urlHelpLink = 'http://www.seo-scene.de/seo/geheimnis-perfekte-seo-urls-1417.html';
        $lengthRecommendation =  _t('SeoHeroToolProAnalyse.URLLengthRecommendation', '(Optimal length is between 20 - 120 Characters)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $lengthOfURL;

        if ($URL == "home") {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthHome', 'The URL of the homepage has to be "home"'),
                    'IconMess' => '3',
                    'HelpLink' => $urlHelpLink,
                )
            ));
            $this->updateRules(3);
        } elseif ($lengthOfURL < 10) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.URLLengthShort', 'The length of the URL is way too short. ').$returnLength,
                  'IconMess' => '3',
                  'HelpLink' => $urlHelpLink,
              )
          ));
            $this->updateRules(2);
        } elseif ($lengthOfURL >= 10 && $lengthOfURL < 20) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthOK', 'The length of the URL is fine but it could be longer. ').$returnLength,
                    'IconMess' => '2',
                    'HelpLink' => $urlHelpLink,
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfURL >= 20 && $lengthOfURL <= 120) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthGood', 'The length of the URL is perfect. Well Done! ').$returnLength,
                    'IconMess' => '3',
                    'HelpLink' => $urlHelpLink,
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthLong', 'The length of the URL is too long. ').$returnLength,
                    'IconMess' => '1',
                    'HelpLink' => $urlHelpLink,
                )
            ));
            $this->updateRules(1);
        }

        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.URL', 'URL Parameter'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkWordCount()
    {
        $UnsortedListEntries = new ArrayList();
        $lengthRecommendation =  _t('SeoHeroToolProAnalyse.WordCountRecommendation', '(A page should contain more than 200 words)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $this->wordCount;
        if ($this->wordCount < 50) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.ContentLengthShort', 'The sites text is way too short. ') .$returnLength,
                  'IconMess' => '1',
                )
            ));
            $this->updateRules(1);
        } elseif ($this->wordCount >= 50 && $this->wordCount <= 200) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.ContentLengthOK', 'The site has less than 200 words. '). $returnLength,
                  'IconMess' => '2',
                )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.ContentLengthGood', 'The site has more than 200 words. Well Done. '). $returnLength,
                  'IconMess' => '3',
                )
            ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.WordCount', 'Word Count'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkLinkDirectoryDepth($Page)
    {
        $UnsortedListEntries = new ArrayList();
        $folders = substr_count($Page->Link(), "/");
        $addText = _t('SeoHeroToolProAnalyse.DirectoryDepth', ' DirectoryDepth').': '.$folders;
        if ($folders > 5) {
            $UnsortedListEntries->push(new ArrayData(
            array(
                'Content' => _t('SeoHeroToolProAnalyse.TooHighDirectoryDepth', 'The Directory Depth should be as small as possible. '). $addText,
                'IconMess' => '2',
                )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.TooHighDirectoryDepth', 'The Directory Depth is perfect. '). $addText,
                'IconMess' => '2',
                )
              ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.DirectoryDepth', ' DirectoryDepth'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function helperForHeadlineCheck()
    {
        $UnsortedListEntries = new ArrayList();
        $headlineArray = array(
          'h1' => $this->dom->getElementsByTagName('h1'),
          'h2' => $this->dom->getElementsByTagName('h2'),
          'h3' => $this->dom->getElementsByTagName('h3')
        );
        foreach ($headlineArray as $key => $value) {
            $headlineCount = $value->length;
            if ($headlineCount == 0) {
                if ($key == 'h1') {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolAnalyse.NoH1', 'There is no h1-Tag.'),
                            'IconMess' => '1',
                        )
                    ));
                    $this->updateRules(1);
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' => _t('SeoHeroToolAnalyse.NoH1', 'Found no tag').' '.$key,
                          'IconMess' => '1',
                      )
                  ));
                    $this->updateRules(2);
                }
            } elseif ($headlineCount > 1 && $key == 'h1') {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' => _t('SeoHeroToolAnalyse.TooMuchH1', 'There is more than one h1-Tag'),
                        'IconMess' => '2',
                    )
                ));
                $this->updateRules(2);
            } elseif ($headlineCount == 1 && $key == 'h1') {
                $sc = SiteConfig::get()->First();
                $headlineContent = $value->item(0)->nodeValue;
                $headlineLength = strlen($headlineContent);
                $lengthRecommendation =  _t('SeoHeroToolProAnalyse.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                $addText = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $headlineLength;
                if ($headlineLength == 0) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthEmpty', 'The h1 Headline is empty').' '.$addText,
                            'IconMess' => '1',
                        )
                    ));
                    $this->updateRules(1);
                } elseif ($headlineLength < 15) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthTooShort', 'The h1 Headline it quite short.').' '.$addText,
                            'IconMess' => '2',
                        )
                    ));
                    $this->updateRules(2);
                } elseif ($headlineLength > 80) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthTooLong', 'The h1 Headline it quite long.').' '.$addText,
                            'IconMess' => '2',
                        )
                    ));
                    $this->updateRules(2);
                } elseif ($headlineContent == $sc->Title) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1SameSiteConfigTitle', 'The h1 tag and site title are the same. Please change the h1 content.'),
                            'IconMess' => '1',
                        )
                    ));
                    $this->updateRules(1);
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                          'Content' => _t('SeoHeroToolProAnalyse.h1SameSiteConfigTitle', 'The h1 tag has a good length').' '.$addText,
                          'IconMess' => '3',
                        )
                    ));
                    $this->updateRules(3);
                }
            } else {
                foreach ($value as $singleHeadline) {
                    $headlineContent = $singleHeadline->textContent;
                    $headlineLength = strlen($headlineContent);
                    $lengthRecommendation =  _t('SeoHeroToolProAnalyse.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                    $addText = $lengthRecommendation.' - '._t('SeoHeroToolProAnalyse.Length', 'Length').': ' . $headlineLength;
                    if ($headlineLength == 0) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => _t('SeoHeroToolProAnalyse.headlineLengthEmpty', 'The Headline is empty').' '.$addText,
                                'IconMess' => '1',
                            )
                        ));
                        $this->updateRules(1);
                    } elseif ($headlineLength < 10) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolAnalyse.headlineLengthTooShort', ' Headline is quite short.').' '.$addText,
                                'IconMess' => '2',
                            )
                        ));
                        $this->updateRules(2);
                    } elseif ($headlineLength > 80) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolAnalyse.headlineLengthTooLong', ' Headline is quite long.') .' '.$addText,
                                'IconMess' => '2',
                            )
                        ));
                        $this->updateRules(2);
                    } else {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolAnalyse.headkubeLenghtGood', ' Headline has a good length.').' '.$addText,
                                'IconMess' => '3',
                            )
                        ));
                        $this->updateRules(3);
                    }
                }
            }
        }
        return $UnsortedListEntries;
    }

    private function checkHeadlineStructure()
    {
        $UnsortedListEntries = new ArrayList();
        $UnsortedListEntries = $this->helperForHeadlineCheck();

        $headlineStructure = array(
          '6' => $this->dom->getElementsByTagName('h6'),
          '5' => $this->dom->getElementsByTagName('h5'),
          '4' => $this->dom->getElementsByTagName('h4'),
          '3' => $this->dom->getElementsByTagName('h3'),
          '2' => $this->dom->getElementsByTagName('h2'),
          '1' => $this->dom->getElementsByTagName('h1')
        );

        $headlineStructureError = 0;
        foreach ($headlineStructure as $hsKey => $hsVal) {
            if ($hsKey > 1 && $hsVal->length >= 1 && $headlineStructure[$hsKey - 1]->length == 0) {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' =>
                        sprintf(
                            _t('SeoHeroToolAnalyse.HeadlineStructureError',
                                'There is at least H%1$d Headline but no H%2$d Headline.'),
                            $hsKey, $hsKey - 1
                        ),

                        'IconMess' => '1',
                    )
                ));
                $headlineStructureError = 1;
                $this->updateRules(1);
            } elseif ($hsKey == 1 && $headlineStructureError == 0) {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' =>_t('SeoHeroToolAnalyse.NoHeadlineStructureError', 'The Headline Structure does not contain obvious Errors.'),
                        'IconMess' => '3',
                    )
                ));
                $this->updateRules(3);
            }
        }

        return array(
            'Headline' => _t('SeoHeroToolProAnalyse.Headlines', 'Headlines'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function PageExists($URL)
    {
        $header = @get_headers($URL);
        return is_array($header) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $header[0]) : false;
    }

    private function loadPage($URL)
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $linkExists = $this->PageExists($URL);
        if ($linkExists == false) {
            return false;
        }
        $html = file_get_contents($URL);
        $this->dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $this->dom->preserveWhiteSpace = false;
        $this->pageHtml = $this->dom->saveHTML();
        $this->pageBody = $this->dom->getElementsByTagName('body')->item(0)->nodeValue;
        $this->pageImages = $this->dom->getElementsByTagName('img');
        $this->pageTitle = $this->dom->getElementsByTagName('title')->item(0)->nodeValue;
        return true;
    }
}
