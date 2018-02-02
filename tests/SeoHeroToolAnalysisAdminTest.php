<?php
/*
  The class SeoHeroToolAnalysisAdminTest run checks against the SeoHeroToolAnalysisAdmin if no page is needed for the check.
  All tests where a page is needed will be handled in the SeoHeroToolAnalysisAdminFunctionalTest Class.
 */
class SeoHeroToolAnalysisAdminTest extends SapphireTest
{
    /**
    * Call protected/private method of a class.
    *
    * @param object &$object    Instantiated object that we will run method on.
    * @param string $methodName Method name to call
    * @param array  $parameters Array of parameters to pass into method.
    *
    * @return mixed Method return.
    */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }


    /*
      Test for function checkTitle. Tests that this returns an array with the correct datas. Test the return for a normal case and an empty title.
     */
    public function testCheckTitle()
    {
        $seotest = new SeoHeroToolAnalysisAdmin();
        $seotest->pageTitle = "test";
        $return = $this->invokeMethod($seotest, 'checkTitle');
        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkTitle is not an array");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find Title Description");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find Title Icon");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find Title HelpLink");

        $seotest->pageTitle = '';
        $return = $this->invokeMethod($seotest, 'checkTitle');
        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkTitle is not an array for an empty Pagetitle");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find Title Description for an empty Pagetitle");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find Title Icon for an empty Pagetitle");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find Title HelpLink for an empty Pagetitle");
    }

    /*
      Test for the function updateRules. Checks that this functions counts correct. Test the return for different cases and also the combined result.
     */
    public function testUpdateRules()
    {
        $seotest = new SeoHeroToolAnalysisAdmin();
        $this->invokeMethod($seotest, 'updateRules', [1]);
        $this->assertTrue($seotest->rules['total'] == 1, "rules['total'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["total"]);
        $this->assertTrue($seotest->rules['wrong'] == 1, "rules['wrong'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["wrong"]);
        $this->assertTrue($seotest->rules['notice'] == 0, "rules['notice'] has not the expected value. Expected to be 0. Value is: ".$seotest->rules['notice']);
        $this->assertTrue($seotest->rules['good'] == 0, "rules['good'] has not the expected value. Expected to be 0. Value is: ".$seotest->rules['good']);

        $this->invokeMethod($seotest, 'updateRules', [2]);
        $this->assertTrue($seotest->rules['total'] == 2, "rules['total'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["total"]);
        $this->assertTrue($seotest->rules['wrong'] == 1, "rules['wrong'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["wrong"]);
        $this->assertTrue($seotest->rules['notice'] == 1, "rules['notice'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules['notice']);
        $this->assertTrue($seotest->rules['good'] == 0, "rules['good'] has not the expected value. Expected to be 0. Value is: ".$seotest->rules['good']);

        $this->invokeMethod($seotest, 'updateRules', [3]);
        $this->assertTrue($seotest->rules['total'] == 3, "rules['total'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["total"]);
        $this->assertTrue($seotest->rules['wrong'] == 1, "rules['wrong'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules["wrong"]);
        $this->assertTrue($seotest->rules['notice'] == 1, "rules['notice'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules['notice']);
        $this->assertTrue($seotest->rules['good'] == 1, "rules['good'] has not the expected value. Expected to be 1. Value is: ".$seotest->rules['good']);
    }

    /*
      Test for the function checkWordCount. Check this functions with normal values, string and empty value.
     */
    public function testCheckWordCount()
    {
        $seotest = new SeoHeroToolAnalysisAdmin();
        $seotest->wordCount = 20;
        $return = $this->invokeMethod($seotest, 'checkWordCount');

        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkWordCount is not an array a cound of 20 words.");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find Title Description for 20 words in checkWordCount");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find Title Icon for 20 words in checkWordCount");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find Title HelpLink for 20 words in checkWordCount");

        $seotest->wordCount = 0;
        $return = $this->invokeMethod($seotest, 'checkWordCount');
        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkWordCount is not an array a cound of 0 words.");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find Title Description for 0 words in checkWordCount");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find Title Icon for 0 words in checkWordCount");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find Title HelpLink for 0 words in checkWordCount");
    }

    public function testGetAPIRequest()
    {
        $seotest = new SeoHeroToolAnalysisAdmin();
        $function = 'testfunction';
        $return = $this->invokeMethod($seotest, 'getAPIRequest', [$function]);
        debug::show($return);
    }
}
