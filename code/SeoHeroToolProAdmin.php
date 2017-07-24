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
    public $pageID;
    public $wordCount;
    public $pageURL;
    public $pageURLSegment;
    public $siteRunsLocally;
    public $pageSpeedKey;
    public $pageSpeedTimeStamp;
    public $linkToWebsite = 'http://seo-hero-tools.com/toollink/';
    public $linkToPageSpeedInsights = 'https://developers.google.com/speed/pagespeed/insights/?url=';
    public $linkToW3CPage = 'https://validator.w3.org/nu/?doc=';
    public $W3CTimeStamp;

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
        $this->pageID = $PageID;
        $URL = $Page->AbsoluteLink();
        $this->URL = $URL;
        $this->pageURLSegment = $Page->URLSegment;
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
            $htmlForWordCount = $this->dom->getElementByID($contentID)->nodeValue;
        } else {
            $htmlForWordCount = $this->pageBody;
        }
        $this->wordCount = str_word_count(preg_replace('#\<(.+?)\>#', ' ', $htmlForWordCount));
        $shtpTitle = $this->checkTitle();
        $shtpSkipToMainContent = $this->checkSkipToMainContent();
        $shtpAMPLink = $this->checkAMPLink();
        $shtpMeta = $this->checkMeta($Page);
        $shtpURL = $this->checkURL($Page);
        $shtpUsefulFiles = $this->checkForUsefulFiles();
        $shtpWordCount = $this->checkWordCount();
        $shtpDirectoryDepth = $this->checkLinkDirectoryDepth($Page);
        $shtpHeadlineStructure = $this->checkHeadlineStructure();
        $shtpLinks = $this->checkLinks($Page);
        $shtpImages = $this->checkImages();
        $shtpStrong = $this->checkStrong();
        $shtpStructuredData = $this->checkStructuredData($Page);
        $Keywords = new SeoHeroToolProAnalyseKeyword();
        $shtpKeywords = $Keywords->checkKeywords($Page, $this->dom);
        $keywordRules = $Keywords->getKeywordResults();
        $debugMode = Config::inst()->get('SeoHeroToolPro', 'Debug');
        $pageSpeedResults = '';
        $shtpw3c = '';
        $pageSpeedResults =  $this->checkPageSpeed($URL);
        $shtpw3c = $this->getW3CValidation($URL);
        if (!$this->siteRunsLocally) {
            $shtpPageSpeedLink = $this->linkToPageSpeedInsights.urlencode($URL);
            $shtpW3CLink = $this->linkToW3CPage.urlencode($URL);
            $pageSpeedMessage = '';
            $W3CMessage = '';
        } else {
            $shtpPageSpeedLink = '';
            $shtpW3CLink = '';
            $pageSpeedMessage = _t('SeoHeroToolPro.PageSpeedLocally', 'The site runs locally and therefore a PageSpeed can not be calculated.');
            $W3CMessage = _t('SeoHeroToolPro.W3CLocally', 'The site runs locally and therefore a W3C Check can not be performed');
        }
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
          'PageSpeedResults' => $pageSpeedResults,
          'StrongResults' => $shtpStrong,
          'ImageResults' => $shtpImages,
          'KeywordResults' => $shtpKeywords,
          'UsefulFilesResults' => $shtpUsefulFiles,
          'StructuredDataResults' => $shtpStructuredData,
          'SkipMainContentResults' => $shtpSkipToMainContent,
          'AMPLinkResult' => $shtpAMPLink,
          'CountResults' => $shtpCountArray,
          'RulesWrong' => $this->rules['wrong'],
          'RulesNotice' => $this->rules['notice'],
          'RulesGood' => $this->rules['good'],
          'RulesTotal' => $this->rules['total'],
          'KeywordRulesWrong' => $keywordRules['wrong'],
          'KeywordRulesNotice' => $keywordRules['notice'],
          'KeywordRulesGood' => $keywordRules['good'],
          'KeywordRulesTotal' => $keywordRules['total'],
          'LinkToWebsite' => $this->linkToWebsite,
          'PageSpeedMessage' => $pageSpeedMessage,
          'PageSpeedLink' => $shtpPageSpeedLink,
          'PageSpeedTimestamp' =>   $this->pageSpeedTimeStamp,
          'W3CLink' => $shtpW3CLink,
          'W3CMessage' => $W3CMessage,
          'W3CTimeStamp' => $this->W3CTimeStamp,
          'DebugMode' => $debugMode,
          'ContentLocale'=>'de-DE',
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
        $this->siteRunsLocally = Config::inst()->get('SeoHeroToolPro', 'Local');
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

        $titleHelpLink = "";
        $lengthRecommendation =  _t('SeoHeroToolPro.TitleLengthRecommendation', 'Recommendation 44 - 56 Characters');
        $returnLength = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $lengthOfTitle;



        if ($lengthOfTitle < 8) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthShort', 'The title of this site is too short! ').$returnLength,
                  'IconMess' => '1',
                  'HelpLink' => 'TitleLengthShort',
              )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfTitle >= 8 && $lengthOfTitle < 44) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.TitleLengthOK', 'The title of this site is fine, but it could be longer. '). $returnLength,
                'IconMess' => '2',
                'HelpLink' => 'TitleLengthOK',
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfTitle >= 44 && $lengthOfTitle < 56) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthGood', 'The title of this site is perfect. Well Done! '). $returnLength,

                  'IconMess' => '3',
                  'HelpLink' => 'TitleLengthGood',
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.TitleLengthLong', 'The title of this site is too long. '). $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => 'TitleLengthLong',
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
                  'HelpLink' => 'NoMetaDescription',
                )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfMetaDescription < 79) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.MetaLengthShort', 'The Meta-Description is too short.'). $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => 'MetaLengthShort',
                )
            ));
            $this->updateRules(1);
        } elseif ($lengthOfMetaDescription >= 80 && $lengthOfMetaDescription < 120) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.MetaLengthOK', 'The Meta-Description is fine, but it could be longer. '). $returnLength,
                    'IconMess' => '2',
                    'HelpLink' => 'MetaLengthOK',
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfMetaDescription >= 120 && $lengthOfMetaDescription <= 140) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.MetaLengthGood', 'The length of the Meta-Description is perfect. Well Done! '). $returnLength,
                    'IconMess' => '3',
                    'HelpLink' => 'MetaLengthGood',
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.MetaLengthLong', 'The Meta-Description is too long. ') . $returnLength,
                  'IconMess' => '1',
                  'HelpLink' => 'MetaLengthLong',
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
                    'HelpLink' => 'URLLengthHome',
                )
            ));
            $this->updateRules(3);
        } elseif ($lengthOfURL < 10) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.URLLengthShort', 'The length of the URL is way too short. ').$returnLength,
                  'IconMess' => '1',
                  'HelpLink' => 'URLLengthShort',
              )
          ));
            $this->updateRules(1);
        } elseif ($lengthOfURL >= 10 && $lengthOfURL < 20) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthOK', 'The length of the URL is fine but it could be longer. ').$returnLength,
                    'IconMess' => '2',
                    'HelpLink' => 'URLLengthOK',
                )
            ));
            $this->updateRules(2);
        } elseif ($lengthOfURL >= 20 && $lengthOfURL <= 120) {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthGood', 'The length of the URL is perfect. Well Done! ').$returnLength,
                    'IconMess' => '3',
                    'HelpLink' => 'URLLengthGood',
                )
            ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
                array(
                    'Content' => _t('SeoHeroToolProAnalyse.URLLengthLong', 'The length of the URL is too long. ').$returnLength,
                    'IconMess' => '1',
                    'HelpLink' => 'URLLengthLong',
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
                  'HelpLink' => 'ContentLengthShort'
                )
            ));
            $this->updateRules(1);
        } elseif ($this->wordCount >= 50 && $this->wordCount <= 200) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.ContentLengthOK', 'The site has less than 200 words. '). $returnLength,
                  'IconMess' => '2',
                  'HelpLink' => 'ContentLengthOK'
                )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                  'Content' => _t('SeoHeroToolProAnalyse.ContentLengthGood', 'The site has more than 200 words. Well Done. '). $returnLength,
                  'IconMess' => '3',
                  'HelpLink' => 'ContentLengthGood'
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
                'HelpLink' => 'TooHighDirectoryDepth'
                )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FineDirectoryDepth', 'The Directory Depth is perfect. '). $addText,
                'IconMess' => '3',
                'HelpLink' => 'FineDirectoryDepth'
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
                            'HelpLink' => 'NoH1'
                        )
                    ));
                    $this->updateRules(1);
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' => sprintf(_t('SeoHeroToolProAnalyse.NoHX', 'No %1$s Tag Found'), $key),
                          'IconMess' => '2',
                          'HelpLink' => 'NoHX'
                      )
                  ));
                    $this->updateRules(2);
                }
            } elseif ($headlineCount > 1 && $key == 'h1') {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' => _t('SeoHeroToolProAnalyse.TooMuchH1', 'There is more than one h1-Tag'),
                        'IconMess' => '2',
                        'HelpLink' => 'TooMuchH1'
                    )
                ));
                $this->updateRules(2);
            } elseif ($headlineCount == 1 && $key == 'h1') {
                $sc = SiteConfig::get()->First();
                $headlineContent = $value->item(0)->nodeValue;
                $headlineLength = strlen($headlineContent);

                $lengthRecommendation =  _t('SeoHeroToolPro.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                $addText = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $headlineLength.' - '.$headlineContent;
                if ($headlineLength == 0) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthEmpty', 'The h1 Headline is empty').' '.$addText,
                            'IconMess' => '1',
                            'HelpLink' => 'h1LengthEmpty'
                        )
                    ));
                    $this->updateRules(1);
                } elseif ($headlineLength < 15) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthTooShort', 'The h1 Headline it quite short.').' '.$addText,
                            'IconMess' => '2',
                            'HelpLink' => 'h1LengthTooShort'
                        )
                    ));
                    $this->updateRules(2);
                } elseif ($headlineLength > 80) {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                            'Content' => _t('SeoHeroToolProAnalyse.h1LengthTooLong', 'The h1 Headline it quite long.').' '.$addText,
                            'IconMess' => '2',
                            'HelpLink' => 'h1LengthTooLong'
                        )
                    ));
                    $this->updateRules(2);
                } else {
                    $UnsortedListEntries->push(new ArrayData(
                        array(
                          'Content' => _t('SeoHeroToolProAnalyse.h1LengthGood', 'The h1 tag has a good length').' '.$addText,
                          'IconMess' => '3',
                          'HelpLink' => 'h1LengthGood'
                        )
                    ));
                    $this->updateRules(3);
                }
                if ($headlineContent == $sc->Title && $this->pageURLSegment != 'home') {
                    $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' => _t('SeoHeroToolProAnalyse.h1SameSiteConfigTitle', 'The h1 tag and general site title are the same. Please change the h1 content.').' - '.$headlineContent,
                          'IconMess' => '1',
                          'HelpLink' => 'h1SameSiteConfigTitle'
                      )
                  ));
                    $this->updateRules(1);
                }
            } else {
                $countHeadlines = 1;
                foreach ($value as $singleHeadline) {
                    $headlineContent = $singleHeadline->textContent;
                    $headlineLength = strlen($headlineContent);
                    $lengthRecommendation =  _t('SeoHeroToolProAnalyse.HeadLineRecommendation', '(optimal length between 15 and 80 Characters)');
                    $addText = $lengthRecommendation.' - '._t('SeoHeroToolPro.Length', 'Length').': ' . $headlineLength.' - '.$headlineContent;
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
                                      'HelpLink' => 'headlineWitSomeContent'
                                  )
                              ));
                                $this->updateRules(2);
                            } else {
                                $UnsortedListEntries->push(new ArrayData(
                                    array(
                                        'Content' =>   sprintf(_t('SeoHeroToolProAnalyse.headlineLengthEmpty', 'The Headline %1$s is empty').' '.$addText, $key),
                                        'IconMess' => '1',
                                        'HelpLink' => 'headlineLengthEmpty'
                                    )
                                ));
                                $this->updateRules(1);
                            }
                        } else {
                            $UnsortedListEntries->push(new ArrayData(
                                array(
                                    'Content' =>   sprintf(_t('SeoHeroToolProAnalyse.headlineLengthEmpty', 'The Headline %1$s is empty').' '.$addText, $key),
                                    'IconMess' => '1',
                                    'HelpLink' => 'headlineLengthEmpty'
                                )
                            ));
                            $this->updateRules(1);
                        }
                    } elseif ($headlineLength < 10) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLengthTooShort', ' Headline is quite short.').' '.$addText,
                                'IconMess' => '2',
                                'HelpLink' => 'headlineLengthTooShort'
                            )
                        ));
                        $this->updateRules(2);
                    } elseif ($headlineLength > 80) {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLengthTooLong', ' Headline is quite long.') .' '.$addText,
                                'IconMess' => '2',
                                'HelpLink' => 'headlineLengthTooLong'
                            )
                        ));
                        $this->updateRules(2);
                    } else {
                        $UnsortedListEntries->push(new ArrayData(
                            array(
                                'Content' => $key._t('SeoHeroToolProAnalyse.headlineLenghtGood', ' Headline has a good length.').' '.$addText,
                                'IconMess' => '3',
                                'HelpLink' => 'headlineLenghtGood'

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
                        'HelpLinnk' => 'HeadlineStructureError'

                    )
                ));
                $headlineStructureError = 1;
                $this->updateRules(1);
            } elseif ($hsKey == 1 && $headlineStructureError == 0) {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                        'Content' =>_t('SeoHeroToolProAnalyse.NoHeadlineStructureError', 'The Headline Structure does not contain obvious Errors.'),
                        'IconMess' => '3',
                        'HelpLink' => 'NoHeadlineStructureError'

                    )
                ));
                $this->updateRules(3);
            }
        }
        return array(
            'Headline' => _t('SeoHeroToolPro.Headlines', 'Headlines'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    private function checkSibling($object)
    {
        foreach ($this->nextElementSibling($object)->childNodes as $morenodes) {
            return $morenodes;
        }

        return false;
    }

    private function nextElementSibling($node)
    {
        while ($node && ($node = $node->nextSibling)) {
            if ($node instanceof DOMElement) {
                break;
            }
        }
        return $node;
    }

    private function checkNodeValue($object)
    {
        $innerHTML = $this->dom->saveHTML($object);
        $innerText = trim(strip_tags($innerHTML));
        if (strlen($innerText) >= 1) {
            return $innerText;
        } else {
            foreach ($object->childNodes as $child) {
                $innerElement = $this->dom->saveHTML($child);
                $imgneedle = '<img';
                $imgres = stripos($innerElement, $imgneedle);
                $svgneedle = '<svg';
                $svgres = stripos($innerElement, $svgneedle);
                $resend = false;
                if ($imgres !== false) {
                    $resend = strpos($innerElement, '>', $imgres);
                    $resstart = $imgres;
                } elseif ($svgres !== false) {
                    $resend = strpos($innerElement, '>', $svgres);
                    $resstart = $svgres;
                }
                if ($resend) {
                    return htmlentities(substr($innerElement, $resstart, $resend+1-$resstart));
                }
            }
            #svg und img noch abfragen
            return false;
        }

        /*elseif (is_object($object->nextSibling)  && $sibling = $this->checkSibling($object)) {
            print_r($object);
            if (strlen(trim($sibling->nodeValue)) >= 1) {
                return $sibling->nodeValue;
            } else {
                return false;
            }
        } else {
            return false;
        }*/
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
            $linkName = $this->checkNodeValue($link);
            $linkline =    '<code class="html tag start-tag">'.htmlentities($this->dom->saveHTML($link)).'</code>';

            if (!$linkName) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                      'Content' =>
                      sprintf(
                          _t('SeoHeroToolProAnalyse.LinkNoAttrTitleAndNoLinkDescription',
                              'Please check the following area for a Link with an empty "a" tag<em>%s</em>'),
                          $linkline
                      ),
                          'IconMess' => '1',
                          'HelpLink' => 'LinkNoAttrTitleAndNoLinkDescription'
                      )
                ));
                $this->updateRules(1);
            }

            if (!$link->hasAttribute('title')) {
                if ($linkName == "") {
                    $linkNoAttrTitle = $linkline;
                } else {
                    $linkNoAttrTitle = $linkName;
                }
                $UnsortedListEntries->push(new ArrayData(
                      array(
                          'Content' =>
                          sprintf(
                              _t('SeoHeroToolProAnalyse.LinkNoAttrTitle',
                                  'The Link %s has no title attribute'),
                              $linkNoAttrTitle
                          ),
                              'IconMess' => '1',
                              'HelpLink' => 'LinkNoAttrTitle'
                          )
                    ));





                $linkError = 1;
                $this->updateRules(1);
            } else {
                $linkTitle = $link->getAttribute('title');
                if ($linkName == $linkTitle) {
                    $linkSameTitleNameMessage.= $linkName.'<br/>';
                }
            }
        }

        if ($linkError == 0 && $documentLinks->length > 0) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.AllLinksHaveTitle', 'All links have a title attribute'),
                    'IconMess' => '3',
                    'HelpLink' => 'AllLinksHaveTitle'
              )
            ));
            $this->updateRules(3);
        }
        if ($linkSameTitleNameMessage != '') {
            $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.LinksWithSameTitleAndName', 'There are links with the same Name and Title Attribute. Those are these links:').'<br/>'.$linkSameTitleNameMessage,
                    'IconMess' => '2',
                    'HelpLink' => 'LinksWithSameTitleAndName'
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
                'IconMess' => '2',
                'HelpLink' => 'FoundNoStrongElements'
              )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundOneOrMoreStrongElements', 'Found one or more strong elements on website (B / STRONG).'),
                'IconMess' => '3',
                'HelpLink' => 'FoundOneOrMoreStrongElements'
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
                    'IconMess' => '3',
                    'HelpLink' => 'AllImagesWithAltTag'
                  )
                ));
                $this->updateRules(3);
            } elseif ($imagesWithoutAltTag >= 1) {
                $UnsortedListEntries->push(new ArrayData(
                    array(
                      'Content' => sprintf(_t('SeoHeroToolProAnalyse.ImagesWithoutAltTagMessage', '%1$d out of %2$d Images are not having alt-Tags. The images are the following:').' <br/>'.$message, $imagesWithoutAltTag, $domImageCount),
                      'IconMess' => '1',
                      'HelpLink' => 'ImagesWithoutAltTagMessage'
                    )
                ));
                $this->updateRules(1);
            }
            if ($imagesWithSameAltTagAndFilename == 0) {
                $UnsortedListEntries->push(new ArrayData(
                array(
                  'Content' => _t('SeoHeroToolProAnalyse.AllImagesWithDiffferentFilenameAndAltTag', 'All Images have for filename and alt-tag different values.'),
                  'IconMess' => '3',
                  'HelpLink' => 'AllImagesWithDiffferentFilenameAndAltTag'

                )
              ));
                $this->updateRules(3);
            } elseif ($imagesWithSameAltTagAndFilename >= 1) {
                $UnsortedListEntries->push(new ArrayData(
                array(
                  'Content' => sprintf(_t('SeoHeroToolProAnalyse.ImagesWithoutDifferentFilenameAndAltTag', '%1$d out of %2$d Images have the same alt-tag and filename. Those images are the following:').' <br/>'.$sameNameMessage, $imagesWithoutAltTag, $domImageCount),
                  'IconMess' => '2',
                  'HelpLink' => 'ImagesWithoutDifferentFilenameAndAltTag'
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
                    'HelpLink' => 'NoImagesFound'
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
        $nonDocumentError = 0;
        if ($this->siteRunsLocally) {
            $UnsortedListEntries->push(new ArrayData(
          array(
                'Content' => _t('SeoHeroToolProAnalyse.SiteRunsLocally', 'The website runs locally. No W3C Validation possible.'),
                'IconMess' => '2',
                'HelpLink' => 'SiteRunsLocally'
              )
            ));
            $this->updateRules(2);

            return array(
              'Headline' => _t('SeoHeroToolProAnalyse.W3CResult', 'W3C Validator Result'),
              'UnsortedListEntries' => $UnsortedListEntries);
        }
        if (!$this->getAPIRequest('W3C')) {
            $results = SeoHeroToolProW3CValidator::checkData($URL);
            $messages = $results->messages;
            if (isset($messages[0]->type) && $messages[0]->type == 'non-document-error') {
                $nonDocumentError = 1;
            }
            $error = 0;
            $warning = 0;
            foreach ($messages as $mes) {
                if ($mes->type == 'error') {
                    $error++;
                } elseif ($mes->type == 'warning') {
                    $warning++;
                }
            }
            $errorsAndWarnings = array('error'=>$error,'warning'=>$warning);
            $this->setAPIRequestValue('W3C', $errorsAndWarnings);
        }
        $sessionVal = $this->getAPIRequestValue('W3C');
        $this->W3CTimeStamp = date("d.m.Y H:i:s", $sessionVal[0]);
        $W3CResults = $sessionVal[1];
        $foundHTMLErrors = $sessionVal[1]['error'];
        $foundHTMLWarnings = $sessionVal[1]['warning'];


        /*
          If the site is hosted locally there will be a  "Name or service not known message"
         */
        if ($nonDocumentError == 1) {
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => _t('SeoHeroToolProAnalyse.W3CNon-Document-Error', 'The Document can not be scanned, maybe the website runs locally?'),
                  'IconMess' => '2',
                  'HelpLink' => 'W3CNon-Document-Error'
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
                  'HelpLink' => 'W3CNNoErrorsAndWarning'
                )
              ));
            $this->updateRules(3);
        } elseif ($nonDocumentError == 0) {
            if ($foundHTMLErrors == 1) {
                $messageFoundHTMLErrors = _t('SeoHeroToolPro.W3CErrorSingular', 'one HTML error');
            } elseif ($foundHTMLErrors > 1) {
                $messageFoundHTMLErrors = _t('SeoHeroToolPro.W3CErrorPlural', 'several HTML errors');
            } else {
                $messageFoundHTMLErrors = _t('SeoHeroToolPro.W3CErrorNone', 'no HTML errors');
            }

            if ($foundHTMLWarnings == 1) {
                $messageFoundHTMLWarnings = _t('SeoHeroToolPro.W3CWarningSingular', 'one HTML warning');
            } elseif ($foundHTMLWarnings > 1) {
                $messageFoundHTMLWarnings = _t('SeoHeroToolPro.W3CWarningPlural', 'several HTML warnings');
            } else {
                $messageFoundHTMLWarnings = _t('SeoHeroToolPro.W3CWarningNone', 'no HTML warnings');
            }
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => sprintf(
                _t('SeoHeroToolProAnalyse.W3CCountMessage',
                    'Es wurden auf der Seite %1$s und %2$s gefunden'),
                $messageFoundHTMLWarnings, $messageFoundHTMLErrors),
                  'IconMess' => '1',
                  'HelpLink' => 'W3CCountMessage'
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
                'IconMess' => '2',
                'HelpLink' => 'NoSkipToMainContentFound'
              )
            ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
            array(
              'Content' => _t('SeoHeroToolProAnalyse.SkipToMainContentFound', 'Skip to main content found on page.'),
              'IconMess' => '3',
              'HelpLink' => 'SkipToMainContentFound'
            )
          ));
            $this->updateRules(3);
        }
        return array(
          'Headline' => _t('SeoHeroToolPro.SkipToMainContent', 'Skip to main content'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }

    /**
    * Check that given URL is valid and exists.
     * @param string $url URL to check
     * @return bool TRUE when valid | FALSE anyway
     */
      private function urlExists($url)
      {
          // Remove all illegal characters from a url
          $url = filter_var($url, FILTER_SANITIZE_URL);

          // Validate URI
          if (filter_var($url, FILTER_VALIDATE_URL) === false
              // check only for http/https schemes.
              || !in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), ['http','https'], true)
          ) {
              return false;
          }

          // Check that URL exists
          $file_headers = @get_headers($url);
          return !(!$file_headers || $file_headers[0] === 'HTTP/1.1 404 Not Found');
      }


    /*
    The function checkAMPLink() checks if there is an link to an amp Page
    */
    private function checkAMPLink()
    {
        $UnsortedListEntries = new ArrayList();
        $search = 'rel="amphtml"';
        if (!strpos(strtolower($this->pageHTML), $search)) {
            $UnsortedListEntries->push(new ArrayData(
            array(
              'Content' => _t('SeoHeroToolProAnalyse.NoAMPLinkFound', 'No AMP Link found on page.'),
              'IconMess' => '2',
              'HelpLink' => 'NoAMPLinkFound'
            )
          ));
            $this->updateRules(2);
        } else {
            $UnsortedListEntries->push(new ArrayData(
            array(
              'Content' => _t('SeoHeroToolProAnalyse.AMPLinkFound', 'AMP Link found on page.'),
              'IconMess' => '3',
              'HelpLink' => 'NoAMPLinkFound'
            )
          ));
            $this->updateRules(3);
            //überprüfe ob die SEite existiert
            $searchPattern = "/<link rel=\"amphtml\" href=\"([^<]*)\">/s";
            preg_match_all($searchPattern, $this->pageHTML, $aMatch);

            $ampPage = $aMatch[1][0];
            if ($this->urlExists($ampPage)) {
                $UnsortedListEntries->push(new ArrayData(
                array(
                'Content' => _t('SeoHeroToolProAnalyse.AMPPageFound', 'AMP Page exists.'),
                'IconMess' => '3',
                'HelpLink' => 'NoAMPLinkFound'
              )
            ));
                $this->updateRules(3);
            } else {
                $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.NoAMPPageFound', 'The AMP Page dos not exists.'),
                'IconMess' => '1',
                'HelpLink' => 'NoAMPLinkFound'
              )
            ));
                $this->updateRules(1);
            }
        }
        return array(
          'Headline' => _t('SeoHeroToolPro.AMPLink', 'AMP Link found'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }

    /*
    The function checkAMPLinkPageExists() checks if there an amp link in head if the page exists
    */
    private function checkAMPLinkPageExists()
    {
        $UnsortedListEntries = new ArrayList();
    }


    /*
      The function checkForUsefulFiles() checks if in the main folder of this project there is a robots.txt and sitemap.xml file and if they are accessible.
     */
    private function checkForUsefulFiles()
    {
        $UnsortedListEntries = new ArrayList();
        if (isset($_SERVER['HTTPS'])) {
            $server = 'https://'.$_SERVER['SERVER_NAME'];
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.SiteIsHTTPS', 'Site can be reached via HTTPS.'),
                'IconMess' => '3',
                'HelpLink' => 'SiteIsHTTPS'
              )
            ));
            $this->updateRules(3);
        } else {
            $server = 'http://'.$_SERVER['SERVER_NAME'];
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.SiteIsHTTP', 'Site can be reached via HTTP. HTTPS would be better.'),
                'IconMess' => '2',
                'HelpLink' => 'SiteIsHTTP'
              )
            ));
            $this->updateRules(2);
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
                'IconMess' => '3',
                'HelpLink' => 'FoundRobotstxt'
              )
            ));
            $this->updateRules(3);
        } elseif (strpos($curlResponeArray[0], ' 404')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundNoRobotstxt', 'No Robots.txt existing. HTTP Response is ').': '.$curlResponeArray[0],
                'IconMess' => '1',
                'HelpLink' => 'FoundNoRobotstxt'
              )
            ));
            $this->updateRules(1);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.ProblemWithRobotstxt', 'Robots.txt file was not accessible. Please check this as this files helps searchengines. - The HTTP Response is').': '.$curlResponeArray[0],
                'IconMess' => '2',
                'HelpLink' => 'ProblemWithRobotstxt'
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
                'IconMess' => '3',
                'HelpLink' => 'FoundSitemapXML'
              )
            ));
            $this->updateRules(3);
        } elseif (strpos($chsmResponseArray[0], ' 404')) {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.FoundNoSitemapXML', 'No Sitemap.xml existing. HTTP Response is ').': '.$chsmResponseArray[0],
                'IconMess' => '1',
                'HelpLink' => 'FoundNoSitemapXML'
              )
            ));
            $this->updateRules(1);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.ProblemWithSitemapXML', 'Sitemap.xml file was not accessible. Please check this as this files helps searchengines. - The HTTP Response is').': '.$chsmResponseArray[0],
                'IconMess' => '2',
                'HelpLink' => 'ProblemWithSitemapXML'
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
                'IconMess' => '2',
                'HelpLink' => 'SitesRunsLocally'
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
              'IconMess' => '3',
              'HelpLink' => 'StructuredDataFound'
            )
          ));
            $this->updateRules(3);
        } else {
            $UnsortedListEntries->push(new ArrayData(
              array(
                'Content' => _t('SeoHeroToolProAnalyse.NoStructuredDataFound', 'No structured Data found.'),
                'IconMess' => '2',
                'HelpLink' => 'NoStructuredDataFound'
              )
            ));
            $this->updateRules(2);
        }
        return array(
          'Headline' => _t('SeoHeroToolProAnalyse.StructuredData', 'Structured Data'),
          'UnsortedListEntries' => $UnsortedListEntries
        );
    }

    private function checkHTTPHeader($URL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        $curlResponse = curl_exec($ch);
        curl_close($ch);
    }

    private function checkPageSpeed($URL)
    {
        $UnsortedListEntries = new ArrayList();
        $pageSpeedInformationCounter = 0;
        if ($this->siteRunsLocally) {
            $UnsortedListEntries->push(new ArrayData(
            array(
                  'Content' => _t('SeoHeroToolProAnalyse.SiteRunsLocallyPageSpeed', 'The website runs locally. No PageSpeed check possible.'),
              'IconMess' => '2',
              'HelpLink' => 'SiteRunsLocallyPageSpeed'
            )
          ));
            $this->updateRules(2);

            return array(
            'Headline' => _t('SeoHeroToolPro.PageSpeed', 'PageSpeed Result'),
            'UnsortedListEntries' => $UnsortedListEntries);
        }
        $PageSpeedAPI = Config::inst()->get('SeoHeroToolPro', 'PageSpeedAPI');
        if ($PageSpeedAPI != '') {
            if (!$this->getAPIRequest('PageSpeed')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url='.urlencode($URL).'&strategy=desktop');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


                $curlResponse = curl_exec($ch);
                curl_close($ch);
                $decodedInformation = json_decode($curlResponse);
                if ($decodedInformation->error->code == '500') {
                    $scoreValue = array('desktop'=>0,'mobile'=>0,'error'=>500);
                    $this->setAPIRequestValue('PageSpeed', $scoreValue);
                } else {
                    $desktopScore = $decodedInformation->ruleGroups->SPEED->score;
                    $chmob = curl_init();
                    curl_setopt($chmob, CURLOPT_URL, 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url='.urlencode($URL).'&strategy=mobile');
                    curl_setopt($chmob, CURLOPT_RETURNTRANSFER, 1);
                    $mobResponse = curl_exec($chmob);
                    curl_close($chmob);
                    $decodedMobInformation = json_decode($mobResponse);
                    $mobileScore = $decodedMobInformation->ruleGroups->SPEED->score;
                    $scoreValue = array('desktop'=>$desktopScore,'mobile'=>$mobileScore);
                    $this->setAPIRequestValue('PageSpeed', $scoreValue);
                }
            }

            $pageSpeedInformation = $this->getAPIRequestValue('PageSpeed');
            $this->pageSpeedTimeStamp = date("d.m.Y H:i:s", $pageSpeedInformation[0]);
            if (isset($pageSpeedInformation[1]['error']) && $pageSpeedInformation[1]['error'] == '500') {
                $UnsortedListEntries->push(new ArrayData(
                array(
                'Content' => _t('SeoHeroToolProAnalyse.PageSpeedNoResults', 'The response delivers an Error Code 500. Please check site manually!'),
                'IconMess' => '1',
                'HelpLink' => 'PageSpeedNoResults'
                )
              ));
                $this->updateRules(1);
                return array(
                'Headline' => _t('SeoHeroToolPro.PageSpeed', 'PageSpeed Result'),
                'UnsortedListEntries' => $UnsortedListEntries);
            }
            $desktopValue = $pageSpeedInformation[1]['desktop'];
            $mobileValue = $pageSpeedInformation[1]['mobile'];
            $pageSpeedInformationCounter++;
            if ($desktopValue >= 85) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.GoodDesktopPageSpeed', 'The PageSpeed is quite good.').' '.$desktopValue,
                    'IconMess' => '3',
                    'HelpLink' => 'GoodDesktopPageSpeed'
                  )
                ));
                $this->updateRules(3);
            } elseif ($desktopValue < 85 && $desktopValue > 60) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.MediocreDesktopPageSpeed', 'The PageSpeed is mediocre. This could be probably done better.').' '.$desktopValue,
                    'IconMess' => '2',
                    'HelpLink' => 'MediocreDesktopPageSpeed'
                  )
                ));
                $this->updateRules(2);
            } else {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.BadDesktopPageSpeed', 'The PageSpeed is far from good. Please check how you can enhance this value.').' '.$desktopValue,
                    'IconMess' => '1',
                    'HelpLink' => 'BadDesktopPageSpeed'
                  )
                ));
                $this->updateRules(1);
            }

            if ($mobileValue >= 85) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.GoodMobilePageSpeed', 'The PageSpeed for mobile devices is quite good.').' '.$mobileValue,
                    'IconMess' => '3',
                    'HelpLink' => 'GoodMobilePageSpeed'
                  )
                ));
                $this->updateRules(3);
            } elseif ($mobileValue < 85 && $mobileValue > 60) {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.MediocreMobilePageSpeed', 'The PageSpeed for mobile devices is mediocre. This could be probably done better.').' '.$mobileValue,
                    'IconMess' => '2',
                    'HelpLink' => 'MediocreMobilePageSpeed'
                  )
                ));
                $this->updateRules(2);
            } else {
                $UnsortedListEntries->push(new ArrayData(
                  array(
                        'Content' => _t('SeoHeroToolProAnalyse.BadMobilePageSpeed', 'The PageSpeed for mobile devices is far from good. Please check how you can enhance this value.').' '.$mobileValue,
                    'IconMess' => '1',
                    'HelpLink' => 'BadMobilePageSpeed'
                  )
                ));
                $this->updateRules(1);
            }
        }
        if ($pageSpeedInformationCounter == 0) {
            if ($PageSpeedAPI != '') {
                $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.PageSpeedNoResultsNoError', 'The Document can not be scanned, but no error was found in the return'),
                    'IconMess' => '2',
                    'HelpLink' => 'PageSpeedNoInformation'
                  )
              ));
                $this->updateRules(2);
            } else {
                $UnsortedListEntries->push(new ArrayData(
              array(
                    'Content' => _t('SeoHeroToolProAnalyse.PageSpeedKeyNotSet', 'The Document can not be scanned as the PageSpeed API-key is not set'),
                    'IconMess' => '2',
                    'HelpLink' => 'PageSpeedNoInformation'
                  )
              ));
                $this->updateRules(2);
            }
        }

        return array(
            'Headline' => _t('SeoHeroToolPro.PageSpeed', 'PageSpeed'),
            'UnsortedListEntries' => $UnsortedListEntries);
    }

    /*
      Function getRequest checks if a given value shall be requested again or not. Returns true or false
     */
    private function getAPIRequest($APIFunction)
    {
        $sessionVal = Session::get($this->pageID.'_'.$APIFunction);
        if (isset($sessionVal) && $sessionVal != '') {
            if ($sessionVal[0] < time()-30) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    private function getAPIRequestValue($APIFunction)
    {
        $sessionVal = Session::get($this->pageID.'_'.$APIFunction);
        if (isset($sessionVal) && $sessionVal != '') {
            return $sessionVal;
        }
    }

    private function setAPIRequestValue($APIFunction, $Value)
    {
        Session::set($this->pageID.'_'.$APIFunction, array(time(),$Value));
    }

    private function resetAPIRequestValue($APIFunction)
    {
        if ($this->getAPIRequest($APIFunction)) {
            Session::clear($this->pageID.'_'.$APIFunction);
        }
    }
}
