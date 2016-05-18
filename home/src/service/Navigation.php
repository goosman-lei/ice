<?php
namespace ice\home\Service;
class Navigation extends \FS_Service {
    // 最多3层结构
    public function getAllNodes($class, $action) {
        $navigationModel = new \ice\home\Model\Navigation();
        $nodes = $navigationModel->getRows();
        if (!is_array($nodes) || empty($nodes)) {
            return $this->succ(array(
                'rootNavs'    => array(),
                'currNavRoot' => array(),
            ));
        }

        // 先pid后id对所有节点排序
        uasort($nodes, array(self, 'cmpNode'));

        // 构造树结构
        $currUri    = "/$class/$action";
        $currUriLen = strlen($currUri);
        $currNavId  = null;
        $rootNavs = array();
        $list     = \U_Array::pickup($nodes, \U_Array::PICKUP_VK_ENTIRE, 'id');
        foreach ($list as $id => $node) {
            $pid = $node['pid'];
            if ($pid == 0) {
                $rootNavs[$id] = &$list[$id];
            } else if (isset($list[$pid]['children'])) {
                $list[$pid]['children'][$id] = &$list[$id];
            } else {
                $list[$pid]['children'] = array($id => &$list[$id]);
            }

            if (strcasecmp($node['url'], $currUri) === 0
                || stripos($node['url'], $currUri) === 0 && @$node['url'][$currUriLen] == '/') {
                $currNavId = $id;
            }
        }

        // 取当前节点根节点
        $currNavRoot = array();
        if (isset($currNavId)) {
            $currNavRoot = $list[$currNavId];
            while ($currNavRoot['pid'] != 0) {
                $currNavRoot = $list[$currNavRoot['pid']];
            }
        }

        unset($list);

        return $this->succ(array(
            'rootNavs'    => $rootNavs,
            'currNavRoot' => $currNavRoot,
        ));
    }

    protected static function cmpNode($a, $b) {
        $pidMinus = $a['pid'] - $b['pid'];
        $idMinus  = $a['id'] - $b['id'];
        return $pidMinus == 0 ? $idMinus : $pidMinus;
    }
}
