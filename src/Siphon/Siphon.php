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

    /**
     * Siphon constructor
     * Allows passing
     */
    public function __construct(){
        // Initialise the listener.
        $this->listener = function(){};

        if(func_num_args() > 0){ // more than the current arg list allows
        	$args = func_get_args();
        	$block = array_pop($args); // last arg only is allowed to be a block.
        	if(is_callable($block)){
        		$this->yield($block, array($this)); // yield!!
        	}
        }
    }

    /**
     *
     * @throws Exception
     * @param $stream
     * @param null|Closure $block
     * @return mixed
     */
    public function siphon($stream, $block = null){

        // before
        $this->yield($this->before_siphon, array(&$stream));

        // If we got a string then convert it to a stream and provide the listener.
        if($from_string = is_string($stream)){
            $ctx = stream_context_create(null, array('notification' => $this->listener));
            $stream = fopen($stream, 'rb', true, $ctx);
        }
        // rewind streams not at the start, throws a warning for streams that cannot be rewound.
        ftell($stream) === 0 || rewind($stream);

        // Initialise the body of the response.
        $body = "";
        while(!feof($stream)){
            if(false === ($chunk = fread($stream, 8192))){
                throw new Exception("Failed to read from stream.");
            }
            $body .= $chunk;
            // if(empty($body)) break; <- might resolve an issue with write only streams not exiting.
        }
        // close the stream
        !$from_string || fclose($stream);

        // after
        $this->yield($this->after_siphon, array(&$body));

        // If we received a block then exec it and alter the response.
        is_callable($block) || ($block = function()use($body){return $body;});

        return $this->yield($block, array(&$body));
    }

    /**
     * Execute a block of code (Closure) with the provided
     * arguments. Catches and throws any exceptions.
     * If block is not callable it utilises a basic lambda
     * instead.
     * @static
     * @throws Exception
     * @param \Closure $block
     * @param array $args
     * @return mixed
     */
    private function yield(&$block, $args = array()){
    	// handle block exceptions nicely.
    	try {
    		is_callable($block) || ($block = function(){});
            return call_user_func_array($block, $args);
    	} catch(\Exception $ex){
    		throw $ex;
    	}
    }

}

