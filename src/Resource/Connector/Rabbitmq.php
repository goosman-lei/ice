<?php
namespace Ice\Resource\Connector;
class Rabbitmq extends Abs {
    
    public static function mergeDefault($nodeConfig, $nodeOptions) {
        static $defaultOptions = array(
            'insist'             => false,
            'login_method'       => 'AMQPLAIN',
            'login_response'     => null,
            'locale'             => 'en_US',
            'connection_timeout' => 1,
            'read_write_timeout' => 1,
            'context'            => null,
            'consumer'           => array(
                'prefetch_size'      => 0,
                'prefetch_count'     => 1,
            ),
            'msg_properties'     => array(
                'delivery_mode' => 2,
            ),
        );

        if (!isset($nodeConfig['vhost'])) {
            $nodeConfig['vhost'] = '/';
        }

        $options = $defaultOptions;
        foreach ($options as $name => $option) {
            if (isset($nodeOptions[$name])) {
                if ($name == 'consumer') {
                    $options['consumer'] = array_merge($options['consumer'], $nodeOptions['consumer']);
                } else if ($name == 'msg_properties') {
                    $options['msg_properties'] = array_merge($options['msg_properties'], $nodeOptions['msg_properties']);
                } else {
                    $options[$name] = $nodeOptions[$name];
                }
                unset($nodeOptions[$name]);
            }
        }
        foreach ($nodeOptions as $name => $option) {
            $options[$name] = $option;
        }

        return array($nodeConfig, $options);
    }

    public static function getSn($nodeConfig, $nodeOptions) {
        return sprintf('%s:%s:%s:%s', $nodeConfig['host'], $nodeConfig['port'], $nodeConfig['vhost'], $nodeOptions['user']);
    }

    public static function getConn($nodeInfo) {
        $options = $nodeInfo['options'];
        $config  = $nodeInfo['config'];

        try {
            $conn = new \PhpAmqpLib\Connection\AMQPConnection($config['host'], $config['port'], 
                $options['user'], $options['passwd'],
                $config['vhost'], $options['insist'],
                $options['login_method'], $options['login_response'],
                $options['locale'],
                $options['connection_timeout'], $options['read_write_timeout'],
                $options['context']);

            # 创建channel
            $channel = new \PhpAmqpLib\Channel\AMQPChannel($conn);
            # 对consumer生效. 预取设置
            $channel->basic_qos($options['consumer']['prefetch_size'], $options['consumer']['prefetch_count'], FALSE);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
            ), \F_ECode::RABBITMQ_CONN_ERROR);
            return FALSE;
        }

        return $channel;
    }
}