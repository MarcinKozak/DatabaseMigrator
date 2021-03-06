<?php

namespace MarcinKozak\DatabaseMigrator\Mappers;

use Closure;

class Column {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $newName;

    /**
     * @var Closure|null
     */
    protected $valueMapper;

    /**
     * Column constructor.
     * @param string $name
     * @param string|null $newName
     */
    public function __construct(string $name, string $newName = null) {
        $this->name     = $name;
        $this->newName  = $newName ?: $name;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNewName() : string {
        return $this->newName;
    }

    /**
     * @param Closure $closure
     */
    public function map(Closure $closure) : void {
        $this->valueMapper = $closure;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function getValue($value) {
        if($this->valueMapper instanceof Closure) {
            return call_user_func($this->valueMapper, $value);
        }

        return $value;
    }

}
