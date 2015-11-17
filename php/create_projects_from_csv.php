<?php
require __DIR__.'/vendor/autoload.php';

use Survos\Client\SurvosClient;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\MemberResource;

$reader = new \EasyCSV\Reader('new_projects.csv');

while ($row = $reader->getRow()) {
    print_r($row);
}

$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);
$client = new SurvosClient($config['endpoint']);
if (!$client->authorize($config['username'], $config['password'])) {
    throw new \Exception('Wrong credentials!');
}

// get all projects
/** @type ProjectResource $resource */
$resource = new ProjectResource($client);


$reader = new \EasyCSV\Reader('new_projects.csv', 'r+', true);

while ($row = $reader->getRow()) {
    //code,name,description,timezone_id

    $res = $resource->save(
        [
            'title'       => $row['name'],
            'code'        => $row['code'],
            'timezone_id' => $row['timezone_id'],
            'description' => $row['description'],
        ]
    );

    print_r($res);
    $res = $resource->addModule($row['code'], 'turk');
    print_r($res);
}

// todo: enable Turk
//$resource->addModule('turk', ['is_active' => true]);


