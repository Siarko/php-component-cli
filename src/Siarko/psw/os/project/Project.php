<?php


namespace Siarko\psw\os\project;


use Siarko\cli\util\ArrayHelper;
use Siarko\cli\util\exceptions\FileNotExistsException;
use Siarko\cli\util\os\Path;
use Siarko\psw\os\docker\Container;
use Siarko\psw\os\docker\Docker;
use Siarko\psw\os\project\exceptions\ProjectStructureInvalidException;

class Project
{
    private string $name;
    private string $dirname;
    private Path $path;
    private bool $valid = true;

    private static $UNKNOWN_NAME_PREFIX = 'Unknown';
    private static $UNKNOWN_NAME_COUNTER = 0;
    /**
     * @var Container[]
     */
    private array $containers = [];

    /**
     * Find all projects in given path
     * @return array
     * @throws FileNotExistsException
     */
    public static function find(): array
    {
        $containerData = Docker::getContainers();
        $result = [];
        foreach ($containerData as $containerDatum) {
            $projectName = ArrayHelper::getValue($containerDatum, 'Config/Labels/com.docker.compose.project');
            if(is_null($projectName)){
                continue;
            }
            if (!array_key_exists($projectName, $result)) {
                $result[$projectName] = new Project($projectName);
            }
            $result[$projectName]->addContainer(new Container($containerDatum));
        }

        return $result;
    }

    /**
     * Project constructor.
     * @param string $name
     * @param Container[] $containers
     */
    public function __construct(string $name, array $containers = [])
    {
        $this->setName($name);
        $this->setContainers($containers);
    }

    /**
     * @return Container[]
     */
    public function getContainers(): array
    {
        return $this->containers;
    }

    /**
     * @param Container[] $containers
     */
    public function setContainers(array $containers): void
    {
        $this->containers = $containers;
    }

    /**
     * @param Container $container
     */
    public function addContainer(Container $container)
    {
        array_push($this->containers, $container);
    }


    /**
     * @return string
     */
    public function getDirname(): string
    {
        return $this->dirname;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Path
     */
    public function getFullPath(): Path
    {
        return $this->path;
    }

    /**
     * Is any container running for this project
     * @return bool
     */
    public function isAnyRunning(): bool
    {
        foreach ($this->getContainers() as $container) {
            if($container->isRunning()){
                return true;
            }
        }
        return false;
    }

    /**
     * Are all containers running for this project
     * @return bool
     */
    public function isFullyRunning(): bool
    {
        foreach ($this->getContainers() as $container) {
            if(!$container->isRunning()){
                return false;
            }
        }
        return true;
    }
}