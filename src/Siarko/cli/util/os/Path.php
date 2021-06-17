<?php


namespace Siarko\cli\util\os;


use Siarko\cli\util\exceptions\FileNotExistsException;

class Path
{
    private string $path;

    public function __construct(string $path = null)
    {
        $this->setPath($path);
    }

    public function exists(){
        return file_exists($this->getPath());
    }

    public function isDir(){
        if(!$this->exists()){
            throw new FileNotExistsException("File or directory does not exist! ".$this->getPath());
        }
        return is_dir($this->getPath());
    }

    public function isFile(){
        if(!$this->exists()){
            throw new FileNotExistsException("File or directory does not exist! ".$this->getPath());
        }
        return is_file($this->getPath());

    }

    public function createDir(bool $recursive = false){
        if(!$this->exists()){
            return mkdir($this->getPath(), 0755, $recursive);
        }
        return false;
    }

    public function createFile(bool $recursive = false){
        if(!$this->exists()){
            /*
             * TODO implement/check recursive creation
             * */
            return file_put_contents($this->getPath(), '');
        }
        return false;
    }

    /**
     * @return array|false|null
     * @throws FileNotExistsException
     */
    public function list(){
        if(!$this->isDir()){
            return null;
        }
        $list = scandir($this->getPath());
        return array_diff($list, array('..', '.'));

    }

    public function __toString()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

}