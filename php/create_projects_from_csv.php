<?php
require __DIR__.'/vendor/autoload.php';

use Survos\Client\SurvosClient;
use Survos\Client\Resource\ProjectResource;

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
$resource = new ProjectResource($client);
$resource->save(
    [
        'title'       => "new poject",
        'code'        => "new_project_code",
        'timezone_id' => 1,
    ]
);

// todo: enable Turk
$resource->addModule('turk', ['is_active' => true]);

$reader = new \EasyCSV\Reader('new_members.csv');

while ($row = $reader->getRow()) {
    print_r($row);
}

