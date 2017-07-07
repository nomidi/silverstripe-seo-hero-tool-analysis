<?php

class SeoHeroToolProAnalyseKeyword
{
    private $keywordRules = array('good' => 0, 'notice' => 0, 'wrong' => 0, 'total' => 0);

    public $dom;
    public $ContentID = false;
    public $ContentIDValue = '';
    public $ContentIDFirstParagraph = '';

    private function updateKeywordRules($type = 3)
    {
        $this->keywordRules['total']++;
        switch ($type) {
          case '1':
            $this->keywordRules['wrong']++;
            break;
          case '2':
            $this->keywordRules['notice']++;
            break;
          default:
            $this->keywordRules['good']++;
        }
    }

    public function getKeywordResults()
    {
        return $this->keywordRules;
    }

    private function replaceSpecialCharacters($keyword)
    {
        $keyword = str_replace(' ', '-', $keyword);
        return $keyword;
    }

    private function getContentIDValue($contentID)
    {
        $this->ContentID = $contentID;
        $this->ContentIDValue  = strtolower($this->dom->getElementById($contentID)->nodeValue);
        $domXPath = new DOMXPath($this->dom);
        $contentConatiner = $domXPath->query('//div[@id="'.$contentID.'"]');

        $dom2 = new DOMDocument;
        $dom2->appendChild($dom2->importNode($contentConatiner->item(0), true));
        $this->ContentIDFirstParagraph = $dom2->getElementsByTagName('p')->item(0)->nodeValue;
    }

    public function checkKeywords($Page, $dom)
    {
        $KeywordEntries = new Arraylist();
        $this->dom = $dom;
        $URLSegment = $Page->URLSegment;
        $contentID = Config::inst()->get('SeoHeroToolPro', 'contentID');
        if ($contentID) {
            $this->getContentIDValue($contentID);
        }
        $title = strtolower($Page->Title);
        $metaDescription = strtolower($Page->BetterMetaDescription());
        $pageImages = $dom->getElementsByTagName('img');
        $pageBody = $dom->getElementsByTagName('body')->item(0)->nodeValue;
        $pageIsHomepage = false;
        $noImagesOnPage = false;
        $featuredKeywords = $Page->FeaturedKeyword;
        if ($featuredKeywords) {
            if (strpos($featuredKeywords, ',')) {
                $keywordArray = explode(',', $featuredKeywords);
            } else {
                $keywordArray = array($featuredKeywords);
            }
            foreach ($keywordArray as $keyword) {
                if (trim($keyword) != '') {
                    $keywordOutput = trim($keyword);
                    $keyword = strtolower(trim($keyword));

                    # URL Check
                    if (strpos(strtolower($URLSegment), $this->replaceSpecialCharacters($keyword)) === false) {
                        if ($URLSegment == 'home' && $pageIsHomepage === false) {
                            $pageIsHomepageURLcheck = true;
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordHomeURL', 'Home URL. Can not find Keywords which is fine on Homepage with URLSegment "home".'),
                                'IconMess' => '2'
                              )
                            ));
                            $this->updateKeywordRules(2);
                        } elseif ($pageIsHomepage === false) {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordNotInUrl', 'Keyword not found in URL for').': '.$keywordOutput,
                                'IconMess' => '1'
                              )
                            ));
                            $this->updateKeywordRules(1);
                        }
                    } else {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordInURL', 'Keyword found in URL for').': '.$keywordOutput,
                            'IconMess' => '3'
                          )
                        ));
                        $this->updateKeywordRules(3);
                    }
                    # Title check
                    if (strpos($title, $keyword) === false) {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordNotInTitle', 'Keyword not found in Page Title for').': '.$keywordOutput,
                            'IconMess' => '1'
                          )
                        ));
                        $this->updateKeywordRules(1);
                    } else {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordInTitle', 'Keyword found in the Page Title for').': '.$keywordOutput,
                            'IconMess' => '3'
                          )
                        ));
                        $this->updateKeywordRules(3);
                    }
                    # Meta Check
                    $keywordOccurence = intval(substr_count($metaDescription, $keyword));
                    if ($keywordOccurence == 0) {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordNotInMetaDescription', 'Keyword not found in Meta Description for').': '.$keywordOutput,
                            'IconMess' => '1'
                          )
                        ));
                        $this->updateKeywordRules(1);
                    } elseif ($keywordOccurence == 1) {
                        $KeywordEntries->push(new ArrayData(
                        array(
                          'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordFoundOnceInMetaDescription', 'Found Keyword once in Meta Description for').': '.$keywordOutput,
                          'IconMess' => '3'
                        )
                      ));
                        $this->updateKeywordRules(3);
                    } else {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => sprintf(_t('SeoHeroToolProAnalyseKeyword.KeywordFoundMoreThanOnceInMetaDescription', 'Found Keyword %1$d times in Meta Description for').': '.$keywordOutput, $keywordOccurence),
                            'IconMess' => '3'
                          )
                        ));
                        $this->updateKeywordRules(3);
                    }
                    #Content Check
                    $keywordInContent = 0;
                    if ($this->ContentID) {
                        $keywordInContent = substr_count($this->ContentIDValue, $keyword);
                    } else {
                        $keywordInContent = substr_count(strtolower($pageBody), $keyword);
                    }
                    if ($keywordInContent == 0) {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyse.KeywordNotInPageContent', 'Keyword was not found in Page Content for Keyword').': '.$keywordOutput,
                            'IconMess' => '1'
                          )
                        ));
                        $this->updateKeywordRules(1);
                    } else {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => sprintf(_t('SeoHeroToolProAnalyse.KeywordFoundInPageContent', 'Keyword was found %1$d times on Page'), $keywordInContent).': '.$keywordOutput,
                            'IconMess' => '3'
                          )
                        ));
                        $this->updateKeywordRules(3);
                    }
                    # keyword in first paragraph
                    $keywordInFirstParagraph = 0;
                    if ($this->ContentID && $this->ContentIDFirstParagraph) {
                        $keywordInFirstParagraph = substr_count($this->ContentIDFirstParagraph, $keyword);
                    } else {
                        $keywordInFirstParagraph = substr_count(strtolower($pageBody), $keyword);
                    }
                    if ($keywordInFirstParagraph == 0 && $keywordInContent > 0) {
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyse.KeywordNotInFirstParagraph', 'Keyword was not found in first Paragraph of the content for Keyword').': '.$keywordOutput,
                            'IconMess' => '2'
                          )
                        ));
                        $this->updateKeywordRules(2);
                    }

                    # IMAGE Check
                    if ($pageImages->length >= 1) {
                        $keyImgFile = 0;
                        $keyImgAlt = 0;
                        foreach ($pageImages as $image) {
                            if (strpos($image->getAttribute('alt'), $keyword) !== false) {
                                $keyImgAlt++;
                            }
                            if (strpos(substr(strrchr($image->getAttribute('src'), "/"), 1), $keyword) !== false) {
                                $keyImgFile++;
                            }
                        }
                        # Occurence in Imagename
                        if ($keyImgFile == 0) {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordNotInImage', 'Found Keyword not in Image Name for').': '.$keywordOutput,
                                'IconMess' => '1'
                              )
                            ));
                            $this->updateKeywordRules(1);
                        } elseif ($keyImgFile == 1) {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.FoundKeywordInOneImage', 'Found Keyword in one Image Name for').': '.$keywordOutput,
                                'IconMess' => '3'
                              )
                            ));
                            $this->updateKeywordRules(3);
                        } else {
                            $KeywordEntries->push(new ArrayData(
                            array(
                              'Content' => sprintf(_t('SeoHeroToolProAnalyseKeyword.FoundKeywordInSeveralImages', 'Found Keyword %1$d times in Image Names for').': '.$keywordOutput, $keyImgFile),
                              'IconMess' => '3'
                            )
                          ));
                            $this->updateKeywordRules(3);
                        }
                        # Occurence in Image Alt Tag
                        if ($keyImgAlt == 0) {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.KeywordNotInImageAltTag', 'Found Keyword not in Image Alt-Tag for').': '.$keywordOutput,
                                'IconMess' => '1'
                              )
                            ));
                            $this->updateKeywordRules(1);
                        } elseif ($keyImgAlt == 1) {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => _t('SeoHeroToolProAnalyseKeyword.FoundKeywordInOneImageAltTag', 'Found Keyword in one Image Alt Tag for').': '.$keywordOutput,
                                'IconMess' => '3'
                              )
                            ));
                            $this->updateKeywordRules(3);
                        } else {
                            $KeywordEntries->push(new ArrayData(
                              array(
                                'Content' => sprintf(_t('SeoHeroToolProAnalyseKeyword.FoundKeywordInSeveralImageAltTags', 'Found Keyword %1$d times in Image Alt Tags for').': '.$keywordOutput, $keyImgFile),
                                'IconMess' => '3'
                              )
                            ));
                            $this->updateKeywordRules(3);
                        }
                    } elseif ($noImagesOnPage === false) {
                        $noImagesOnPage = true;
                        $KeywordEntries->push(new ArrayData(
                          array(
                            'Content' => _t('SeoHeroToolProAnalyseKeyword.NoImagesFound', 'No Images found on this site.'),
                            'IconMess' => '2'
                          )
                        ));
                        $this->updateKeywordRules(2);
                    }
                }
            }
        }
        return array(
        'Headline' => _t('SeoHeroToolProAnalyseKeyword.Keyword', 'Keyword'),
        'KeywordEntries' => $KeywordEntries,
      );
    }
}
