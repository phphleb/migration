<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

class BaseMigrate
{
    /** @internal  */
    protected ?object $pdo;
    
    /** @internal  */
    protected string $tableName;

    /** @internal  */
    protected ?string $directory;

    /**
     * Name of the current database.
     * @var string|null
     */
    protected ?string $dbName;

    /**
     * @param object|null $pdo    - An initialized PDO object.
     *                            - Инициализированный объект PDO.
     *
     * @param string $tableName   - The name of the table to store migration data.
     *                            - Название таблицы для хранения данных миграций.
     *
     * @param string $dir         - Directory for migration files in the project.
     *                            - Директория для файлов миграций в проекте.
     */
    public function __construct(?object $pdo = null, $tableName = 'migrations', ?string $dir = null)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        if (defined('HLEB_GLOBAL_DIRECTORY')) {
            $baseDir = HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR;
            if (is_null($dir)) {
                if (file_exists($baseDir . 'database' . DIRECTORY_SEPARATOR . 'migrations')) {
                    $this->directory = $baseDir . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                } else {
                    $this->directory = $baseDir . 'migrations';
                }
            } else {
                $this->directory = rtrim($dir, '\\/ ');
            }
        } else {
            $this->directory = rtrim($dir, '\\/ ');
        }
    }
    

}

