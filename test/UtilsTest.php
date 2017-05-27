<?php 

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase {}
}

class UtilsTest extends \PHPUnit_Framework_TestCase{
    function testCookie(){
        $this->assertNull(cookie('foo'));
    }
    function testSession(){
        $this->assertNull(session('foo'));
    }
    /**
     * @depends testCookie
     */
    public function testConfigInit(){
        file_put_contents('test.ini', "site.url = www.example.com\r\nmcache.solt = 123456789987654321\r\nlogger.file = access.log");
        return config('source', 'test.ini');
    }
    /**
     * @depends testConfigInit
     */
    public function testConfig($config){
        $this->assertEquals('www.example.com', config('site.url'));
        $this->assertEquals('123456789987654321', config('mcache.solt'));
        $this->assertEquals('testdefault', config('cache.solt', null, 'testdefault'));
    }
    /**
     * @depends testConfigInit
     */
    public function testCache($config){
        $this->assertNull(cache('testcachekey'));
        cache('testcachekey', 'testcachevalue', 1);
        $this->assertEquals('testcachevalue', cache('testcachekey'));
        $this->assertNull(mcache('testcachekey'));
        mcache('testcachekey', 'testcachevalue', 1);
        $this->assertEquals('testcachevalue', mcache('testcachekey'));
    }
    /**
     * @depends testConfigInit
     */
    public function testLogger($config){
        config('logger.file', __dir__. '/access.log');
        @unlink(config('logger.file'));
        $access = logger(config('logger.file'));
        $access('test log');
        $access('test format log: %d, %s', 10, 'bababa');
        $test = $this;
        regiSter_shutdown_function(function()use ($test){
            $test->assertEquals("test log". PHP_EOL. "test format log: 10, bababa". PHP_EOL, file_get_contents(config('logger.file')));
        });
    }
}
