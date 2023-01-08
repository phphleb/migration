<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

class BaseMigrate
{
    /** @internal  */
    protected ?string $dbType;
    
    /** @internal  */
    protected string $tableName;

    /** @internal  */
    protected ?string $directory;

    /**
     * @param string|null $dbType
     * @param string $tableName
     * @param string $dir
     */
    public function __construct(?string $dbType = null, $tableName = 'migrations', ?string $dir = null)
    {
        $this->dbType = $dbType;
        $this->tableName = $tableName;
        $baseDir = HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR;

        if (is_null($dir)) {
            if (file_exists( $baseDir . 'database' . DIRECTORY_SEPARATOR . 'migrations')) {
                $this->directory = $baseDir . 'database' . DIRECTORY_SEPARATOR . 'migrations';
            } else {
                $this->directory = $baseDir . 'migrations';
            }
        } else {
            $this->directory = rtrim($dir, '\\/ ');
        }
    }
    

}

