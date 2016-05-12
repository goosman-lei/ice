<?php
namespace Ice\Filter;

class Token {

    const LITERAL_STRING  = 0x00001;
    const LITERAL_NUMERIC = 0x00002;
    const LITERAL_ID      = 0x00004;

    const KEYWORD         = 0x00008;

    const TYPE            = 0x00010;
    const REQUIREMENT     = 0x00020;

    const COLON           = 0x00040;
    const COMMA           = 0x00080;
    const SEMICOLON       = 0x00100;
    const BLOCK_START     = 0x00200;
    const BLOCK_END       = 0x00400;
    const BRACKET_START   = 0x00800;
    const BRACKET_END     = 0x01000;
    const AT              = 0x02000;
    const PIPE            = 0x04000;
    const STAR            = 0x08000;

    const EOF             = 0x10000;

    public $literal;
    public $type;
    public $pos;

    private function __construct($literal, $pos, $type) {
        $this->literal = $literal;
        $this->pos = $pos;
        $this->type = $type;
    }
    public static function buildToken($literal, $pos, $type) {
        return new self($literal, $pos, $type);
    }
    public function isValid($type) {
        return $this->type & $type;
    }

    public static function typeToString($type) {
        $typeStr = '[';
        if ($type & self::LITERAL_STRING) {
            $typeStr .= ' LITERAL_STRING |';
        }
        if ($type & self::LITERAL_NUMERIC) {
            $typeStr .= ' LITERAL_NUMERIC |';
        }
        if ($type & self::LITERAL_ID) {
            $typeStr .= ' LITERAL_ID |';
        }
        if ($type & self::KEYWORD) {
            $typeStr .= ' KEYWORD |';
        }
        if ($type & self::TYPE) {
            $typeStr .= ' TYPE |';
        }
        if ($type & self::REQUIREMENT) {
            $typeStr .= ' REQUIREMENT |';
        }
        if ($type & self::COLON) {
            $typeStr .= ' COLON |';
        }
        if ($type & self::COMMA) {
            $typeStr .= ' COMMA |';
        }
        if ($type & self::SEMICOLON) {
            $typeStr .= ' SEMICOLON |';
        }
        if ($type & self::BLOCK_START) {
            $typeStr .= ' BLOCK_START |';
        }
        if ($type & self::BLOCK_END) {
            $typeStr .= ' BLOCK_END |';
        }
        if ($type & self::BRACKET_START) {
            $typeStr .= ' BRACKET_START |';
        }
        if ($type & self::BRACKET_END) {
            $typeStr .= ' BRACKET_END |';
        }
        if ($type & self::AT) {
            $typeStr .= ' AT |';
        }
        if ($type & self::PIPE) {
            $typeStr .= ' PIPE |';
        }
        if ($type & self::EOF) {
            $typeStr .= ' EOF |';
        }

        $typeStr[strlen($typeStr) - 1] = ']';
        return $typeStr;
    }

    public function __toString() {
        $typeStr = self::typeToString($this->type);
        return sprintf("%s%s [position: %6d]", $this->literal, $typeStr, $this->pos);
    }
}
