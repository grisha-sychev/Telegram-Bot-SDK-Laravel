<?php

namespace Teg\Types;

class DynamicData
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __call($name, $arguments)
    {
        $property = lcfirst(preg_replace('/^get/', '', $name));
        $property = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property));

        if (array_key_exists($property, $this->data)) {
            $value = $this->data[$property];

            if (is_array($value)) {
                return new self($value);
            }

            return $value;
        }

        return null;
    }

    public function all()
    {
        return $this->data;
    }

    public function __toString()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function objectMaster($array)
    {
        if (is_array($array)) {
            return (object) array_map([$this, 'objectMaster'], $array);
        }
        return $array;
    }
}



