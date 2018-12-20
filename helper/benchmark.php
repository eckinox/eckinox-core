<?php

namespace Eckinox;

/**
 * @author Mikael Laforge <mikael.laforge@gmail.com>
 * @version 1.1.3
 * @package Eckinox
 *
 * @update (16/11/11) [ML] - 1.1.0 - Changed log display
 * 								   - Added prefix to files and manage directory/path by itstatic
 * 							       - Added total total memory usage to logs
 * @update (26/03/12) [ML] - 1.1.1 - Bugfix: dump_all() method now correctly open the benchmark
 * 	                                 file using a full path.
 * @update (07/02/14) [ML] - 1.1.2 - Memory usage will now be output as MB
 * @update (02/05/14) [ML] - 1.1.3 - will now create var/benchmark directory if doesnt exist
 */
abstract class benchmark {

    // Benchmark timestamps
    protected static $marks;

    /**
     * Set a benchmark start point.
     *
     * @param   string  benchmark name
     * @return  void
     */
    public static function start($name) {
        if (!isset(static::$marks[$name])) {
            static::$marks[$name] = array
                (
                'start' => microtime(TRUE),
                'stop' => FALSE,
                'memory_start' => function_exists('memory_get_usage') ? memory_get_usage() : 0,
                'memory_stop' => FALSE,
            );
        }
    }

    /**
     * Set a benchmark stop point.
     *
     * @param   string  benchmark name
     * @return  void
     */
    public static function stop($name) {
        if (isset(static::$marks[$name]) AND static::$marks[$name]['stop'] === FALSE) {
            static::$marks[$name]['stop'] = microtime(TRUE);
            static::$marks[$name]['memory_stop'] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
        }
    }

    /**
     * Get the elapsed time between a start and stop.
     *
     * @param   string   benchmark name, TRUE for all
     * @param   integer  number of decimal places to count to
     * @return  array
     */
    public static function get($name, $decimals = 4) {
        if ($name === TRUE) {
            $times = [];
            $names = array_keys(static::$marks);

            foreach ($names as $name) {
                // Get each mark recursively
                $times[$name] = static::get($name, $decimals);
            }

            // Return the array
            return $times;
        }

        if (!isset(static::$marks[$name]))
            return FALSE;

        if (static::$marks[$name]['stop'] === FALSE) {
            // Stop the benchmark to prevent mis-matched results
            static::stop($name);
        }

        // Return a string version of the time between the start and stop points
        // Properly reading a float requires using number_format or sprintf
        return array
            (
            'time' => number_format(static::$marks[$name]['stop'] - static::$marks[$name]['start'], $decimals),
            'memory' => (static::$marks[$name]['memory_stop'] - static::$marks[$name]['memory_start']),
            'total_memory' => (function_exists('memory_get_usage') ? memory_get_usage() : 0),
        );
    }

    /**
     * Dump all benchmark in a file
     * given in argument
     * @param string $file
     * @param string $mode
     */
    public static function dump_all($file, $mode = 'a') {
        $path = DOC_ROOT . VAR_PATH . 'benchmark';
        if (!is_dir($path))
            mkdir($path, 0775, true);

        $file = $path . DIRECTORY_SEPARATOR . date('m-Y') . '-' . basename($file);

        if ($handle = fopen($file, $mode)) {
            $times = static::get(true);
            foreach ($times as $name => $time) {
                $content = date('Y-m-d H:i:s') . ' ' .
                        'NAME: "' . $name . '" ' .
                        'TIME USED: ' . $time['time'] . ' seconds ' .
                        'MEMORY USED: ' . static::bytes2megabytes($time['memory']) . ' mb ' .
                        'TOTAL MEMORY USED: ' . static::bytes2megabytes($time['total_memory']) . ' mb' . NEX_EOL;

                fwrite($handle, $content);
            }

            fwrite($handle, '--' . NEX_EOL);
            fclose($handle);
        }
    }

    protected static function bytes2megabytes($bytes) {
        return number_format($bytes / 1024 / 1024, 2);
    }

}
