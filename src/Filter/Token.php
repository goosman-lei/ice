<?php
namespace Ice\Filter;

class Token {

    const LITERAL_STRING  = 0x00001;
    const LITERAL_NUMERIC = 0x00002;
    const LITERAL_ID      = 0x00004;

    const KEYWORD_TRUE    = 0x00008;
    const KEYWORD_FALSE   = 0x00010;
    const KEYWORD_NULL    = 0x00020;

    const TYPE            = 0x00040;
    const REQUIREMENT     = 0x00080;

    const COLON           = 0x00100;
    const COMMA           = 0x00200;
    const SEMICOLON       = 0x00400;
    const BLOCK_START     = 0x00800;
    const BLOCK_END       = 0x01000;
    const BRACKET_START   = 0x02000;
    const BRACKET_END     = 0x04000;
    const AT              = 0x08000;
    const PIPE            = 0x10000;
    const STAR            = 0x20000;

    const EOF             = 0x40000;

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
        } else if ($type & self::LITERAL_NUMERIC) {
            $typeStr .= ' LITERAL_NUMERIC |';
        } else if ($type & self::LITERAL_ID) {
            $typeStr .= ' LITERAL_ID |';
        } else if ($type & self::KEYWORD_TRUE) {
            $typeStr .= ' KEYWORD_TRUE |';
        } else if ($type & self::KEYWORD_FALSE) {
            $typeStr .= ' KEYWORD_FALSE |';
        } else if ($type & self::KEYWORD_NULL) {
            $typeStr .= ' KEYWORD_NULL |';
        } else if ($type & self::TYPE) {
            $typeStr .= ' TYPE |';
        } else if ($type & self::REQUIREMENT) {
            $typeStr .= ' REQUIREMENT |';
        } else if ($type & self::COLON) {
            $typeStr .= ' COLON |';
        } else if ($type & self::COMMA) {
            $typeStr .= ' COMMA |';
        } else if ($type & self::SEMICOLON) {
            $typeStr .= ' SEMICOLON |';
        } else if ($type & self::BLOCK_START) {
            $typeStr .= ' BLOCK_START |';
        } else if ($type & self::BLOCK_END) {
            $typeStr .= ' BLOCK_END |';
        } else if ($type & self::BRACKET_START) {
            $typeStr .= ' BRACKET_START |';
        } else if ($type & self::BRACKET_END) {
            $typeStr .= ' BRACKET_END |';
        } else if ($type & self::AT) {
            $typeStr .= ' AT |';
        } else if ($type & self::PIPE) {
            $typeStr .= ' PIPE |';
        } else if ($type & self::EOF) {
            $typeStr .= ' EOF |';
        }

        $typeStr[strlen($typeStr) - 1] = ']';
    }

    public function __toString() {
        $typeStr = self::typeToString($this->type);
        return sprintf("%s%s [position: %6d]", $this->literal, $typeStr, $this->pos);
    }
}
