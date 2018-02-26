<?php
/*
  The class SeoHeroToolAnalysisAdminTest run checks against the SeoHeroToolAnalysisAdmin if a page is needed for the check.
  All tests where no page is needed will be handled in the SeoHeroToolAnalysisAdminTest Class.
 */
class SeoHeroToolAnalysisAdminFunctionalTest extends FunctionalTest
{
    protected static $fixture_file = 'SeoHeroToolAnalysisAdminFunctionalTest.yml';
    public static $use_draft_site = true;

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
      Test case for checkMeta Function. Checks the return value of this function for a normal case and an empty Meta Description.
     */
    public function testCheckMeta()
    {
        $Page = $this->objFromFixture('Page', 'home');
        $Page->MetaDescription = 'This is a Test Case';
        $seotest = new SeoHeroToolAnalysisAdmin();
        $return =  $this->invokeMethod($seotest, 'checkMeta', [$Page]);

        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkMeta is not an array");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find checkMeta Description within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find checkMeta Icon within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find checkMeta HelpLink within the returned value");

        $Page->MetaDescription = '';
        $return = $this->invokeMethod($seotest, 'checkMeta', [$Page]);
        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkMeta is not an array for an empty MetaDescription.");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find checkMeta Description within the returned value for an empty MetaDescription.");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find checkMeta Icon within the returned value for an empty MetaDescription.");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find checkMeta HelpLink within the returned value for an empty MetaDescription.");
    }

    /*
      Test case for checkURL Function. Checking home and someURL and that there is an array response.
     */
    public function testCheckURL()
    {
        $Page = $this->objFromFixture('Page', 'home');
        $seotest = new SeoHeroToolAnalysisAdmin();
        $return = $this->invokeMethod($seotest, 'checkURL', [$Page]);

        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkURL is not an array for home url");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find checkURL Description within the returned value for home url");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find checkURL Icon within the returned value for home url");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find checkURL HelpLink within the returned value for home url");

        $Page = $this->objFromFixture('Page', 'someURL');
        $return = $this->invokeMethod($seotest, 'checkURL', [$Page]);

        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return checkURL is not an array");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find checkURL Description within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find checkURL Icon within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find checkURL HelpLink within the returned value");
    }

    /*
      Test case for Directory Depth function "checkLinkDirectoryDepth"
     */
    public function testCheckLinkDirectoryDepth()
    {
        $Page = $this->objFromFixture('Page', 'someURL');
        $seotest = new SeoHeroToolAnalysisAdmin();
        $return = $this->invokeMethod($seotest, 'checkLinkDirectoryDepth', [$Page]);

        $this->assertTrue(is_array($return) && array_key_exists('UnsortedListEntries', $return), "Return of checkLinkDirectoryDepth is not an array");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->Content), "Cant find checkLinkDirectoryDepth Description within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->IconMess), "Cant find checkLinkDirectoryDepth Icon within the returned value");
        $this->assertTrue(isset($return['UnsortedListEntries']->first()->HelpLink), "Cant find checkLinkDirectoryDepth HelpLink within the returned value");
    }
}
