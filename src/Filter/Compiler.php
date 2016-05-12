<?php
namespace Ice\Filter;
class Compiler {

    public function __construct($srcCode) {
        $this->srcCode    = $srcCode;
        $this->srcCodeLen = strlen($srcCode);
        $this->dstCode    = '';
    }

    protected $indentLevel = 0;
    protected $indentStr   = '    ';
    protected $dstCode     = '';
    protected $srcCode     = '';
    protected $srcCodeLen  = 100;
    protected function appendCode($dstCode = "\n", $indent = 0) {
        $this->dstCode .= str_repeat($this->indentStr, $this->indentLevel + $indent) . $dstCode;
    }
    protected function readTokenV2($expectType = 0xFFFFFFFF) {
        $startPos = $this->position;
        $token = null;
        while ($this->position < $this->srcCodeLen) {
            $literal = $this->srcCode[$this->position];
            $this->position ++;
            switch ($literal) {
                // 空白字符. 直接跳过
                case ' ':
                case "\n":
                case "\v":
                case "\f":
                case "\t":
                    $startPos ++;
                    while ($this->position < $this->srcCodeLen && strpos(" \n\v\f\t", $this->srcCode[$this->position]) !== FALSE) {
                        $this->position ++;
                        $startPos ++;
                    }
                    break;
                // 注释. 忽略#后本行所有字符
                case '#':
                    $startPos ++;
                    while ($this->position < $this->srcCodeLen && $this->srcCode[$this->position] !== "\n") {
                        $this->position ++;
                        $startPos ++;
                    }
                    break;
                case ',':
                    $token = Token::buildToken($literal, $startPos, Token::COMMA);
                    break;
                case ';':
                    $token = Token::buildToken($literal, $startPos, Token::SEMICOLON);
                    break;
                case '|':
                    $token = Token::buildToken($literal, $startPos, Token::PIPE);
                    break;
                case ':':
                    $token = Token::buildToken($literal, $startPos, Token::COLON);
                    break;
                case '{':
                    $token = Token::buildToken($literal, $startPos, Token::BLOCK_START);
                    break;
                case '}':
                    $token = Token::buildToken($literal, $startPos, Token::BLOK_END);
                    break;
                case '(':
                    $token = Token::buildToken($literal, $startPos, Token::BRACKET_START);
                    break;
                case ')':
                    $token = Token::buildToken($literal, $startPos, Token::BRACKET_END);
                    break;
                case '@':
                    $token = Token::buildToken($literal, $startPos, Token::AT);
                    break;
                case '*':
                    $token = Token::buildToken($literal, $startPos, Token::STAR);
                    break;
                case '"':
                case "'":
                    $quote  = $literal;
                    $quoted = FALSE;
                    while ($this->position < $this->srcCodeLen) {
                        $ch = $this->srcCode[$this->position];
                        $this->position ++;
                        // 忽略转义字符. 直接补上下一个字符
                        if ($ch === '\\') {
                            $literal .= @$this->srcCode[$this->position]; // 如果到结尾直接认为空字符串即可
                            $this->position ++;
                        // 引号配对闭合
                        } else if ($ch === $quote) {
                            $literal .= $ch;
                            $quoted  = TRUE;
                            break;
                        // 其他字符直接拼接
                        } else {
                            $literal .= $ch;
                        }
                    }
                    if (!$quoted) {
                        $this->position = $startPos;
                        throw new CompileException($this->srcCode, $this->position, 'CompilerReadToken: Quote have no completed');
                    }
                    $token = Token::buildToken($literal, $startPos, Token::LITERAL_STRING);
                    break;
                case 'a': case 'b': case 'c': case 'd': case 'e': case 'f': case 'g': case 'h': case 'i':
                case 'j': case 'k': case 'l': case 'm': case 'n': case 'o': case 'p': case 'q': case 'r':
                case 's': case 't': case 'u': case 'v': case 'w': case 'x': case 'y': case 'z': case 'A':
                case 'B': case 'C': case 'D': case 'E': case 'F': case 'G': case 'H': case 'I': case 'J':
                case 'K': case 'L': case 'M': case 'N': case 'O': case 'P': case 'Q': case 'R': case 'S':
                case 'T': case 'U': case 'V': case 'W': case 'X': case 'Y': case 'Z': case '_':
                    while ($this->position < $this->srcCodeLen 
                        && strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_', $this->srcCode[$this->position]) !== FALSE) {
                        $literal .= $this->srcCode[$this->position];
                        $this->position ++;
                    }
                    if (strcasecmp($literal, 'null') === 0) {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD_NULL);
                    } else if (strcasecmp($literal, 'true') === 0) {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD_TRUE);
                    } else if (strcasecmp($literal, 'false') === 0) {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD_FALSE);
                    } else {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID);
                    }
                    break;
                case '0': case '1': case '2': case '3': case '4':
                case '5': case '6': case '7': case '8': case '9': case '-':
                    $existsDot = FALSE;
                    while ($this->position < $this->srcCodeLen && strpos('0123456789.', $this->srcCode[$this->position]) !== FALSE) {
                        if ($this->srcCode[$this->position] === '.') {
                            if ($existsDot) {
                                $this->position = $startPos;
                                throw new CompileException($this->srcCode, $this->position, 'CompilerReadToken: Here is invalid Numeric');
                            }
                            $existsDot = TRUE;
                        }
                        $literal .= $srcCode[$this->position];
                        $this->position ++;
                    }
                    $token = Token::buildToken($literal, $startPos, Token::NUMERIC);
                    break;
                default:
                    $this->position = $startPos;
                    throw new CompileException($this->srcCode, $this->position, 'CompilerReadToken: Unrecognized token');
                    break;
            }

        }
        if (is_null($token)) {
            $token = Token::buildToken('', $startPos, Token::EOF);
        }
        if (!$token->isValid($expectType)) {
            throw new CompileException($this->srcCode, $this->position, 'CompilerReadToken: Unexpected token');
        }
        return $token;
    }
    /*
语法规则:
CHARS_NUMERIC := [0-9]
CHARS_ID      := [a-zA-Z_0-9]
CHARS_ALL     := .

CHAR_LIST_ID      := CHARS_ID | CHARS_ID CHAR_LIST_ID
CHAR_LIST_NUMERIC := [ "-" ] CHARS_NUMERIC [ "." CHARS_NUMERIC ]

CHAR_LIST_STRING_LITERAL_D_QUOTES := "\"" CHARS_ALL "\""
CHAR_LIST_STRING_LITERAL_S_QUOTES := "'" CHARS_ALL "'"
CHAR_LIST_STRING_LITERAL          := CHAR_LIST_STRING_LITERAL_D_QUOTES | CHAR_LIST_STRING_LITERAL_S_QUOTES

WORD_TYPE           := CHAR_LIST_ID
WORD_DEFAULT_OR_REQ := "__opt" | "__req" | CHAR_LIST_STRING_LITERAL | CHAR_LIST_NUMERIC | CHAR_LIST_ID
WORD_FIELD_NAME     := CHAR_LIST_ID
WORD_OP_NAME        := CHAR_LIST_ID
WORD_OP_ARG         := CHAR_LIST_ID | CHAR_LIST_NUMERIC | CHAR_LIST_STRING_LITERAL

LIST_OP_ARG := WORD_OP_ARG | WORD_OP_ARG "," LIST_OP_ARG

STATEMENT_OP      := WORD_OP_NAME "(" LIST_OP_ARG ")"
STATEMENT_LIST_OP := STATEMENT_OP | STATEMENT_OP "|" STATEMENT_LIST_OP

STATEMENT_FIELD      := WORD_FIELD_NAME STATEMENT_FIELD_FILTER
STATEMENT_LIST_FIELD := STATEMENT_FIELD | STATEMENT_FIELD ";" STATEMENT_LIST_FIELD [ ";" ]

STATEMENT_BLOCK := "{" STATEMENT_LIST_FIELD "}"

STATEMENT_TYPE_OR_EXTEND := "(" WORD_TYPE  [ ":" WORD_DEFAULT_OR_REQ ] ")"

STATEMENT_FIELD_FILTER = STATEMENT_TYPE_OR_EXTEND [ STATEMENT_LIST_OP ] [ STATEMENT_BLOCK ]

ROOT_STATEMENT := STATEMENT_FIELD_FILTER
    */
    protected function revertTokenV2($token) {
        $this->position = $token->pos;
    }

    public function compile() {
        $this->compileV2('$data', 0);
        echo $this->dstCode . chr(10);
    }

    public function compileV2($dataLiteral, $indent = 0) {
        // 类型解析
        $this->readTokenV2(Token::BRACKET_START);
        $tokenType = $this->readTokenV2(Token::LITERAL_ID);
        $lcTypeName = strtolower($tokenType->literal);
        $ucTypeName = ucfirst($lcTypeName);
        $this->appendCode("\$expectData = \$this->default{$ucTypeName};\n", $indent);

        // 类型默认值处理
        $token = $this->readTokenV2(Token::COLON | Token::BRACKET_END);
        if ($token->isValid(Token::COLON)) {
            $tokenDefault = $this->readTokenV2(Token::LITERAL_ID | Token::LITERAL_STRING | Token::LITERAL_NUMERIC);
            $token = $this->readTokenV2(Token::BRACKET_END);
            $typeArg = $tokenDefault->isValid(Token::LITERAL_ID) ? "'{$tokenDefault->literal}'" : $tokenDefault->literal;
            $this->appendCode("\$this->type_{$lcTypeName}({$dataLiteral}, {$typeArg});\n", $indent);
        } else {
            $this->appendCode("\$this->type_{$lcTypeName}({$dataLiteral});\n", $indent);
        }
        $mustArray = in_array($lcTypeName, array('map', 'arr'));

        do {
            $token = $this->readTokenV2(Token::LITERAL_ID | Token::BLOCK_START | Token::EOF);
            // OP操作处理
            if ($token->isValid(Token::LITERAL_ID)) {
                $tokenOpName = $token;
                $token = $this->readTokenV2(Token::COLON | Token::PIPE | Token::SEMICOLON | Token::BLOCK_START);
                // 后跟参数列表
                if ($token->isValid(Token::COLON)) {
                // 结束当前OP
                } else if ($token->isValid(Token::PIPE)) {
                // 结束当前Op. 并返还BLOCK_START
                } else if ($token->isValid(Token::BLOCK_START)) {
                // 结束当前Field: ROOT节点. 后面应该接着读到Token::EOF
                } else if ($token->isValid(Token::SEMICOLON)) {
                }
            // 块数据操作处理
            } else if ($token->isValid(Token::BLOCK_START)) {
                // 数组检测
                if ($mustArray) {
                    $this->appendCode("if (is_array({$dataLiteral})) {\n", $indent);
                    $indent ++;
                }

                do {
                    $token = $this->readTokenV2(Token::STAR | Token::LITERAL_ID | Token::LITERAL_STRING | Token::LITERAL_NUMERIC);
                    if ($token->isValid(Token::STAR)) {
                        $this->appendCode("foreach ({$dataLiteral} as \$k => \$v) {\n", $indent);

                        $this->compileV2("{$dataLiteral}[\$k]", $indent + 1);

                        $this->appendCode("}\n", $indent);
                    } else if ($token->isValid(Token::LITERAL_STRING | Token::LITERAL_NUMERIC)) {
                        $this->compileV2("{$dataLiteral}[{$token->literal}]", $indent);
                    } else if ($token->isValid(Token::LITERAL_ID)) {
                        $this->compileV2("{$dataLiteral}['{$token->literal}']", $indent);
                    }
                } while (!$token->isValid(Token::BLOCK_END));

                // 数组检测结尾
                if ($mustArray) {
                    $indent --;
                    $this->appendCode("}\n");
                }
            }
        } while (!$token->isValid(Token::EOF | Token::SEMICOLON | Token::BRACKET_END));
        if ($token->isValid(Token::BRACKET_END)) {
            $this->revertTokenV2($token);
        }
    }
}
