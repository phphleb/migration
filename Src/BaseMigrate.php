<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

abstract class BaseMigrate
{
    /** @internal  */
    protected ?string $dbType;
    
    /** @internal  */
    protected string $tableName;
    
    /** @internal  */
    private array $sql = [];

    /** @param string|null $dbType
     * @param string $tableName
     * @internal
     */
    public function __construct(string $dbType = null, $tableName = 'migrations')
    {
        $this->dbType = $dbType;
        $this->tableName = $tableName;
    }
    
    /** @internal  */
    public function getSql(): array
    {
         return $this->sql;
    }

    /**
     * This adds a line containing the SQL query to be executed in the migration.
     *
     * Здесь добавляется строка содержащая запрос SQL, который должен выполниться в миграции.
     *
     * $this->addSql("INSERT INTO `table_name` (`cell_name`) values ('example_value')");
     *
     * @param string $query
     */
    protected function addSql(string $query): void
    {
        $this->sql[] = $query;
    }

    abstract function run();
}

