<?php


namespace Siarko\psw\os\docker;


use Siarko\cli\util\ArrayHelper;
use Siarko\cli\util\Cacheable;

class Container
{
    const PATH_PROJECT_NAME = 'Config/Labels/com.docker.compose.project';
    const PATH_IS_RUNNING = 'State/Running';

    use Cacheable;

    private array $data;

    public function __construct(array $containerData)
    {
        $this->data = $containerData;
    }

    public function getName()
    {
        if ($this->cacheExists('_shortName')) {
            return $this->getCache('_shortName');
        }
        $fullName = $this->getFullName();
        $pos = strpos($fullName, $this->getProjectName());
        $len = strlen($this->getProjectName());
        $name = substr($fullName, $pos+$len+1);
        if(strlen($name) == 0){
            $name = $fullName;
        }
        return $this->setCache('_shortName', $name);
    }

    /**
     * @return mixed|null
     */
    public function getFullName()
    {
        return $this->getCache('Name', $this->data['Name'], true);
    }

    /**
     * Return project name that this container belongs to
     * @return mixed|null
     */
    public function getProjectName(){
        return $this->getCache(
            self::PATH_PROJECT_NAME,
            ArrayHelper::getValue($this->data, self::PATH_PROJECT_NAME),
            true
        );
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return ArrayHelper::getValue($this->data, self::PATH_IS_RUNNING);
    }


}