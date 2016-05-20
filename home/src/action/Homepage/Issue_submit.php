<?php
namespace ice\home\Action\Homepage;
class Issue_submit extends \H_Action {
    protected $inputFilter = '(map){
        issue_contact(str) escape;
        issue_title(str) escape;
        issue_content(str) escape;
    }';

    protected $outputFilter = '(map)';

    public function realExecute() {
        $contact = htmlspecialchars($this->input['issue_contact']);
        $title   = htmlspecialchars($this->input['issue_title']);
        $content = htmlspecialchars($this->input['issue_content']);

        if (strlen($contact) < 1 || strlen($title) < 1) {
            return array(
                'code' => 1,
                'data' => array(
                    'msg' => '联系信息和标题是必填项',
                ),
            );
        }

        $service = $this->ice->workApp->proxy_service->get('internal', 'UserIssue');
        $retArr  = $service->save($contact, $title, $content);

        if ($retArr['code'] == 0) {
            return array(
                'code' => $retArr['code'],
                'data' => new \U_Map(),
            );
        } else {
            return array(
                'code' => $retArr['code'],
                'data' => array(
                    'msg' => $retArr['data']['msg'],
                ),
            );
        }
    }
}
