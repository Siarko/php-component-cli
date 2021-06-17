<?php


namespace Siarko\psw\os\project;


use Siarko\cli\util\os\Path;
use Siarko\psw\os\docker\Docker;
use Siarko\psw\os\project\exceptions\ProjectStructureInvalidException;

class Project
{
    private const PROJECT_REQUIRED_DIRS = ['magento', 'docker'];
    private string $name;
    private string $dirname;
    private Path $path;
    private bool $valid = true;

    public function __construct(Path $path)
    {
        $this->dirname = basename($path->getPath());
        $this->path = $path;
        $this->validate();
    }

    private function validate()
    {
        foreach (self::PROJECT_REQUIRED_DIRS as $PROJECT_REQUIRED_DIR) {
            if (!(new Path($this->path . "/" . $PROJECT_REQUIRED_DIR))->exists()) {
                $this->valid = false;
            }
        }
    }

    /**
     * Find all projects in given path
     * @param Path $root
     * @return array
     * @throws \Siarko\cli\util\exceptions\FileNotExistsException
     */
    public static function find(Path $root): array
    {
        $containerData = Docker::getContainers();
        $list = preg_filter('/^local\..*\\.com$/', '$0', $root->list());
        $result = [];
        foreach ($list as $name) {
            $result[] = new Project(new Path($root . '/' . $name));
        }
        return $result;
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
}