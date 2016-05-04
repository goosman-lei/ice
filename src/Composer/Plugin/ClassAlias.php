<?php
namespace Ice\Composer\Plugin;
use \Composer\Script\Event;
use \Composer\Install\PackageEvent;

class ClassAlias {

    const ALIAS_CFG_FILE = 'alias.json';
    const AUTOLOAD_ALIAS_FILE = 'autoload_alias.php';

    protected static $vendorDir;
    protected static $rootDir;

    public static function postUpdate(Event $event) {
        self::$vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        self::$rootDir   = realpath(self::$vendorDir . '/..');
        $aliasFile       = self::$rootDir . '/' . self::ALIAS_CFG_FILE;
        if (!is_file($aliasFile)) {
            return self::generateAliasMap(array());
        }
        $aliasConfigStr = file_get_contents($aliasFile);
        $aliasConfig    = json_decode($aliasConfigStr, TRUE);
        if (empty($aliasConfig) || !is_array($aliasConfig)) {
            return self::generateAliasMap(array());
        }
        return self::generateAliasMap($aliasConfig);
    }

    protected static function generateAliasMap($aliasConfig) {
        $autoloadAliasFile   = self::AUTOLOAD_ALIAS_FILE;

        if (empty($aliasConfig)) {
            if (is_file(self::$vendorDir. '/composer/' . $autoloadAliasFile)) {
                unlink(self::$vendorDir. '/composer/' . $autoloadAliasFile);
            }
            return ;
        }

        $aliasCfgCode = var_export($aliasConfig, TRUE);
        $aliasAutoloadCode = <<<EOF
<?php
\$aliasMapping = $aliasCfgCode;
foreach (\$aliasMapping as \$className => \$aliasName) {
    class_alias(\$className, \$aliasName);
}
EOF;
        file_put_contents(self::$vendorDir. '/composer/' . $autoloadAliasFile, $aliasAutoloadCode);
        $autoloadFile        = self::$vendorDir . '/autoload.php';
        $autoloadFileContent = file_get_contents($autoloadFile);
        $pattern     = ';return (ComposerAutoloaderInit\w+::getLoader\(\))\;;i';
        $replacement = "\$loader = \$1;\n\nrequire_once __DIR__ . '/composer' . '/{$autoloadAliasFile}';\n\nreturn \$loader;";
        $autoloadFileContent = preg_replace($pattern, $replacement, $autoloadFileContent);
        file_put_contents($autoloadFile, $autoloadFileContent);
    }
}
