<?php
namespace Ice\Resource\Strategy;
class Random {
    public static function getNode($nodes) {
        return array_rand($nodes);
    }
}
