<?php 
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
        $this->assertNull(shmcache('testcachekey'));
        shmcache('testcachekey', 'testcachevalue', 1);
        $this->assertEquals('testcachevalue', shmcache('testcachekey'));
    }
    /**
     * @depends testConfigInit
     */
    public function testLogger($config){
        @unlink(config('logger.file'));
        $this->assertEquals('access.log', config('logger.file'));
        $access = logger();
        $access('test log');
        $access('test format log: %d, %s', 10, 'bababa');
        $this->assertEquals("test log\r\ntest format log: 10, bababa\r\n", file_get_contents(config('logger.file')));
    }
}
