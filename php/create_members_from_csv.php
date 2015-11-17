<?php
require __DIR__.'/vendor/autoload.php';

use Survos\Client\SurvosClient;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\UserResource;


$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);
$client = new SurvosClient($config['endpoint']);
if (!$client->authorize($config['username'], $config['password'])) {
    throw new \Exception('Wrong credentials!');
}

$projectResource = new ProjectResource($client);
$userResource = new UserResource($client);
$memberResource = new MemberResource($client);

$reader = new \EasyCSV\Reader('new_members.csv');

while ($row = $reader->getRow()) {
    $project = $projectResource->getByCode($row['project_code']);
    $user = $userResource->getOneBy('username', $row['code']);
    $res = $memberResource->save(
        [
            'code'                 => $row['code'],
            'project_id'           => $project['id'],
            'permission_type_code' => $row['permission_type_code'],
        ]
    );

}

// todo: enable Turk
//$resource->addModule('turk', ['is_active' => true]);


