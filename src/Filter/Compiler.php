<?php
namespace Ice\Filter;
class Compiler {

    // 语法版本. 如果语法规则变更, 需要修改.上层会据此变更编译产出路径
    const SYNTAX_VERSION = 1;

    public function __construct() {
    }

    protected function resetCode($srcCode) {
        $this->dstCode     = '';
        $this->srcCode     = $srcCode;
        $this->srcCodeLen  = strlen($srcCode);
        $this->line        = 1;
        $this->lineStart   = 0;
        $this->indentLevel = 0;
        $this->indentStr   = '    ';
    }

    public $dstCode;
    public $srcCode;
    public $srcCodeLen;
	public $line;
	public $lineStart;
    protected $indentLevel;
    protected $indentStr;

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
    public function compile($srcCode, $proxyNamespace, $proxyClassName, $baseFilterClassName) {
        try {
            $this->resetCode($srcCode);

            $this->recursiveCompile('$data', '$expectData', 3);
            $dstCode = '<' . "?php
namespace $proxyNamespace;
class {$proxyClassName} extends {$baseFilterClassName} {
    public function filter(\$data) {
        try {
{$this->dstCode}
        } catch (\Ice\Filter\RunException \$e) {
            return FALSE;
        }
        return \$this->expectData(\$expectData, \$data);
    }
}";
        } catch (CompileException $e) {
            return FALSE;
        }

        return $dstCode;
    }

    protected function recursiveCompile($dataLiteral, $expectDataLiteral, $indent = 0) {
        // 类型解析
        $this->readToken(Token::BRACKET_START);
        $tokenType = $this->readToken(Token::LITERAL_ID);
        $lcTypeName = strtolower($tokenType->literal);
        $ucTypeName = ucfirst($lcTypeName);
        $this->appendCode("{$expectDataLiteral} = \$this->default{$ucTypeName};\n", $indent);

        // 类型默认值处理
        $token = $this->readToken(Token::COLON | Token::BRACKET_END);
        if ($token->isValid(Token::COLON)) {
            $tokenDefault = $this->readToken(Token::LITERAL_ID | Token::LITERAL_STRING | Token::LITERAL_NUMERIC);
            $token = $this->readToken(Token::BRACKET_END);
            $typeArg = $tokenDefault->isValid(Token::LITERAL_ID) && !$tokenDefault->isValid(Token::KEYWORD) ? "'{$tokenDefault->literal}'" : $tokenDefault->literal;
            $this->appendCode("\$this->type_{$lcTypeName}({$dataLiteral}, {$typeArg});\n", $indent);
        } else {
            $this->appendCode("\$this->type_{$lcTypeName}({$dataLiteral});\n", $indent);
        }
        $mustArray = in_array($lcTypeName, array('map', 'arr'));

		// OP, 继承, 块数据列表
        do {
            $token = $this->readToken(Token::LITERAL_ID | Token::BLOCK_START | Token::AT, FALSE);
			if (!$token) {
				break;
			}
			// OP
			if ($token->isValid(Token::LITERAL_ID)) {
				$tokenOpName = $token;
				$token = $this->readToken(Token::COLON, FALSE);
				$tmpCode = "\$this->op_{$tokenOpName->literal}({$dataLiteral}";
				if ($token) {
					// 处理参数列表
					do {
						$tokenArg = $this->readToken(Token::LITERAL_ID | Token::LITERAL_STRING | Token::LITERAL_NUMERIC);
						$tmpCode .= $tokenArg->isValid(Token::LITERAL_ID) && !$tokenArg->isValid(Token::KEYWORD)
                                ? ", '{$tokenArg->literal}'"
                                : ", {$tokenArg->literal}";
						$token = $this->readToken(Token::COMMA, FALSE);
						if (empty($token)) {
							break;
						}
					} while (TRUE);
				}
				$tmpCode .= ");\n";
				$this->appendCode($tmpCode, $indent);
				$this->readToken(Token::PIPE, FALSE);
			// 块数据
			} else if ($token->isValid(Token::BLOCK_START)) {
                // 数组检测
                if ($mustArray) {
                    $this->appendCode("if (is_array({$dataLiteral}) || {$dataLiteral} instanceof \\ArrayAccess) {\n", $indent);
                    $indent ++;
                }

                do {
					$this->appendEmptyLine();
                    $token = $this->readToken(Token::STAR | Token::LITERAL_ID | Token::LITERAL_STRING | Token::LITERAL_NUMERIC, FALSE);
					if (!$token) {
						break;
					}
					// 星号匹配: 所有子元素应用相同的过滤规则
                    if ($token->isValid(Token::STAR)) {
                        $this->appendCode("foreach ({$dataLiteral} as \$k => \$v) {\n", $indent);

						$GLOBALS['debug'] = TRUE;
                        $this->recursiveCompile("{$dataLiteral}[\$k]", "{$expectDataLiteral}[\$k]", $indent + 1);

                        $this->appendCode("}\n", $indent);
                    } else if ($token->isValid(Token::LITERAL_STRING | Token::LITERAL_NUMERIC)) {
                        $this->recursiveCompile("{$dataLiteral}[{$token->literal}]", "{$expectDataLiteral}[{$token->literal}]", $indent);
                    } else if ($token->isValid(Token::LITERAL_ID)) {
                        $this->recursiveCompile("{$dataLiteral}['{$token->literal}']", "{$expectDataLiteral}['{$token->literal}']", $indent);
                    }
                } while (!$token->isValid(Token::BLOCK_END));

				$this->readToken(Token::BLOCK_END);

                // 数组检测结尾
                if ($mustArray) {
                    $indent --;
                    $this->appendCode("}\n", $indent);
                }


			// 继承
			} else if ($token->isValid(Token::AT)) {
				$token = $this->readToken(Token::LITERAL_STRING);
				$this->appendCode("\$this->extend_filter({$dataLiteral}, {$token->literal});\n", $indent);
				$this->readToken(Token::PIPE, FALSE);
			}
        } while (TRUE);
		$this->readToken(Token::SEMICOLON, FALSE);
    }
    protected function readToken($expectType = 0xFFFFFFFF, $strict = TRUE) {
        $startPos = $this->position;
        $token = null;
        while (!isset($token) && $this->position < $this->srcCodeLen) {
            $literal = $this->srcCode[$this->position];
            $this->position ++;
            switch ($literal) {
                // 空白字符. 直接跳过
                case ' ':
                case "\n":
                case "\v":
                case "\f":
                case "\t":
					if ($literal == "\n") {
						$this->line ++;
						$this->lineStart = min($this->srcCodeLen, $this->position);
					}
                    $startPos ++;
                    while ($this->position < $this->srcCodeLen && strpos(" \n\v\f\t", $this->srcCode[$this->position]) !== FALSE) {
						if ($this->srcCode[$this->position] == "\n") {
							$this->line ++;
							$this->lineStart = min($this->srcCodeLen, $this->position);
						}
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
                    $token = Token::buildToken($literal, $startPos, Token::BLOCK_END);
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
                        throw new CompileException($this, 'CompilerReadToken: Quote have no completed');
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
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD);
                    } else if (strcasecmp($literal, 'true') === 0) {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD);
                    } else if (strcasecmp($literal, 'false') === 0) {
                        $token = Token::buildToken($literal, $startPos, Token::LITERAL_ID | Token::KEYWORD);
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
                                throw new CompileException($this, 'CompilerReadToken: Numeric literal multi dot');
                            }
                            $existsDot = TRUE;
                        }
                        $literal .= $srcCode[$this->position];
                        $this->position ++;
                    }
                    $token = Token::buildToken($literal, $startPos, Token::LITERAL_NUMERIC);
                    break;
                default:
                    $this->position = $startPos;
                    throw new CompileException($this, 'CompilerReadToken: Unrecognized token');
                    break;
            }

        }
        if (is_null($token)) {
            $token = Token::buildToken('', $startPos, Token::EOF);
        }
        if (!$token->isValid($expectType)) {
			if ($strict) {
				throw new CompileException($this, sprintf('CompilerReadToken: Unexpected token and strict mode(expect: %s, get: %s)', Token::typeToString($expectType), Token::typeToString($token)));
			} else {
				$this->revertToken($token);
				return null;
			}
        }
        return $token;
    }

    protected function revertToken($token) {
        $this->position = $token->pos;
    }

    protected function appendCode($dstCode = "\n", $indent = 0) {
        $this->dstCode .= str_repeat($this->indentStr, $this->indentLevel + $indent) . $dstCode;
    }
	protected function appendEmptyLine() {
		$this->dstCode .= "\n";
	}

}
