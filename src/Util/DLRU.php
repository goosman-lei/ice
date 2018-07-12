<?php
namespace Ice\Util;
/**
 * DLRU 
 * 纯内存LRU Cache
 * @copyright Copyright (c) 2014 oneniceapp.com, Inc. All Rights Reserved
 * @author 雷果国<leiguoguo@oneniceapp.com> 
 */
class DLRU {
    protected $head;
    protected $tail;
    protected $kv = [];

    public function __construct($cap) {
        $this->cap = $cap;
    }

    /**
     * set 
     * 设置数据
     * @param mixed $k 
     * @param mixed $v 
     * @access public
     * @return void
     */
    public function set($k, $v) {
        if (!isset($this->kv[$k])) {
            $node        = new DLRUNode;
            $node->key   = $k;
            $node->value = $v;

            $node->next  = &$this->head;
            if (isset($this->head)) {
                $this->head->prev = &$node;
            }
            $this->head   = &$node;
            if (!isset($this->tail)) {
                $this->tail = &$node;
            }
            $this->kv[$k] = &$node;
            // 只有新增数据项触发LRU
            $this->lru();
        } else {
            $node        = &$this->kv[$k];
            $node->value = $v;
            if (!isset($node->prev)) {
                // 链表头: 不处理
            } else if (!isset($node->next)) {
                // 链表尾-step1: 去尾
                unset($node->prev->next);
                $node->prev->next = NULL;
                unset($node->prev);
                $node->prev = NULL;
                $this->tail = &$node->prev;
                // 链表尾-step1: 链头
                $node->next = &$this->head;
                $this->head->prev = &$node;
                $this->head = &$node;
            } else {
                // 链表中部-step1: 断链
                $node->prev->next = &$node->next;
                $node->next->prev = &$node->prev;
                // 链表尾-step1: 链头
                unset($node->prev);
                $node->prev = NULL;
                $node->next = &$this->head;
                $this->head->prev = &$node;
                $this->head = &$node;
            }
        }
    }

    /**
     * del 
     * 删除数据
     * @param mixed $k 
     * @access public
     * @return void
     */
    public function del($k) {
        if (!isset($this->kv[$k])) {
            // 不存在, 直接返回删除成功
            return TRUE;
        }
        $node = &$this->kv[$k];
        if (!isset($node->prev)) {
            // 链表头: 去头
            $this->head = &$node->next;
            unset($node->next);
            $node->next = NULL;
        } else if (!isset($node->next)) {
            // 链表尾-step1: 去尾
            $this->tail = &$node->prev;
            unset($this->tail->next);
            $this->tail->next = NULL;
            unset($node->prev);
            $node->prev = NULL;
        } else {
            // 链表中部-step1: 断链
            $node->prev->next = &$node->next;
            $node->next->prev = &$node->prev;
            unset($node->prev);
            $node->prev = NULL;
            unset($node->next);
            $node->next = NULL;
        }
        unset($this->kv[$k]);
        unset($node);
    }

    /**
     * get 
     * 读取数据
     * @param mixed $k 
     * @access public
     * @return void
     */
    public function get($k) {
        return isset($this->kv[$k]) ? $this->kv[$k] : null;
    }

    /**
     * exists 
     * 检测数据存在性
     * @param mixed $k 
     * @access public
     * @return void
     */
    public function exists($k) {
        return isset($this->kv[$k]);
    }

    protected function lru() {
        if (count($this->kv) > $this->cap) {
            // 去尾
            $node       = &$this->tail;
            unset($node->prev->next);
            $node->prev->next = NULL;
            $this->tail = &$node->prev;
            unset($node->prev);
            $node->prev = NULL;
            unset($this->kv[$node->key]);
            unset($node);
        }
    }

    /**
     * dump_head 
     * 从前向后打印存活的KEY链表
     * @access public
     * @return void
     */
    public function dump_head() {
        return isset($this->head) ? $this->head->dump_behind() : '(empty)';
    }

    /**
     * dump_tail 
     * 从后向前打印存活的KEY链表
     * @access public
     * @return void
     */
    public function dump_tail() {
        return isset($this->tail) ? $this->tail->dump_ahead() : '(empty)';
    }
}
class DLRUNode {

    public $prev;
    public $next;
    public $value;
    public $key;

    public function dump_behind() {
        $dump_str = $this->key;
        if (isset($this->next)) {
            $dump_str .= '  =>  ' . $this->next->dump_behind();
        }
        return $dump_str;
    }

    public function dump_ahead() {
        $dump_str = '';
        if (isset($this->prev)) {
            $dump_str .= '  =>  ' . $this->prev->dump_ahead();
        }
        return $this->key . $dump_str;
    }
}
