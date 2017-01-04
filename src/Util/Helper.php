<?php
if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('array_first')) {
    function array_first($array, callable $callback, $default = null)
    {
        return DArray::first($array, $callback, $default);
    }
}
