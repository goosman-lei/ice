<?php
namespace Ice\Frame\Error;
class Code {
    // 框架占用[100000 - 199999]区间的错误码
    // 框架通用层级错误
    const UNKNOWN     = 100100;
    const PHP_ERROR   = 100101;
    const PHP_WARN    = 100102;
    const UNKNOWN_URI = 100103;
    const ROUTE_ERROR = 100104;
    // WebService相关错误
    const WS_REQ_PARSE_ERROR       = 100200;
    const WS_REQ_PROTOCOL_ERROR    = 100201;
    const WS_REQ_VERSION_ERROR     = 100202;

    const WS_RESP_PARSE_ERROR      = 100210;
    const WS_RESP_PROTOCOL_ERROR   = 100211;
    const WS_RESP_VERSION_ERROR    = 100212;

    const WS_PROXY_UNKONW_PROXY    = 100220;
    const WS_PROXY_UNKONW_SERVICE  = 100221;
    const WS_PROXY_READ_ERROR      = 100222;
    const WS_PROXY_MESSAGE_ERROR   = 100223;
    const WS_PROXY_MESSAGE_REPEAT  = 100224;

    const WS_ERROR_RESPONSE        = 100230;
    const WS_EXCEPTION_RESPONSE    = 100231;
    // 资源管理器相关错误
    const R_ERROR_URI          = 100300;
    const R_ERROR_GET_NODE     = 100301;
    const R_ERROR_GET_CONN     = 100302;
    const R_ERROR_GET_ALL_CONN = 100303;

    const R_NO_CONNECTOR       = 100310;
    const R_NO_STRATEGY        = 100311;
    const R_NO_HANDLER         = 100312;
    // MYSQL 相关错误
    const MYSQL_CONN_ERROR           = 100400;
    const MYSQL_SET_CHARSET_FAILED   = 100401;
    const MYSQL_SET_COLLATION_FAILED = 100402;
    const MYSQL_QUERY_SQL_TOO_LONG   = 100403;
    const MYSQL_QUERY_WRITE_NO_WHERE = 100404;
    const MYSQL_FAILED_TO_OPERATE_DB = 100405;

    // Filter相关错误
    const FILTER_COMPILE_FAILED      = 100500;
    const FILTER_RUN_STRICT_UNEXPECT = 100501;

    // Query相关错误
    const QUERY_BUILD_EXPR_ERROR          = 100600;
    const QUERY_ESCAPE_FIELD_VALUE_FAILED = 100601;
    const QUERY_QUERY_FAILED              = 100602;
    const QUERY_GET_HANDLER_FAILED        = 100603;

    // Rabbitmq相关错误
    const RABBITMQ_CONN_ERROR    = 100700;
    const RABBITMQ_COMMAND_ERROR = 100701;

    // Redis相关错误
    const REDIS_CONN_ERROR       = 100800;
    const REDIS_FOBIDDEN_COMMAND = 100801;
    const REDIS_COMMAND_ERROR    = 100802;

    //Curl相关错误
    const CURL_GET_EXEC_ERROR    = 100900;
    const CURL_GET_HTTP_ERROR    = 100901;
    const CURL_POST_EXEC_ERROR   = 100902;
    const CURL_POST_HTTP_ERROR   = 100903;
}
