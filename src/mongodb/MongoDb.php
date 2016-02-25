<?php
namespace mongodb;

class MongoDb
{
    private static $instance;
    private $mongoClient;
    public $errors;
    public $dbName;
    public $collectionName;
    protected $db;

    protected function __construct($server = 'mongodb://localhost:27017', array $options = ['connect' => true,])
    {
        $this->mongoClient = new \MongoClient($server, $options);
        if (isset($options['db'])) {
            $this->selectDb($options['db']);
        }

    }

    public static function instance($server = 'mongodb://localhost:27017', array $options = ['connect' => true,])
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($server, $options);
        }
        return static::$instance;
    }

    public function __clone()
    {

    }

    public function __destruct()
    {
        $this->mongoClient->close();
    }


    // **************** 实例化后的方法 ****************


    /**
     * 连接到数据库服务器
     * @return bool
     */
    public function connect()
    {
        try {
            return $this->mongoClient->connect();
        } catch (\MongoConnectionException $e) {
            $this->errors = $e->getMessage();
        }
    }


    /**
     * 列出所有有效数据库
     * @return array 返回的关联数组包括了三个字段。
     * 第一个字段是 databases，里面包含了一个数组。每个元素对应一个数据库，给出数据库名称、尺寸以及是否为空。
     * 另外两个字段是 totalSize（单位为字节 bytes）和 ok，如果方法成功运行，它会是 1。
     */
    public function listDbs()
    {
        return $this->mongoClient->listDBs();
    }

    /**
     * 获取一个数据库
     * @param $dbName
     * @return \MongoDB
     */
    public function selectDb($dbName)
    {
        try {
            $this->dbName = $dbName;
            return $this->db = $this->mongoClient->selectDB($dbName);
        } catch (\Exception $e) {
            $this->errors = $e->getMessage();
        }

    }

    /**
     * 获取数据库的文档集
     * @param $dbName
     * @param $collectName
     * @return \MongoCollection 返回一个新的文档集对象
     */
    public function selectCollection($dbName, $collectName)
    {
        try {
            $this->dbName = $dbName;
            $this->collectionName = $collectName;
            return $this->selectDb($dbName)->selectCollection($collectName);
        } catch (\Exception $e) {
            $this->errors = $e->getMessage();
        }
    }


    /**
     * 删除该集合，以及它的索引
     * @param $collectionName
     * @return array
     */
    public function dropCollection($collectionName)
    {
        return $this->db->$collectionName->drop();
    }


    /**
     * 创建一个索引
     * @param  string $collectionName 集合名
     * @param array $keys 字段名
     * @param array $options unique=true时创建唯一索引
     * @return array
     */
    public function createIndex($collectionName, array $keys, array $options = ['unique' => false])
    {
        try {
            return $this->db->$collectionName->createIndex($keys, $options);
        } catch (\MongoException $me) {
            $this->errors = $me->getMessage();
        }
    }


    /**
     * 删除集合索引
     * @param string $collectionName 集合名
     * @param $keys
     * @return array
     */
    public function deleteIndex($collectionName, $keys)
    {
        return $this->db->$collectionName->deleteIndex($keys);
    }


    /**
     *  删除集合的所有索引
     * @param string $collectionName 集合名
     * @return array
     */
    public function deleteAllIndex($collectionName)
    {
        return $this->db->$collectionName->deleteIndexes();
    }


    /**
     * 插入文档到集合中
     * @param string $collectionName 集合名
     * @param array|object $data 要保存的 Array 或 Object。 如果用的是 Object，它不能有 protected 或 private 的属性
     * @param array $options
     * @return array|bool
     */
    public function insert($collectionName, $data, $options = [])
    {
        try {
            return $this->db->$collectionName->insert($data, $options);
        } catch (\MongoCursorTimeoutException $mcte) {
            $this->errors = $mcte->getMessage();
        } catch (\MongoCursorException $mce) {
            $this->errors = $mce->getMessage();
        } catch (\MongoException $me) {
            $this->errors = $me->getMessage();
        }
    }


    /**
     * 保存一个文档到集合 (如果存在此对象则更新，否则插入）
     * @param string $collectionName 集合名
     * @param array|object $data 要保存的 Array 或 Object。 如果用的是 Object，它不能有 protected 或 private 的属性
     * @param array $options
     * @return array|bool
     */
    public function save($collectionName, $data, array $options)
    {
        try {
            return $this->db->$collectionName->save($data, $options);
        } catch (\MongoCursorTimeoutException $mcte) {
            $this->errors = $mcte->getMessage();
        } catch (\MongoCursorException $mce) {
            $this->errors = $mce->getMessage();
        } catch (\MongoException $me) {
            $this->errors = $me->getMessage();
        }
    }


    /**
     * 更新一个文档
     * @param string $collectionName 集合名
     * @param array $condition 条件
     * @param array $newObj 要更新的内容
     * @param array $options 选项: safe 是否返回操作结果; fsync 是否是直接影响到物理硬盘; upsert 是否没有匹配数据就添加一条新的; multiple 是否影响所有符合条件的记录，默认只影响一条
     * @return bool
     */
    public function update($collectionName, array $condition, array $newObj, array $options = ['fsync' => 0, 'upsert' => 0, 'multiple' => 0])
    {
        try {
            if (isset($condition['_id']) && is_string($condition['_id'])) {
                $condition['_id'] = (new \MongoId($condition['_id']));
            }
            return $this->db->$collectionName->update($condition, $newObj, $options);
        } catch (\MongoCursorTimeoutException $mctx) {
            $this->errors = $mctx->getMessage();
        } catch (\MongoCursorException $mce) {
            $this->errors = $mce->getMessage();
        }
    }

    /**
     * 从集合中删除记录
     * @param string $collectionName 集合名
     * @param array $condition
     * @param array $options
     * @return array|bool
     */
    public function remove($collectionName, array $condition, array $options)
    {
        try {
            return $this->db->$collectionName->remove($condition, $options);
        } catch (\MongoCursorTimeoutException $mctx) {
            $this->errors = $mctx->getMessage();
        } catch (\MongoCursorException $mce) {
            $this->errors = $mce->getMessage();
        }
    }


    /**
     * 从集合中删除一条记录
     * @param string $collectionName 集合名
     * @param array $condition
     * @return array|bool
     */
    public function removeOne($collectionName, array $condition)
    {
        return $this->remove($collectionName, $condition, ['justOne' => true]);
    }


    /**
     * 查询一条记录
     * @param $collectionName
     * @param array $condition
     * @param array $fields
     * @param array $options maxTimeMS
     * @return array|null
     */
    public function findOne($collectionName, array $condition, array $fields = [], array $options = [])
    {
        try {
            if (isset($condition['_id']) && is_string($condition['_id'])) {
                $condition['_id'] = (new \MongoId($condition['_id']));
            }
            return $this->db->$collectionName->findOne($condition, $fields, $options);
        } catch (\MongoConnectionException $mce) {
            $this->errors = $mce->getMessage();
        } catch (\MongoExecutionTimeoutException $mete) {
            $this->errors = $mete->getMessage();
        }
    }


    /**
     * 根据ID查询一条记录
     * @param string $collectionName 集合名
     * @param string $_id MongoId
     * @param array $fields 指定返回的列，默认返回所有列
     * @param array $options
     * @return array|null
     */
    public function findByObjectId($collectionName, $_id, array $fields = [], array $options = [])
    {
        return $this->findOne($collectionName, ['_id' => (new \MongoId($_id))], $fields, $options);
    }


    /**
     * 返回集合中的文档数量
     * @param string $collectionName 集合名
     * @param array $condition
     * @param int $limit 指定返回数量的上限。
     * @param int $skip 指定在开始统计前，需要跳过的结果数目。
     * @return int
     */
    public function count($collectionName, array $condition, $limit = 0, $skip = 0)
    {
        return $this->db->$collectionName->count($condition, $limit, $skip);
    }

    public function findAll($collectionName, array $condition = [], array $filter = [], array $fields = [])
    {
        $cursor = $this->db->$collectionName->find();
        return $cursor;
    }


    public function createCollection($name)
    {
        $this->db->createCollection($name);
    }


}