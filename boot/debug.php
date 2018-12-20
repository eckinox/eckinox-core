<?php

use Eckinox\Eckinox;

foreach(Eckinox::header_vars() as $key => $item) {
    switch($key) {
        case 'debug':
            Eckinox::debug((bool)$item);
            break;

        case 'error_reporting':
            Eckinox::error_reporting($item);
            break;
    }
}

if (! function_exists('dump')) {
    function dump(...$what) {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        echo "<pre style='-webkit-overflow-scrolling: touch;background-color: #f5f5f5;color: #4a4a4a;font-size: 12px;line-height:18px;overflow-x: auto;padding: 1.25rem 1.5rem;word-wrap: normal;white-space: pre;margin:0.33rem 0;'>";
        echo "<div style='color:#9f9f9f;font-size:10px;' title='{$stack[0]['file']}'>"."[ file: ".basename($stack[0]['file']).",\tline: {$stack[0]['line']} ]</div>\n";
        var_dump( ...$what );
        echo "<div style='margin-left:25px;'>";
        echo "</div></pre>";
    }
}
