<?php
if (! function_exists('only')) {

    /**
     * return only key data from array
     * @param array $input
     * @param $keys
     * @return array
     */
    function only(array $input, $keys)
    {
        $keys = is_array($keys) ? $keys : array_slice(func_get_args(),1);

        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $input[$key];
        }

        return $results;
    }
}

if (! function_exists('except')) {

    /**
     * return except key data from array
     * @param array $input
     * @param $keys
     * @return array
     */
    function except(array $input, $keys)
    {
        $keys = is_array($keys) ? $keys : array_slice(func_get_args(),1);

        foreach ($keys as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}