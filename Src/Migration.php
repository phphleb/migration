<?php

declare(strict_types=1);

namespace Phphleb\Migration\Src;

use Hleb\Main\DB;
use Hleb\Main\PdoManager;

class Migration extends BaseMigrate
{
    private bool $status = false;

    public function run(): array
    {
        DB::dbQuery("CREATE TABLE IF NOT EXISTS {$this->tableName} (label bigint NOT NULL, datecreate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP)", $this->dbType);
        $rows = DB::run("SELECT * FROM {$this->tableName} ORDER BY label ASC", [], $this->dbType)->fetchAll(\PDO::FETCH_ASSOC);
        $list = [];
        $result = [];
        foreach ($rows as $key => $row) {
            $list[(int)$row['label']] = [];
        }

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
                    if ($index && !isset($list[$index])) {
                        require $this->directory . DIRECTORY_SEPARATOR . $file;
                        /** @var  BaseMigrate $object */
                        $object = new $className($this->dbType, $this->tableName, $this->directory);
                        $object->run();
                        $list[$index] = ['sql' => $object->getSql(), 'name' => $className, 'index' => $index];
                    }
                }
            }
        }
        foreach ($list as $key => $item) {
            if (!empty($item['sql'])) {
                foreach ($item['sql'] as $query) {
                    DB::dbQuery((stripos(trim($query), 'EXPLAIN ') !== 0 ? 'EXPLAIN ' : '') . $query, $this->dbType);
                }
            } else {
                unset($list[$key]);
            }

        }
        if (!$this->status) {
            /** @var PdoManager $connection */
            $connection = DB::getPdoInstance($this->dbType);
            try {
                $connection->beginTransaction();
                foreach ($list as $item) {
                    $result[] = $item['name'];
                    foreach ($item['sql'] as $query) {
                            $connection->prepare($query)->execute();
                            $connection->prepare("INSERT INTO {$this->tableName} (label) VALUES (?)")->execute([$item['index']]);
                    }
                }
            } catch (\PDOException $e) {
                $connection->rollBack();
                throw $e;
            }
            $connection->commit();
        } else {
            foreach ($list as $item) {
                $result[] = $item['name'];
            }
        }

        return $result;
    }

    public function create(string $name)
    {
        if (empty($name) || !preg_match('~^[a-z0-9_]*$~i', $name)) {
            throw new MigrateException('Wrong migration name ( A-Za-z0-9_ )');
        }
        $milliseconds = floor(microtime(true) * 1000);
        $content = '<?php

class Migration_' . $milliseconds . '_' . $name . ' extends \Phphleb\Migration\Src\BaseMigrate
{
   public function run()
   {
       $this->addSql(/* ... */);
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

    public function status(): array
    {
        $this->status = true;
        return $this->run();
    }
}