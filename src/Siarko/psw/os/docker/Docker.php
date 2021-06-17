<?php


namespace Siarko\psw\os\docker;


use Siarko\psw\os\project\Project;

class Docker
{

    public static function getContainers(){
        $ids = "";
        exec('docker container ls --all -q', $ids);
        $containers = "";
        exec('docker container inspect '.implode(' ', $ids), $containers);
        return json_decode(implode('', $containers), true);
    }

    public static function ssh(Project $project){
        echo "=== PSW still running! Exit shell to return to it! ===\n";
        passthru("cd ".$project->getFullPath()->getPath()."/docker; docker-compose exec php bash");
        echo "=== Subshell exit ===\n";
    }

}