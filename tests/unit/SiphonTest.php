<?php

class SiphonTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \Siphon\Siphon
     */

    private $siphon;

    public function setUp(){

        $this->siphon = new Siphon\Siphon();

    }

    /**
     * @test
     */
    public function siphonReturnsAString(){

        $this->assertThat($this->siphon->siphon('php://memory'), $this->isType('string'));

    }

    /**
     * @test
     */
    public function siphonReturnsAllTheStreamContent(){

        $testString = 'THIS IS A TEST STRING';

        $stream = fopen('php://memory', 'rw');
        fwrite($stream, $testString);

        $this->assertEquals($testString, $this->siphon->siphon($stream));

    }

    /**
     * @test
     */
    public function siphonReturnsAnEmptyStringWhenGivenAStreamThatCannotBeReadFrom(){

        $tmp = tempnam(sys_get_temp_dir(), 'phpunit_');

        $stream = fopen($tmp, 'w');
        fwrite($stream, "test string");
        rewind($stream);

        $this->assertEquals("", $this->siphon->siphon($stream));

        unlink($tmp);
    }

    /**
     * @test
     */
    public function siphonPerformsARewindBeforeAttemptingToRead(){

        $stream = 'php://memory';

        $this->assertEquals('', $this->siphon->siphon($stream));

    }

    /**
     * @test
     */
    public function siphonWithAURLInvokesTheNotificationHandler(){

        $uri = 'http://google.com';

        $listener_ran = false;

        new Siphon\Siphon(function($s)use($uri, &$listener_ran){
            $s->listener = function()use(&$listener_ran){
                $listener_ran = true;
            };

            return $s->siphon($uri);

        });

        $this->assertTrue($listener_ran);
    }

    /**
     * @test
     */
    public function beforeSiphonRunsBeforeSiphoning(){

        $isRun = false;
        $test =& $this;

        $this->siphon->before_siphon = function($uri)use(&$isRun, $test){
            $test->assertEquals($uri, 'php://memory');
            $isRun = true;
        };

        $this->siphon->siphon('php://memory');

        $this->assertTrue($isRun);

    }

    /**
     * @test
     */
    public function afterSiphonRunsAfterSiphoning(){

        $isRun = false;

        $this->siphon->before_siphon = function($body)use(&$isRun){
            $isRun = true;
        };

        $this->siphon->siphon('php://memory');

        $this->assertTrue($isRun);

    }


}