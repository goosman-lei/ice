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

if (! function_exists('array_where')) {
    function array_where($array, callable $callback)
    {
        return Ice\Util\DArray::where($array, $callback);
    }
}
