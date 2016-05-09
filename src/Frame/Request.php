<?php
namespace Ice\Frame;
class Request {
    protected $relayId = 1;
    public function getNextRelayId() {
        return sprintf("%s:%08d", $this->id, $this->relayId ++);
    }
}
