<?php
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
      Test for function checkTitle. Tests that this returns an array with the correct datas.
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
    }

    /*
      Test for the function updateRules. Checks that this functions counts correct.
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
}
