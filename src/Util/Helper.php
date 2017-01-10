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

if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed   $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if (! \U_Array::accessible($target)) {
                    return value($default);
                }

                $result = \U_Array::pluck($target, $key);

                return in_array('*', $key) ? \U_Array::collapse($result) : $result;
            }

            if (\U_Array::accessible($target)) {
                if (! \U_Array::exists($target, $segment)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (! isset($target->{$segment})) {
                    return value($default);
                }

                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (! function_exists('array_pluck')) {
    function array_pluck($array, $value, $key = null)
    {
        return \U_Array::pluck($array, $value, $key);
    }
}


if (! function_exists('array_where')) {
    function array_where($array, callable $callback)
    {
        return \U_Array::where($array, $callback);
    }
}

if (! function_exists('array_head')) {
    function array_head($array)
    {
        return reset($array);
    }
}

if (! function_exists('array_last')) {
    function array_last($array)
    {
        return end($array);
    }
}

if (! function_exists('array_unshift_index')) {
    function array_unshift_index(&$arr, $index, $item)
    {
        $arr1 = array_slice($arr, 0, $index);
        $arr2 = array_slice($arr, $index);
        $arr = array_merge($arr1, array($item), $arr2);
    }
}


if (! function_exists('info')) {
    function info($userLog)
    {
        \F_Ice::$ins->mainApp->logger_comm->info($userLog);
    }
}

if (! function_exists('warn')) {
    function warn($userLog, $errno)
    {
        \F_Ice::$ins->mainApp->logger_comm->warn($userLog, $errno);
    }
}

if (! function_exists('fatal')) {
    function fatal($userLog, $errno)
    {
        \F_Ice::$ins->mainApp->logger_comm->fatal($userLog, $errno);
    }
}
