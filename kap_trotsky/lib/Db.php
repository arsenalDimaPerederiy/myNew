<?php

class KapDb
{
    private $_db;
    private $_SQL_RANDOM_KAP;
    private $_settings = false;
    private $_prefix = '';

    public function __construct($dir, $db_config = false)
    {
        if(isset($db_config['dsn']) && $db_config['dsn'])
            $this->init($db_config);
        else
            $this->init_sqlite($dir);
    }

    public function __destruct()
    {
        $this->_db = null;
    }

    public function query($sql, $data = array())
    {
        $stmt = $this->_db->prepare($sql);

        foreach($data as $name => $value)
        {
            $stmt->bindValue($name, htmlspecialchars($value));
        }

        if(strstr($sql, 'SELECT') || strstr($sql, 'SHOW'))
        {
            $stmt->execute();
            if(strstr($sql, 'LIMIT 1;'))
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            else
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else
            $data = $stmt->execute();

        $stmt->closeCursor();
        $stmt = null;

        return $data;
    }

    public function getRandomLinks($limit)
    {
        return $this->_db->query($this->_SQL_RANDOM_KAP.' LIMIT '.$limit);
    }

    public function multiQuery($sql, $data)
    {
        $stmt = $this->_db->prepare($sql);

        $this->_db->beginTransaction();

        foreach($data as $field)
        {
            foreach($field as $name => $value)
            {
                $stmt->bindValue($name, htmlspecialchars($value));
            }

            $stmt->execute();
        }
        $status = $this->_db->commit();

        $stmt->closeCursor();
        $stmt = null;

        return $status;
    }

    public function getParam($name)
    {
        if( ! $this->_settings)
        {
            $result = $this->query('SELECT * FROM `'.$this->getTableName('settings').'`');

            if($result)
            {
                foreach($result as $param)
                {
                    $this->_settings[$param['name']] = $param['value'];
                }
            }
        }

        if(isset($this->_settings[$name]))
            return $this->_settings[$name];

        return false;
    }

    public function setParam($name, $value)
    {
        $this->_settings[$name] = $value;

        return $this->query('REPLACE INTO `'.$this->getTableName('settings').'` (`name`,`value`) VALUES (:name, :value)',
            array(':name' => $name, ':value' => $value)
        );
    }
    public function getTableName($table)
    {
        return $this->_prefix . $table;
    }

    private function init($db_config)
    {
        $this->_prefix = $db_config['table_prefix'];

        $this->_SQL_RANDOM_KAP = 'SELECT * FROM `'.$this->getTableName('kap').'` ORDER BY RAND()';

        try  {
            $this->_db = new PDO($db_config['dsn'], $db_config['user'], $db_config['pass']);

            $this->_db->exec('SET NAMES utf8;');

            if( ! $this->checkExistsTables())
            {
                $this->_db->exec('CREATE TABLE IF NOT EXISTS `'.$this->getTableName('kap').'` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `url` varchar(255) NOT NULL,
                      `phrase` varchar(255) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `phrase` (`phrase`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
                );

                $this->_db->exec('CREATE TABLE IF NOT EXISTS `'.$this->getTableName('links').'` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `page_url` varchar(255) NOT NULL,
                      `phrase` varchar(255) NOT NULL,
                      `target_url` varchar(255) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `phrase` (`phrase`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
              );

                $this->_db->exec('CREATE TABLE IF NOT EXISTS `'.$this->getTableName('settings').'` (
                      `name` varchar(255) NOT NULL,
                      `value` varchar(255) NOT NULL,
                      PRIMARY KEY (`name`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
              );

              $this->afterInit();
          }

        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function afterInit()
    {
        $this->setParam('get_params', 'gclid,_OPENSTAT,utm_source,utm_medium,utm_campaign,yclid');
    }

    private function checkExistsTables()
    {
        return (bool) $this->query('SHOW TABLE STATUS LIKE "'.$this->getTableName('kap').'"');
    }

    private function init_sqlite($dir)
    {
        $this->_SQL_RANDOM_KAP = 'SELECT * FROM `'.$this->getTableName('kap').'` ORDER BY RANDOM()';

        $file = $dir.'/kap.db';
        $dsn = 'sqlite:'.$file;
        $new_file = false;

        if( ! file_exists($file))
        {
            $f = fopen($file,'w');

            if($f)
                fclose($f);
            else
                throw new Exception('Создать файл с базой не удалось');

            $new_file = true;
        }

        try  {
           $this->_db = new PDO($dsn);
            //$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

           if($new_file)
           {
               $this->_db->exec("CREATE TABLE IF NOT EXISTS `".$this->getTableName('kap')."` (
                    `id` INTEGER PRIMARY KEY,
                    `url` varchar(255) NOT NULL,
                    `phrase` varchar(255) NOT NULL
                )");

                $this->_db->exec("CREATE UNIQUE INDEX IF NOT EXISTS `phrase` ON `".$this->getTableName('kap')."` (`phrase`)");

                $this->_db->exec("CREATE TABLE IF NOT EXISTS ".$this->getTableName('links')." (
                    `id` INTEGER PRIMARY KEY,
                    `page_url` varchar(255) NOT NULL,
                    `phrase` varchar(255) NOT NULL,
                    `target_url` varchar(255) NOT NULL
                 )");

                $this->_db->exec("CREATE UNIQUE INDEX IF NOT EXISTS `phrase` ON `".$this->getTableName('links')."` (`phrase`)");

                $this->_db->exec("CREATE TABLE IF NOT EXISTS ".$this->getTableName('settings')." (
                    `name`  varchar(255) NOT NULL,
                    `value` varchar(255) NOT NULL
                 )");

                $this->_db->exec("CREATE UNIQUE INDEX IF NOT EXISTS `name` ON `".$this->getTableName('settings')."` (`name`)");

                $this->afterInit();
            }

        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
