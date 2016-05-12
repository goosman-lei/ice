<?php
namespace Ice\Filter;

class CompileException extends \Exception {
    public function __construct($compiler, $message) {
        $lineEnd    = strpos($compiler->srcCode, "\n", $compiler->lineStart);
        $codeLine   = substr($compiler->srcCode, $compiler->lineStart, $compiler->position - $compiler->lineStart)
            . ' 『Here =>』 ' . $compiler->srcCode[$compiler->position]
            . substr($compiler->srcCode, $compiler->position + 1, $lineEnd - $compiler->position);
        $codeLine   = rtrim($codeLine, "\n");

        $dstMessage = sprintf("[%s] occur line %d: ###%s###", $message, $compiler->line, $codeLine);
        parent::__construct($dstMessage);
    }
}
