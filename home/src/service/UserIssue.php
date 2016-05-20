<?php
namespace ice\home\Service;
class UserIssue extends \FS_Service {
    public function save($contact, $title, $content) {
        $issueModel = new \ice\home\Model\UserIssue();

        $isSuccess = $issueModel->insert(array(
            'contact' => $contact,
            'title'   => $title,
            'content' => $content,
        ));

        if ($isSuccess !== FALSE) {
            return $this->succ();
        } else {
            return $this->error(1, '保存失败, 请稍后重试');
        }
    }
}
