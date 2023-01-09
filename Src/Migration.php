<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

use Hleb\Main\DB;
use Hleb\Main\PdoManager;

class Migration extends BaseMigrate
{
    private const TYPE_UP = 1;
    private const TYPE_DOWN = 2;
    private const TYPE_STATUS = 3;

    /**
     * @return array
     * @throws MigrateException
     */
    public function run(): array
    {
        return $this->action(self::TYPE_UP);
    }

    /**
     * @param int $steps
     * @return array
     * @throws MigrateException
     */
    public function rollback(int $steps = 1): array
    {
        return $this->action(self::TYPE_DOWN, $steps);
    }

    /**
     * @return array
     * @throws MigrateException
     */
    public function status(): array
    {
        return $this->action(self::TYPE_STATUS);
    }

    /**
     * @param string $name
     * @return string
     * @throws MigrateException
     */
    public function create(string $name)
    {
        if (empty($name) || !preg_match('~^[a-z0-9_]*$~i', $name)) {
            throw new MigrateException('Wrong migration name ( A-Za-z0-9_ )');
        }
        $milliseconds = floor(microtime(true) * 1000);
        $content = '<?php

class Migration_' . $milliseconds . '_' . $name . ' extends \Phphleb\Migration\Src\StandardMigration
{
   public function up()
   {
       $this->addSql(/* ... */);
   }      
   
   public function down()
   {
       // $this->addSql(/* ... */);
   }  
}
';

        $newFile = $this->directory . DIRECTORY_SEPARATOR . 'Migration_' . $milliseconds . '_' . $name . '.php';
        if (file_exists($newFile)) {
            throw new MigrateException('A file with the same name already exists!');
        }
        if (!file_exists(dirname($newFile))) {
            throw new MigrateException("Migrations folder {$this->directory} does not exist! It needs to be created.");
        }
        $files = scandir($this->directory);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && !is_dir($file)) {
                $parts = explode('_', basename($file, '.php'));
                unset($parts[0], $parts[1]);
                if (implode('_' , $parts) === $name) {
                    throw new MigrateException('A name with the same name already exists!');
                }
            }
        }
        file_put_contents($newFile, $content);

        return $newFile;
    }

    private function action(int $type = self::TYPE_UP, ?int $steps = null): array
    {
        DB::dbQuery("CREATE TABLE IF NOT EXISTS {$this->tableName} (label bigint NOT NULL, datecreate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP)", $this->dbType);
        $rows = DB::run("SELECT * FROM {$this->tableName} ORDER BY label ASC", [], $this->dbType)->fetchAll(\PDO::FETCH_ASSOC);
        $list = [];
        $result = [];
        foreach ($rows as $key => $row) {
            $list[(int)$row['label']] = [];
        }

        $dbName = DB::getConfig($this->dbType)['dbname'] ?? null;

        $files = scandir($this->directory);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && !is_dir($file)) {
                $className = basename($file, '.php');
                if ($className) {
                    $parts = explode('_', $className);
                    if (empty($parts[1]) || !is_numeric($parts[1]) || $parts[0] !== 'Migration' || count($parts) < 3) {
                        throw new MigrateException("Wrong migration name: $className" );
                    }
                    $index = (int)$parts[1];
                    if ($index && ((is_int($steps) && isset($list[$index])) || (is_null($steps) && !isset($list[$index])))) {
                        require $this->directory . DIRECTORY_SEPARATOR . $file;
                        /** @var  StandardMigration $object */
                        $object = new $className($this->dbType, $this->tableName, $this->directory, $dbName);
                        if ($type === self::TYPE_UP) {
                            $object->up();
                        } else if ($type === self::TYPE_DOWN) {
                            $object->down();
                        }
                        $list[$index] = ['sql' => $object->getSql(), 'name' => $className, 'index' => $index];
                    }
                }
            }
        }

        if ($type === self::TYPE_DOWN) {
            krsort($list, SORT_NUMERIC);
            if (is_int($steps) && $steps > 0) {
                $list = array_slice($list, 0, $steps);
            }
        } else {
            ksort($list, SORT_NUMERIC);
        }

        if ($type !== self::TYPE_STATUS) {
            /** @var PdoManager $connection */
            $connection = DB::getPdoInstance($this->dbType);
            try {
                $connection->beginTransaction();
                foreach ($list as $item) {
                    if (empty($item['sql'])) {
                        continue;
                    }
                    $result[] = $item['name'];
                    foreach ($item['sql'] as $query) {
                        $connection->prepare($query)->execute();
                    }
                    if ($type === self::TYPE_DOWN) {
                        $connection->prepare("DELETE FROM {$this->tableName} WHERE label = ?")->execute([$item['index']]);
                    } else {
                        $connection->prepare("INSERT INTO {$this->tableName} (label) VALUES (?)")->execute([$item['index']]);
                    }
                }
            } catch (\Throwable $e) {
                $connection->rollBack();
                throw $e;
            }
            $connection->commit();
        } else {
            foreach ($list as $item) {
                if ($item['name']) {
                    $result[] = $item['name'];
                }
            }
        }

        return $result;
    }
}