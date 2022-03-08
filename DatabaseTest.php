<?php

class DatabaseTest
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string|null
     */
    protected $connectionError;

    /**
     * @var string|null
     */
    protected $lastError;

    /**
     * @var \PDO|null
     */
    protected $pdo;

    /**
     * Initialize new MySQL instance
     *
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $database
     */
    public function __construct($host, $port, $username, $password, $database)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * Connect to PDF
     *
     * @return void
     */
    public function connect()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            $this->host,
            $this->port,
            $this->database
        );

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            $this->connectionError = $e->getMessage();
        }
    }

    /**
     * Get the database version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Test database version privilege
     *
     * @return bool
     */
    public function testVersion($min)
    {
        return version_compare($this->getVersion(), $min) >= 0;
    }

    /**
     * Test DROP privilege
     *
     * @return void
     */
    public function testDropTable()
    {
        try {
            // Even if there is no table will fail if the privileges are not granted
            $this->dropTable();
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Test CREATE privilege
     *
     * @return void
     */
    public function testCreateTable()
    {
        try {
            $this->createTable();
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test INSERT privilege
     *
     * @return void
     */
    public function testInsert()
    {
        try {
            $this->createTable();
            $this->insertRow();
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test SELECT privilege
     *
     * @return void
     */
    public function testSelect()
    {
        try {
            $this->createTable();
            $this->pdo->query('SELECT * from test_table');
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test UPDATE privilege
     *
     * @return void
     */
    public function testUpdate()
    {
        try {
            $this->createTable();
            $this->insertRow();
            $sql  = 'UPDATE test_table SET text_column = ? WHERE id= ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['Concord CRM', 1]);
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test DELETE privilege
     *
     * @return void
     */
    public function testDelete()
    {
        try {
            $this->createTable();
            $this->insertRow();
            $sql  = 'DELETE FROM test_table WHERE id= ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([1]);
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test ALTER privilege
     *
     * @return void
     */
    public function testAlter()
    {
        try {
            $this->createTable();
            $this->insertRow();
            $this->pdo->exec('ALTER table test_table ADD INDEX(id)');
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test INDEX privilege
     *
     * @return void
     */
    public function testIndex()
    {
        try {
            $this->createTable();
            $this->insertRow();
            $this->pdo->exec('CREATE INDEX text_column_index ON test_table (text_column(10));');
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable();
        }
    }

    /**
     * Test the references
     *
     * @return void
     */
    public function testReferences()
    {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS `test_table` (
                `id` bigint UNSIGNED NOT NULL,
                `test_user_id` bigint UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $this->pdo->exec('ALTER TABLE `test_table` ADD PRIMARY KEY (`id`)');

            $this->pdo->exec('CREATE TABLE IF NOT EXISTS `test_another_table` (
                `id` bigint UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $this->pdo->exec('ALTER TABLE `test_another_table` ADD PRIMARY KEY (`id`)');

            $this->pdo->exec('ALTER TABLE `test_table` ADD CONSTRAINT `test_user_id_foreign` FOREIGN KEY (`test_user_id`) REFERENCES `test_another_table` (`id`)');
        } catch (PDOException | Exception $e) {
            $this->lastError = $e->getMessage();
        } finally {
            $this->dropTable('test_table');
            $this->dropTable('test_another_table');
        }
    }

    /**
     * Check if is connected to the database
     *
     * @return boolean
     */
    public function isConnected()
    {
        return is_null($this->getConnectionError());
    }

    /**
     * Get the connection error
     *
     * @return string|null
     */
    public function getConnectionError()
    {
        return $this->connectionError;
    }

    /**
     * Get the last test error
     *
     * @return string|null
     */
    public function lastError()
    {
        return $this->lastError;
    }

    /**
     * Drop table
     *
     * @param  string|null $tableName
     *
     * @return void
     */
    protected function dropTable($tableName = null)
    {
        $this->pdo->exec('DROP TABLE IF EXISTS `' . ($tableName ?: 'test_table') . '`');
    }

    /**
     * Create test table
     *
     * @return void
     */
    protected function createTable()
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `test_table` (
            `id` bigint UNSIGNED NOT NULL,
            `text_column` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    /**
     * Insert test row in the test table
     *
     * @return void
     */
    protected function insertRow()
    {
        $sql  = 'INSERT INTO `test_table` (`id`, `text_column`) VALUES (?,?)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([1, 'Test']);
    }
}
