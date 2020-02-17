<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17
 * Time: 11:34 PM
 */

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}