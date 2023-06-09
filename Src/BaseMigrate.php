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
     * @param string|null $dir    - Directory for migration files in the project.
     *                            - Директория для файлов миграций в проекте.
     */
    public function __construct(?object $pdo = null, string $tableName = 'migrations', ?string $dir = null)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $dir = realpath($dir ? rtrim($dir, '\\/ ') : __DIR__ . '/../../../../migrations');
        if (!$dir) {
            throw new MigrateException('Error! Specify the correct path to the folder for storing migrations.');
        }
        $this->directory = $dir;
    }
}

