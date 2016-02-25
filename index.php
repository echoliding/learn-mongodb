<?php
require __DIR__ . '/vendor/autoload.php';
/* @var $mongodb mongodb\MongoModel */
$mongodb = mongodb\MongoDb::instance('mongodb://127.0.0.1:27017', ['connect' => true,'db' =>'local']);
$collectionName = 'test';
//查看db列表
//dump($mongodb->listDbs());

//添加数据
/*$result = $mongodb->insert(
    $collectionName,
    [
        "title" => "MongoDB",
        "description" => "database",
        "url" => "http://www.w3cschool.cc/mongodb/",
    ],
    ['w' => true]
);
dump($result);*/

// 删除数据
/*$result = $mongodb->removeOne($collectionName, ['_id' => (new MongoId('56ca88ecf87976ef310041a7'))]);
dump($result);*/

//修改数据
$result = $mongodb->update(
    $collectionName,
    ['_id' =>'56cad6e8f879769c850041a7'],
    ['title'=>'mysql']
);
dump($result);


//查询
/*$data = $mongodb->findAll($collectionName);
foreach($data as $key=>$value){
    $result[] = $value;
}
dump(iterator_to_array($data));*/
