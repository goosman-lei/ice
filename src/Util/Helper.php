<?php
//数组类
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
        if ($value == '*') {
            $value = false;
        }
        if (!is_array($array) || !count($array)) {
            return array();
        }
        if (false === $value && null === $key) {
            return $array;
        }
        return \U_Array::pluck($array, $value, $key);
    }
}

if (! function_exists('array_where')) {
    function array_where($array, callable $callback)
    {
        return \U_Array::where($array, $callback);
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

if (! function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return \U_Array::get($array, $key, $default);
    }
}

if (! function_exists('array_sort')) {
    function array_sort(&$array, $key, $type = 'string', $reverse = FALSE)
    {
        \U_Array::sortWithValue($array, $key, $type, $reverse);
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


//字符串类
if (! function_exists('str_is')) {
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    function str_is($pattern, $value)
    {
        return \U_String::is($pattern, $value);
    }
}


if (! function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int     $limit
     * @param  string  $end
     * @return string
     */
    function str_limit($string, $maxLen, $suffix = '...', $charset = 'utf-8')
    {
        return \U_String::omitTail($string, $maxLen, $suffix, $charset);
    }
}


if (! function_exists('str_random')) {
    function str_random($length)
    {
        return \U_String::randStr($length);
    }
}

//时间



//常用

if (! function_exists('info')) {
    function info($userLog)
    {
        \F_Ice::$ins->mainApp->logger_comm->info($userLog);
    }
}

if (! function_exists('warn')) {
    function warn($userLog, $errno = [])
    {
        \F_Ice::$ins->mainApp->logger_comm->warn($userLog, $errno);
    }
}

if (! function_exists('fatal')) {
    function fatal($userLog, $errno = [])
    {
        \F_Ice::$ins->mainApp->logger_comm->fatal($userLog, $errno);
    }
}


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

if (! function_exists('success')) {
    function success($data = null)
    {
        return array('data' => $data, 'code' => 0);
    }
}

if (! function_exists('is_success')) {
    function is_success($ret)
    {
        if (isset($ret['code']) && 0 === $ret['code']) {
            return true;
        }
        return false;
    }
}

if (! function_exists('error')) {
    function error($code = \F_ECode::UNKNOWN_URI, $data = null)
    {
        return array('code' => $code, 'data' => $data);
    }
}

if (! function_exists('service')) {
    function service($serviceName, $class = null)
    {

        return \F_Ice::$ins->workApp->proxy_service->get($serviceName, $class);
    }
}


if (! function_exists('filter_uint')) {
    function filter_uint($var)
    {
        $options = array(
            'options' => array(
                'min_range' => 0,
                'max_range' => 2147493647,
            )
        );
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $var[$key] = filter_var($val, FILTER_VALIDATE_INT, $options);
            }
            return array_filter($var);
        } else {
            return filter_var($var, FILTER_VALIDATE_INT, $options);
        }
    }
} 
