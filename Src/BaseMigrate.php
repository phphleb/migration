<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

class BaseMigrate
{
    /** @internal  */
    protected ?\PDO $pdo;
    
    /** @internal  */
    protected string $tableName;

    /** @internal  */
    protected string $directory;

    /** @internal  */
    protected bool $notify;

    /**
     * Name of the current database.
     * @var string|null
     */
    protected ?string $dbName;

    /**
     * @param \PDO|null $pdo - An initialized PDO object.
     *                            - Инициализированный объект PDO.
     *
     * @param string $tableName - The name of the table to store migration data.
     *                            - Название таблицы для хранения данных миграций.
     *
     * @param string $dir         - Directory for migration files in the project.
     *                            - Директория для файлов миграций в проекте.
     *
     * @param bool $notify - Display notifications about the actions taken.
     *                            - Выводить уведомления о произведённых действиях.
     */
    public function __construct(?\PDO $pdo, string $tableName, string $dir, bool $notify = false)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        if (!\file_exists($dir)) {
            throw new MigrateException("Migrations directory $dir not created! You need to create it.");
        }
        $this->directory = \realpath(\rtrim($dir, '\\/ '));
        $this->notify = $notify;
    }
}

