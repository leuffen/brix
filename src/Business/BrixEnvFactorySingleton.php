<?php

namespace Leuffen\Brix\Business;

use Lack\Keystore\KeyStore;
use Leuffen\Brix\Type\BrixEnv;
use Leuffen\Brix\Type\T_BrixConfig;

class BrixEnvFactorySingleton
{

    public static function getInstance() : BrixEnvFactorySingleton
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }


    public function getEnv() : BrixEnv
    {
        $curDir = phore_dir(getcwd());
        $brixConfig = null;
        while (true) {
            $brixFile = $curDir->withFileName(".brix.yml");
            if ($brixFile->exists()) {
                $brixConfig = $brixFile->get_yaml(T_BrixConfig::class);
                break;
            }
            $curDir = $curDir->withParentDir();
            if ((string)$curDir === "/")
                throw new \InvalidArgumentException("Cannot find .brix.yml in current or parent directories.");
        }
        /* @var $brixConfig T_BrixConfig */
        $rootDir = $curDir;

        $contextCombined = $brixConfig->context ?? "";
        if (isset ($brixConfig->context_file)) {
            $contextCombined .= "\n" . phore_file($brixConfig->context_file)->get_contents();
        }

        return new BrixEnv(
            KeyStore::Get(),
            $brixConfig,
            $curDir,
            $curDir->withRelativePath($brixConfig->output_dir)->asDirectory(),
            $curDir->withRelativePath($brixConfig->templates_dir)->asDirectory(),
            $contextCombined
        );
    }


}
