<?php
namespace Ice\Resource\Handler;
class Rabbitmq extends Abs {

    public function exchangeDeclare($exchange, $type, $durable = false, $auto_delete = true) {
        try {
            $this->conn->exchange_declare($exchange, $type, false, $durable, $auto_delete);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
                'sn'        => $this->nodeInfo['sn'],
                'command'   => 'exchange_declare',
                'exchange'  => $exchange,
                'exchange_type' => $type,
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function queueDeclare($queue = "", $durable = false, $exclusive = false, $auto_delete = true) {
        try {
            $this->conn->queue_declare($queue, false, $durable, $exclusive, $auto_delete);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
                'sn'        => $this->nodeInfo['sn'],
                'command'   => 'queue_declare',
                'queue'     => $queue,
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function queueBind($queue, $exchange, $routingKey = '') {
        try {
            $this->conn->queue_bind($queue, $exchange, $routingKey);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'code'      => $e->getCode(),
                'sn'        => $this->nodeInfo['sn'],
                'command'   => 'queue_bind',
                'queue'     => $queue,
                'exchange'  => $exchange,
                'bind_key'  => $routingKey,
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function produce($msgBody, $exchange = '', $routingKey = '') {
        try {
            $amqpMsg = new \PhpAmqpLib\Message\AMQPMessage($msgBody, $this->nodeOptions['msg_properties']);
            $this->conn->basic_publish($amqpMsg, $exchange, $routingKey);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception'   => get_class($e),
                'message'     => $e->getMessage(),
                'code'        => $e->getCode(),
                'sn'          => $this->nodeInfo['sn'],
                'command'     => 'produce',
                'exchange'    => $exchange,
                'routing_key' => $routingKey,
                'msg_body'    => substr($msgBody, 0, 256),
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function consume($queue = "", $callback = null, $noAck = false) {
        try {
            $this->conn->basic_consume($queue, '', false, $noAck, false, false, $callback);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception'   => get_class($e),
                'message'     => $e->getMessage(),
                'code'        => $e->getCode(),
                'sn'          => $this->nodeInfo['sn'],
                'command'     => 'consume',
                'queue'       => $queue,
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function wait($allowed_methods=null, $non_blocking = false, $timeout = 0) {
        try {
            $this->conn->wait($allowed_methods, $non_blocking, $timeout);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception'   => get_class($e),
                'message'     => $e->getMessage(),
                'code'        => $e->getCode(),
                'sn'          => $this->nodeInfo['sn'],
                'command'     => 'wait',
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
        return TRUE;
    }

    public function __call($method, $argv) {
        try {
            return call_user_func_array([$this->conn, $method], $argv);
        } catch (\Exception $e) {
            \F_Ice::$ins->mainApp->logger_comm->warn(array(
                'exception'   => get_class($e),
                'message'     => $e->getMessage(),
                'code'        => $e->getCode(),
                'sn'          => $this->nodeInfo['sn'],
                'command'     => $method,
            ), \F_ECode::RABBITMQ_COMMAND_ERROR);
            return FALSE;
        }
    }
}
