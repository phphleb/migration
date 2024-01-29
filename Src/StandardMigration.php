<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

abstract class StandardMigration extends BaseMigrate
{

    /** @internal  */
    private array $sql = [];

    /**
     * Method for collecting SQL queries to perform the migration.
     *
     * Метод сбора SQL-запросов для выполнения миграции.
     */
    abstract public function up(\PDO $db);

    /**
     * Method for collecting SQL queries for rolling back migration.
     * Must be the opposite of requests from the `up` method.
     *
     * Метод сбора SQL-запросов для отката миграции.
     * Должен быть противоположен запросам из метода `up`.
     */
    abstract public function down();


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

}
