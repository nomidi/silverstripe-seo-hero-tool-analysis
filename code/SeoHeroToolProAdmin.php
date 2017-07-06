<?php

class SeoHeroToolProAdmin extends LeftAndMain
{
    private static $url_segment = 'shtpro-admin';
    private static $allowed_actions = array('Analyse');

    public $dom;
    public $rules = array('good' => 0, 'notice' => 0, 'wrong' => 0, 'total' => 0);
    public $pageHTML;
    public $pageBody;
    public $pageImages;
    public $pageLinks;
    public $pageTitle;
    public $wordCount;
    public $siteRunsLocally;

    public function canView($member = null)
    {
        if (Permission::check('ADMIN')) {
            return true;
        } else {
            Security::permissionFailure();
        }
    }

    /*
^     Functino Analyse checks the actual site
     */
    public function Analyse()
    {
        $PageID = $this->request->param('ID');
        $Page = Page::get()->byID($PageID);
        if (!$Page->ID) {
            return false;
        }
        $URL = $Page->AbsoluteLink();
        $versions = $Page->allVersions();
        Requirements::clear();
        if ($this->loadPage($URL, $Page) == false) {
            $render = $this->owner->customise(array(
              'AccessError' => _t('SeoHeroToolPro.CanNotAccessCurrentPage', 'This page can not be accessed by the Analyse function. Please check the rights and if there are any authentication necessary.'),
                'SHTProPath' => '/' .SEO_HERO_TOOL_PRO_PATH,
            ))->renderWith('SeoHeroToolProAnalysePage');
            return $render;
        }

        $this->checkIfSiteRunsLocally();

        $contentID = Config::inst()->get('SeoHeroToolPro', 'contentID');

        if ($contentID) {
            $this->wordCount = str_word_count(strip_tags($this->dom->getElementByID($contentID)->nodeValue));
        } else {
            $this->wordCount = str_word_count(strip_tags($this->pageBody));
        }

        $shtpTitle = $this->checkTitle();
        $shtpSkipToMainContent = $this->checkSkipToMainContent();
        $shtpMeta = $this->checkMeta($Page);
        $shtpURL = $this->checkURL($Page);
        $shtpUsefulFiles = $this->checkForUsefulFiles();
        $shtpWordCount = $this->checkWordCount();
        $shtpDirectoryDepth = $this->checkLinkDirectoryDepth($Page);
        $shtpHeadlineStructure = $this->checkHeadlineStructure();
        $shtpLinks = $this->checkLinks($Page);
        $shtpImages = $this->checkImages();
        $shtpStrong = $this->checkStrong();
        $shtpw3c = $this->getW3CValidation($URL);
        $shtpStructuredData = $this->checkStructuredData($Page);
        $Keywords = new SeoHeroToolProAnalyseKeyword();
        $shtpKeywords = $Keywords->checkKeywords($Page, $this->pageImages);
        $keywordRules = $Keywords->getKeywordResults();
        $shtpCountArray = $this->getCountArray();

        $render = $this->owner->customise(array(
          'WordCount' => $this->wordCount,
          'PageLink' => $URL,
          'TitleResults' => $shtpTitle,
          'MetaResults' => $shtpMeta,
          'URLResults' => $shtpURL,
          'DirectoryDepthResults' => $shtpDirectoryDepth,
          'WordCountResults' => $shtpWordCount,
          'HeadlineResults' => $shtpHeadlineStructure,
          'LinkResults' => $shtpLinks,
          'W3CResults' => $shtpw3c,
          'StrongResults' => $shtpStrong,
          'ImageResults' => $shtpImages,
          'KeywordResults' => $shtpKeywords,
          'UsefulFilesResults' => $shtpUsefulFiles,
          'StructuredDataResults' => $shtpStructuredData,
          'SkipMainContentResults' => $shtpSkipToMainContent,
          'CountResults' => $shtpCountArray,
          'RulesWrong' => $this->rules['wrong'],
          'RulesNotice' => $this->rules['notice'],
          'RulesGood' => $this->rules['good'],
          'RulesTotal' => $this->rules['total'],
          'KeywordRulesWrong' => $keywordRules['wrong'],
          'KeywordRulesNotice' => $keywordRules['notice'],
          'KeywordRulesGood' => $keywordRules['good'],
          'KeywordRulesTotal' => $keywordRules['total'],
          'SHTProPath' => '/' .SEO_HERO_TOOL_PRO_PATH,
        ))->renderWith('SeoHeroToolProAnalysePage');
        return $render;
    }

    /*
      Checks if in the configuration the setting is set that this site runs locally. If yes the W3C Check will be skipped.
      Furhtermore the Structured Data check will not display the link to googles structured data tool.
     */
    private function checkIfSiteRunsLocally()
    {
        $this->siteRunsLocally = Config::inst()->get('SeoHeroToolPro', 'locally');
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

    private function getCountArray()
    {
        $shtpCountArray = new ArrayList();

        $shtpCountArray->push(array(
          'CountLabel'=>_t('SeoHeroToolPro.NumberOfWords', 'Number of Words'),
          'CountValue'=> $this->wordCount
        ));
        $shtpCountArray->push(array(
          'CountLabel'=>_t('SeoHeroToolPro.NumberOfImages', 'Number of Images'),
          'CountValue'=> $this->pageImages->length
        ));
        $shtpCountArray->push(array(
          'CountLabel'=>_t('SeoHeroToolPro.NumberOfLinks', 'Number of Links'),
          'CountValue'=> $this->pageLinks->length
        ));
        return array('UnsortedListEntries'=>$shtpCountArray);
    }

    /*
      Function checkTitle() checks if the title of the page has the correct length and will return the appropiate message.
     */
    private function checkTitle()
    {
        $lengthOfTitle = strlen($this->pageTitle);
        $UnsortedListEntries = new ArrayList();
        $titleHelpLink = 'https://seo-summary.de/title-tag-der-optimale-seitentitel/';
        $lengthRecommendation =  _t('SeoHeroToolPro.TitleLengthRecommendation', 'Recommendation 44 - 61 Characters');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroTool.Length', 'Length').': ' . $lengthOfTitle;
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
          'Headline' => _t('SeoHeroToolPro.Title', 'Title'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkMeta($Page) checks if the Meta description has a good length and will return the appropiate answer.
      @param : $Page - the actual Page
     */
    private function checkMeta($Page)
    {
        $metaDescription = $Page->BetterMetaDescription();
        $lengthOfMetaDescription = strlen($metaDescription);
        $metaDescHelpLink = 'http://www.searchmetrics.com/de/glossar/meta-description/';
        $UnsortedListEntries = new ArrayList();
        $lengthRecommendation =  _t('SeoHeroToolPro.MetaLengthRecommendation', '(Optimal length is between 120 - 140 Characters)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $lengthOfMetaDescription;

        if ($lengthOfMetaDescription == 0) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.NoMetaDescription', 'The Meta-Description is empty.'),
                  'IconMess' => '1',
                  'HelpLink' => $metaDescHelpLink,
                )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfMetaDescription < 79) {
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
          'Headline' => _t('SeoHeroToolPro.Metadescription', 'Meta-Description'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkURL($Page) will check if tha URL has a good length and will return the appropiate answer.
      @param $Page  the actual Page
     */
    private function checkURL($Page)
    {
        $URL = $Page->URLSegment;
        $UnsortedListEntries = new ArrayList();
        $lengthOfURL = strlen($URL);
        $urlHelpLink = 'http://www.seo-scene.de/seo/geheimnis-perfekte-seo-urls-1417.html';
        $lengthRecommendation =  _t('SeoHeroToolPro.URLLengthRecommendation', '(Optimal length is between 20 - 120 Characters)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $lengthOfURL;

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
                  'IconMess' => '1',
                  'HelpLink' => $urlHelpLink,
              )
          ));
            $this->updateRules(1);
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
          'Headline' => _t('SeoHeroToolPro.URL', 'URL Parameter'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkWordCount()
    {
        $UnsortedListEntries = new ArrayList();
        $lengthRecommendation =  _t('SeoHeroToolPro.WordCountRecommendation', '(A page should contain more than 200 words)');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $this->wordCount;
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
          'Headline' => _t('SeoHeroToolPro.WordCount', 'Word Count'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkLinkDirectoryDepth() checks if the depth of the directory is fine and will return the appropiate answer.
      A too deep directory structure is not liked by search engines.
      @param $Page - the actual page
     */
    private function checkLinkDirectoryDepth($Page)
    {
        $UnsortedListEntries = new ArrayList();
        $folders = substr_count($Page->Link(), "/");
        $addText = _t('SeoHeroToolPro.DirectoryDepth', ' DirectoryDepth').': '.$folders;
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
                'IconMess' => '3',
                )
              ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.DirectoryDepth', ' DirectoryDepth'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function helperForHeadlineCheck() checks the occurence and length of the following tags: h1, h2, h3.
      It will return then the appropiate results.
      Will be called by the function checkHeadlineStructure()
     */
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
                            'Content' => _t('SeoHeroToolProAnalyse.NoH1', 'There is no h1-Tag.'),
                            'IconMess' => '1',
                        )
                    ));
                    $this->updateRules(1);
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' => _t('SeoHeroToolProAnalyse.NoH1', 'Found no tag').' '.$key,
                          'IconMess' => '1',
                      )
                  ));
                    $this->updateRules(2);
                }
            } elseif ($headlineCount > 1 && $key == 'h1') {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' => _t('SeoHeroToolProAnalyse.TooMuchH1', 'There is more than one h1-Tag'),
                        'IconMess' => '2',
                    )
                ));
                $this->updateRules(2);
            } elseif ($headlineCount == 1 && $key == 'h1') {
                $sc = SiteConfig::get()->First();
                $headlineContent = $value->item(0)->nodeValue;
                $headlineLength = strlen($headlineContent);
                $lengthRecommendation =  _t('SeoHeroToolPro.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                $addText = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $headlineLength;
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
                          'Content' => _t('SeoHeroToolProAnalyse.h1LengthGood', 'The h1 tag has a good length').' '.$addText,
                          'IconMess' => '3',
                        )
                    ));
                    $this->updateRules(3);
                }
            } else {
                $countHeadlines = 1;
                foreach ($value as $singleHeadline) {
                    $headlineContent = $singleHeadline->textContent;
                    $headlineLength = strlen($headlineContent);
                    $lengthRecommendation =  _t('SeoHeroToolProAnalyse.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                    $addText = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $headlineLength;
                    if ($headlineLength == 0) {
                        $i = 0;
                        $searchedHeadlinePos = 0;
                        for ($i = 0; $i < $countHeadlines; $i++) {
                            if ($i > 0) {
                                $searchedHeadlinePos = strpos($this->pageHTML, '<'.$key.'>', $searchedHeadlinePos + 4);
                            } else {
                                $searchedHeadlinePos = strpos($this->pageHTML, '<'.$key.'>');
                            }
                        }
                        if ($searchedHeadlinePos) {
                            $searchHeadlineEndPos = strpos($this->pageHTML, "</".$key.">", $searchedHeadlinePos);
                            $headlineTagContent = substr($this->pageHTML, $searchedHeadlinePos+4, $searchHeadlineEndPos - $searchedHeadlinePos-4);
                            if ($headlineTagContent != '') {
                                $presumableTag = substr($headlineTagContent, 1, strpos($headlineTagContent, ' '));
                                $UnsortedListEntries->push(new ArrayData(
                                  array(
                                      'Content' =>   sprintf(_t('SeoHeroToolProAnalyse.headlineWitSomeContent', 'The Headline %1$s has some kind of content but no text. Found the following tag in the Headline ').': '.$presumableTag, $key),
                                      'IconMess' => '2',
                                  )
                              ));
                                $this->updateRules(2);
                            } else {
                                $UnsortedListEntries->push(new ArrayData(
                                    array(
                                        'Content' =>   sprintf(_t('SeoHeroToolProAnalyse.headlineLengthEmpty', 'The Headline %1$s is empty').' '.$addText, $key),
                                        'IconMess' => '1',
                                    )
                                ));
                                $this->updateRules(1);
                            }
                        } else {
                            $UnsortedListEntries->push(new ArrayData(
                                array(
                                    'Content' =>   sprintf(_t('SeoHeroToolProAnalyse.headlineLengthEmpty', 'The Headline %1$s is empty').' '.$addText, $key),
                                    'IconMess' => '1',
                                )
                            ));
                            $this->updateRules(1);
                        }
                    } elseif ($headlineLength < 10) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLengthTooShort', ' Headline is quite short.').' '.$addText,
                                'IconMess' => '2',
                            )
                        ));
                        $this->updateRules(2);
                    } elseif ($headlineLength > 80) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLengthTooLong', ' Headline is quite long.') .' '.$addText,
                                'IconMess' => '2',
                            )
                        ));
                        $this->updateRules(2);
                    } else {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLenghtGood', ' Headline has a good length.').' '.$addText,
                                'IconMess' => '3',
                            )
                        ));
                        $this->updateRules(3);
                    }
                    $countHeadlines++;
                }
            }
        }
        return $UnsortedListEntries;
    }

    /*
      The function checkHeadlineStructure() checks the headline structure of the actual document.
      FOr example if h1, h2 h3 are present (with the help of function helperForHeadlineCheck()) and if there are skipped hX tags.
     */
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
                            _t('SeoHeroToolProAnalyse.HeadlineStructureError',
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
                        'Content' =>_t('SeoHeroToolProAnalyse.NoHeadlineStructureError', 'The Headline Structure does not contain obvious Errors.'),
                        'IconMess' => '3',
                    )
                ));
                $this->updateRules(3);
            }
        }
        return array(
            'Headline' => _t('SeoHeroToolPro.Headlines', 'Headlines'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkLinks() checks the links for titles and if there is content within the <a>-tags.
      @param $Page - the actual Page
     */
    private function checkLinks($Page)
    {
        $UnsortedListEntries = new ArrayList();
        $documentLinks = $this->pageLinks;
        $linkError = 0;
        $linkSameTitleNameMessage = '';

        foreach ($documentLinks as $link) {
            $linkName = $link->nodeValue;
            $linkline = 0;
            if ($linkName == '') {
                $lines = explode(PHP_EOL, $this->pageHTML);
                $countlines = count($lines);
                for ($i=1;  $i < $countlines; $i++) {
                    preg_match("/<a href=.*?><\/a>/", $lines[$i], $matches, PREG_OFFSET_CAPTURE);
                    if (isset($matches[0])) {
                        $start = '';
                        $end = '';
                        if (isset($lines[$i-2])) {
                            $start = $lines[$i-2].' <br/>'.$lines[$i-1].' <br/>';
                        } elseif (isset($lines[$i-1])) {
                            $start = $lines[$i-1].' <br/>';
                        }
                        if (isset($lines[$i+2])) {
                            $end = '<br/>'.$lines[$i+1].' <br/>'.$lines[$i+2];
                        } elseif (isset($lines[$i+1])) {
                            $end = '<br/>'.$lines[$i+1];
                        }
                        $linkline = $start.$lines[$i].$end;
                    }
                }
            }
            if (!$link->hasAttribute('title')) {
                if ($linkName != '') {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' =>
                          sprintf(
                              _t('SeoHeroToolProAnalyse.LinkNoAttrTitle',
                                  'The Link %s has no title attribute'),
                              $link->nodeValue
                          ),
                              'IconMess' => '1',
                          )
                    ));
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' =>
                          sprintf(
                              _t('SeoHeroToolProAnalyse.LinkNoAttrTitleAndNoLinkDescription',
                                  'Please check the following area for a Link with an empty "a" tag<em>%s</em>'),
                              $linkline
                          ),
                              'IconMess' => '1',
                          )
                    ));
                }


                $linkError = 1;
                $this->updateRules(1);
            } else {
                $linkTitle = $link->getAttribute('title');
                if ($linkName == $linkTitle) {
                    $linkSameTitleNameMessage.= sprintf(_t('SeoHeroToolProAnalyse.LinkHasSameTitleAsValue', 'The link %1$s'), $linkName).'<br/>';
                }
            }
        }
        if ($linkError == 0 && $documentLinks->length > 0) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.AllLinksHaveTitle', 'All links have a title attribute'),
                    'IconMess' => '3',
              )
            ));
            $this->updateRules(3);
        }
        if ($linkSameTitleNameMessage != '') {
            $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.LinksWithSameTitleAndName', 'There are links with the same Name and Title Attribute. Those are these links:').'<br/>'.$linkSameTitleNameMessage,
                    'IconMess' => '2',
              )
            ));
            $this->updateRules(2);
        }


        return array(
            'Headline' => _t('SeoHeroToolPro.Links', 'Links'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function PageExists() checks if the actual page exists.
      @param $URL - the actual page.
     */
    private function PageExists($URL)
    {
        $header = @get_headers($URL);
        return is_array($header) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $header[0]) : false;
    }

    /*
      The function loadPage() loads the actual DOM.
      @param $URL - the actual page
     */
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
        $this->pageLinks = $this->dom->getElementsByTagName('a');
        $this->pageHTML = $this->dom->saveHTML();
        $this->pageBody = $this->dom->getElementsByTagName('body')->item(0)->nodeValue;
        $this->pageImages = $this->dom->getElementsByTagName('img');
        $this->pageTitle = $this->dom->getElementsByTagName('title')->item(0)->nodeValue;
        return true;
    }

    /*
      The function checkStrong() checks if there are any B / Strong elements on the website
    */
    private function checkStrong()
    {
        $UnsortedListEntries = new ArrayList();
        $domStrong = $this->dom->getElementsByTagName('strong');
        $domStrongCount = $domStrong->length;
        if ($domStrongCount == 0) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundNoStrongElements', 'Found no strong elements on website (B / STRONG).'),
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundOneOrMoreStrongElements', 'Found one or more strong elements on website (B / STRONG).'),
                'IconMess' => '3'
              )
            ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolPro.strongElements', 'Strong elements'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkImages() checks if there are images on the site and also if the images contain alt-tags.
    */
    private function checkImages()
    {
        $UnsortedListEntries = new ArrayList();
        $domImageCount = $this->pageImages->length;
        $imagesWithoutAltTag = 0;
        $imagesWithSameAltTagAndFilename = 0;
        $message = '';
        $sameNameMessage = '';
        if ($domImageCount >= 1) {
            foreach ($this->pageImages as $img) {
                $imgAltTag = $img->getAttribute('alt');
                $imgFileName = $img->getAttribute('src');
                $imgFileNameWithoutPath = substr($imgFileName, strrpos($imgFileName, '/')+1);
                $imgFileNameWithoutExtension = substr($imgFileNameWithoutPath, 0, strrpos($imgFileNameWithoutPath, '.'));
                if (trim($imgAltTag) == '') {
                    $message .= sprintf(_t('SeoHeroToolPro.ImageWithoutAltTag', 'The Image %1$s does not contain an Alt-Tag.').'<br/>', $imgFileName);
                    $imagesWithoutAltTag++;
                }
                if ($imgAltTag == $imgFileNameWithoutPath || $imgAltTag == $imgFileNameWithoutExtension) {
                    $sameNameMessage .= sprintf(_t('SeoHeroToolPro.ImageWithSameAltAndFileName', 'The Image %1$s has the same filename and alt tag.'), $imgFileName);
                    $imagesWithSameAltTagAndFilename++;
                }
            }
            if ($imagesWithoutAltTag == 0) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                    'Content' => _t('SeoHeroToolProAnalyse.AllImagesWithAltTag', 'All Images contain Alt-Tags.'),
                    'IconMess' => '3'
                  )
                ));
                $this->updateRules(3);
            } elseif ($imagesWithoutAltTag >= 1) {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                      'Content' => sprintf(_t('SeoHeroToolProAnalyse.ImagesWithoutAltTagMessage', '%1$d out of %2$d Images are not having alt-Tags. The images are the following:').' <br/>'.$message, $imagesWithoutAltTag, $domImageCount),
                      'IconMess' => '1'
                    )
                ));
                $this->updateRules(1);
            }
            if ($imagesWithSameAltTagAndFilename == 0) {
                $UnsortedListEntries->push(new ArrayData(
                array(
                  'Content' => _t('SeoHeroToolProAnalyse.AllImagesWithDiffferentFilenameAndAltTag', 'All Images have for filename and alt-tag different values.'),
                  'IconMess' => '3'

                )
              ));
                $this->updateRules(3);
            } elseif ($imagesWithSameAltTagAndFilename >= 1) {
                $UnsortedListEntries->push(new ArrayData(
                array(
                  'Content' => sprintf(_t('SeoHeroToolProAnalyse.ImagesWithoutDifferentFilenameAndAltTag', '%1$d out of %2$d Images have the same alt-tag and filename. Those images are the following:').' <br/>'.$sameNameMessage, $imagesWithoutAltTag, $domImageCount),
                  'IconMess' => '2'
                )
              ));
                $this->updateRules(2);
            }
        } else {
            $imageCountText = _t('SeoHeroToolProAnalyse.NoImagesFound', 'This page does not contain any pictures');
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => $imageCountText,
                    'IconMess' => '2',
                )
            ));
            $this->updateRules(2);
        }
        return array(
          'Headline' => _t('SeoHeroToolPro.Images', 'Images'),
          'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function getW3CValidation() checks if the site runs locally. In that case no check will be performed. Otherweise
      the SeoHeroToolProW3CValidator will be called to check the actual Page for HTML Error and Warning.
      The result will be returned.
      @param $URL - the actual Page
     */
    private function getW3CValidation($URL)
    {
        $UnsortedListEntries = new ArrayList();
        if ($this->siteRunsLocally) {
            $UnsortedListEntries->push(new ArrayData(
          array(
                'Content' => _t('SeoHeroToolProAnalyse.SiteRunsLocally', 'The website runs locally. No W3C Validation possible.'),
                'IconMess' => '2',
              )
            ));
            $this->updateRules(2);

            return array(
              'Headline' => _t('SeoHeroToolProAnalyse.W3CResult', 'W3C Validator Result'),
              'UnsortedListEntries' => $UnsortedListEntries);
        }
        $W3CResults = SeoHeroToolProW3CValidator::checkData($URL);
        $foundHTMLErrors = 0;
        $foundHTMLWarnings = 0;
        $nonDocumentError = 0;
        /*
          If the site is hosted locally there will be a  "Name or service not known message"
         */
        if (isset($W3CResults->messages[0]->type) && $W3CResults->messages[0]->type == 'non-document-error') {
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => _t('SeoHeroToolProAnalyse.W3CNon-Document-Error', 'The Document can not be scanned, maybe the website runs locally?'),
                  'IconMess' => '2',
                )
              ));
            $this->updateRules(2);
            $nonDocumentError = 1;
        }

        if ($foundHTMLErrors == 0 && $foundHTMLWarnings == 0 && $nonDocumentError == 0) {
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => _t('SeoHeroToolProAnalyse.W3CNNoErrorsAndWarning', 'The Validator did not find any Errors or Warnings in your Document.'),
                  'IconMess' => '3',
                )
              ));
            $this->updateRules(3);
        } elseif ($nonDocumentError == 0) {
            if ($foundHTMLErrors == 1) {
                $messageFoundHTMLErrors = _t('SeoHeroToolPro.W3CErrorSingular', 'one HTML error');
            } elseif ($foundHTMLErrors > 1) {
                $messageFoundHTMLErrors = _t('SeoHeroToolPro.W3CErrorPlural', 'several HTML errors');
            }

            if ($foundHTMLWarnings == 1) {
                $messageFoundHTMLWarnings = _t('SeoHeroToolPro.W3CWarningSingular', 'one HTML warning');
            } elseif ($foundHTMLWarnings > 1) {
                $messageFoundHTMLWarnings = _t('SeoHeroToolPro.W3CWarningPlural', 'several HTML warnings');
            }
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => sprintf(
                _t('SeoHeroToolProAnalyse.W3CCountMessage',
                    'Es wurden auf der Seite %1$s und %2$s gefunden'),
                $messageFoundHTMLWarnings, $messageFoundHTMLErrors),
                  'IconMess' => '1',
                )
              ));
            $this->updateRules(1);
        }



        return array(
            'Headline' => _t('SeoHeroToolPro.W3CResult', 'W3C Validator Result'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      The function checkSkipToMainContent() checks if there is within the body a link called "Skip to Main Content" which allows ScreenReaders to skip for example the navigation of the actual site.
     */
    private function checkSkipToMainContent()
    {
        $UnsortedListEntries = new ArrayList();
        $search = 'skip to main content';
        if (!strpos(strtolower($this->pageBody), $search)) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.NoSkipToMainContentFound', 'No skip to main content link found on page.'),
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
            array(
              'Content' => _t('SeoHeroToolProAnalyse.SkipToMainContentFound', 'Skip to main content found on page.'),
              'IconMess' => '3'
            )
          ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.SkipToMainContent', 'Skip to main content'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }

    /*
      The function checkForUsefulFiles() checks if in the main folder of this project there is a robots.txt and sitemap.xml file and if they are accessible.
     */
    private function checkForUsefulFiles()
    {
        $UnsortedListEntries = new ArrayList();
        if (isset($_SERVER['HTTPS'])) {
            $server = 'https://'.$_SERVER['SERVER_NAME'];
        } else {
            $server = 'http://'.$_SERVER['SERVER_NAME'];
        }
        # check robots.txt
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server.'/robots.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        $curlResponse = curl_exec($ch);
        curl_close($ch);
        $curlResponeArray = explode("\n", $curlResponse);
        if (strpos($curlResponeArray[0], ' 200 OK')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundRobotstxt', 'Found Robots.txt file. Content was not checked.'),
                'IconMess' => '3'
              )
            ));
            $this->updateRules(3);
        } elseif (strpos($curlResponeArray[0], ' 404')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundNoRobotstxt', 'No Robots.txt existing. HTTP Response is ').': '.$curlResponeArray[0],
                'IconMess' => '1'
              )
            ));
            $this->updateRules(1);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.ProblemWithRobotstxt', 'Robots.txt file was not accessible. Please check this as this files helps searchengines. - The HTTP Response is').': '.$curlResponeArray[0],
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
        }
        # check sitemap.xml
        $chsm = curl_init();
        curl_setopt($chsm, CURLOPT_URL, $server.'/sitemap.xml');
        curl_setopt($chsm, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chsm, CURLOPT_HEADER, 1);
        curl_setopt($chsm, CURLOPT_NOBODY, 1);
        $chsmResponse = curl_exec($chsm);
        curl_close($chsm);
        $chsmResponseArray = explode("\n", $chsmResponse);
        if (strpos($chsmResponseArray[0], ' 200 OK')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundSitemapXML', 'Found Robots.txt file. Content was not checked.'),
                'IconMess' => '3'
              )
            ));
            $this->updateRules(3);
        } elseif (strpos($chsmResponseArray[0], ' 404')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundNoSitemapXML', 'No Sitemap.xml existing. HTTP Response is ').': '.$chsmResponseArray[0],
                'IconMess' => '1'
              )
            ));
            $this->updateRules(1);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.ProblemWithSitemapXML', 'Sitemap.xml file was not accessible. Please check this as this files helps searchengines. - The HTTP Response is').': '.$chsmResponseArray[0],
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.UsefulFiles', 'Files for Search Engines'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }

    /*
      The function checkStructuredData checks if there is structed data on the actual page. If so there will be a link to the google testing tool displayed, in case the website is not running locally
      @param $Page - actual page
     */
    private function checkStructuredData($Page)
    {
        $UnsortedListEntries = new ArrayList();
        if ($this->siteRunsLocally) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.SitesRunsLocally', 'Website runs locally. Therefore it is not possible to check for structured Data.'),
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
            return array(
            'Headline' => _t('SeoHeroToolAnalyse.StructuredData', 'Structured Data'),
            'UnsortedListEntries' => $UnsortedListEntries
          );
        }

        $searchPattern = "/<script type=\"application\/ld\+json\">([^<]*)<\/script>/s";
        preg_match_all($searchPattern, $this->pageHTML, $aMatch);
        $foundstData = $aMatch[1];
        $sDatas = $aMatch[1];
        if (count($sDatas) >= 1) {
            $UnsortedListEntries->push(new ArrayData(
            array(
              'Content' => _t('SeoHeroToolProAnalyse.StructuredDataFound', ' Found structured Data. You can check the structured Data here').': '.'<a href="https://search.google.com/structured-data/testing-tool?hl=de#url=' . urldecode($Page->AbsoluteLink()) . '" target="_blank">Structured Data Google</a>',
              'IconMess' => '3'
            )
          ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.NoStructuredDataFound', 'No structured Data found.'),
                'IconMess' => '2'
              )
            ));
            $this->updateRules(2);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.StructuredData', 'Structured Data'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }
}
