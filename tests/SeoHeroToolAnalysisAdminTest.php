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
}
