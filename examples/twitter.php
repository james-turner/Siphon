<?php

error_reporting(E_ALL|E_STRICT);

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/../src'),
    get_include_path()
)));

require_once('Siphon/Autoloader.php');

Siphon\Autoloader::register();

use Siphon\Siphon as Siphon;

new Siphon(function($s){

    $s->before_siphon = function($uri)use(&$start_time){
        $start_time = microtime(true);
    };

    $s->after_siphon = function($body)use(&$end_time){
        $end_time = microtime(true);
        if(null !== $json = json_decode($body, true)){
            $json['debug'] = true;
            $body = json_encode($json);
        }
    };

    $s->listener = function($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)use(&$filesize){

        switch($notification_code) {
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_COMPLETED:
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
                /* Ignore */
                break;

            case STREAM_NOTIFY_REDIRECTED:
                echo "Being redirected to: ", $message, "\n";
                break;

            case STREAM_NOTIFY_CONNECT:
                echo "Connected...\n";
                break;

            case STREAM_NOTIFY_FILE_SIZE_IS:
                $filesize = $bytes_max;
                echo "Filesize: ", $filesize, " bytes\n";
                break;

            case STREAM_NOTIFY_MIME_TYPE_IS:
                echo "Mime-type: ", $message, "\n";
                break;

            case STREAM_NOTIFY_PROGRESS:
                if ($bytes_transferred > 0) {
                    if (!isset($filesize)) {
                        printf("\rUnknown filesize.. %2d kb done..", $bytes_transferred/1024);
                    } else {
                        $length = (int)(($bytes_transferred/$filesize)*100);
                        printf("\r[%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length). ">", $length, ($bytes_transferred/1024), $filesize/1024);
                    }
                }
                break;
        }
    };

	$s->siphon('http://search.twitter.com/search.json?q=php');

    echo PHP_EOL;
    echo "Took " . ($end_time - $start_time) . " seconds." . PHP_EOL;

});