<?php

namespace Siphon;

use Siphon\Exception;

class Siphon {

    /**
     * @var \Closure
     */
    public $before_siphon;

    /**
     * @var \Closure
     */
    public $after_siphon;


    /**
     * @var \Closure
     */
    public $listener;

    public function __construct(){

        $this->listener = function(){};

        if(func_num_args() == 1){ // more than the current arg list allows
        	$args = func_get_args();
        	$block = array_pop($args);
        	if(is_callable($block)){
        		$this->exec($block, array($this));
        	}
        }
    }

    public function siphon($stream, $block = null){

        // before
        $this->exec($this->before_siphon, array(&$stream));

        if($from_string = is_string($stream)){
            $ctx = stream_context_create(null, array('notification' => $this->listener));
            $stream = fopen($stream, 'rb', true, $ctx);
        }
        // rewind streams not at the start, throws a warning for streams that cannot be rewound.
        ftell($stream) === 0 || rewind($stream);

        $body = "";
        while(!feof($stream)){
            if(false === ($body .= fread($stream, 8192))){
                throw new Exception("Failed to read from stream.");
            }
            if(empty($chunk)) break;
        }
        // close the stream
        !$from_string || fclose($stream);

        // after
        $this->exec($this->after_siphon, array(&$body));

        // If we received a block then exec it and alter the response.
        is_callable($block) || ($block = function()use($body){return $body;});

        return $this->exec($block, array(&$body));
    }
    
    private function exec(&$block, $args = array()){
    	// handle block exceptions nicely.
    	try {
    		is_callable($block) || ($block = function(){});
            return call_user_func_array($block, $args);
    	} catch(\Exception $ex){
    		throw $ex;
    	}
    }

}

