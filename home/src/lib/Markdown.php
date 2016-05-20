<?php
namespace ice\home\Lib;
class Markdown {
    protected $markdownPath;

    public function __construct() {
        $this->markdownPath = \F_Ice::$ins->mainApp->config->get('app.root_path') . '/markdown';
        $this->compiledPath = \F_Ice::$ins->mainApp->config->get('app.run_path') . '/markdown';
    }

    public function render($path) {
        $srcFile = $this->markdownPath . '/' . ltrim(dirname($path), '/') . '/' . basename($path, '.md')  . '.md';
        $dstFile = $this->compiledPath . '/' . ltrim(dirname($path), '/') . '/' . basename($path, '.html')  . '.html';

        if (!is_file($srcFile)) {
            return '';
        }

        $reCompile = FALSE;
        if (!is_file($dstFile)) {
            if (!mkdir(dirname($dstFile), 0777, TRUE)) {
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'src_file' => $srcFile,
                    'dst_file' => $dstFile,
                ), \H_ECode::MARKDOWN_COMPILED_DIR_MAKE_FAILED);
                return '';
            }
            $reCompile = TRUE;
        } else if (filemtime($dstFile) < filemtime($srcFile)) {
            $reCompile = TRUE;
        }

        if (!$reCompile) {
            return file_get_contents($dstFile);
        }

        $markdown   = new \Michelf\Markdown();
        $srcContent = file_get_contents($srcFile);
        $dstContent = $markdown->transform($srcContent);

        file_put_contents($dstFile, $dstContent);

        return $dstContent;
    }
}