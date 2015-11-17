<?php
require __DIR__.'/vendor/autoload.php';

use Survos\Client\SurvosClient;
use Survos\Client\Resource\ProjectResource;
use Survos\Client\Resource\MemberResource;
use Survos\Client\Resource\UserResource;

$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);
$client = new SurvosClient($config['endpoint']);
if (!$client->authorize($config['username'], $config['password'])) {
    print_r($config);
    throw new \Exception('Wrong credentials!');
}

$userResource = new UserResource($client);
$memberResource = new MemberResource($client);
$projectResource = new ProjectResource($client);


// get all projects
/** @type ProjectResource $resource */
$resource = new ProjectResource($client);

$reader = new \EasyCSV\Reader('psy_class.csv', 'r+', true);

while ($row = $reader->getRow()) {
    //code,name,description,timezone_id

    $code = $row['code'];
    $params = [];
    if ($project = $resource->getOneBy('code', $code))
    {
        $params['id'] = $project['id'];
    }
    try {
        $res = $resource->save(
            array_merge($params, [
                'title'       => $name = $row['name'],
                'code'        => $code,
                'timezone_id' => 1, // $row['timezone_id'],
                'description' => $name . " Project",
                'background_server_code' => 'psymeasurement',
            ])
        );
    } catch (\Exception $e)
    {
        printf("Error saving project: %s\n", $e->getMessage());
        printf("Project $code already exists\n");
    }

    $project = $projectResource->getByCode($code);

    $res = $resource->addModule($code, 'turk');

    try {
        foreach ([$code,'tac','ho449'] as $idx=>$username)
        {
            $user = $userResource->getOneBy('username', $username);
            if (!$user){
                print "user '$username' not found\n";
                continue;
            }
            $params = [
                'code'                 => $username,
                'project_id'           => $project['id'],
                'user_id'              => $user['id'],
                'permission_type_code' => 'owner',
            ];
            $response = $memberResource->getList(1, 1, ['code' => $username, 'project_id' => $project['id']]);
            $members = $response['items'];
            if ($members) {
                print "Member '$username' already exists for project " . $project['id'] . "\n";
                continue;
//                $params['id'] = $members[0]['id'];
            }
            print "Saving member '$username' for project " . $project['id'] . "\n";
            $res = $memberResource->save($params);
        }
    } catch(Exception $e) {
        var_dump($params);
        print "Error importing member: " .$e->getMessage()."\n";
    }


}



// todo: enable Turk
//$resource->addModule('turk', ['is_active' => true]);


