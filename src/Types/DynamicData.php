<?php

namespace Teg\Types;

use Illuminate\Support\Arr;

class DynamicData
{
    private array $data;

    /**
     * DynamicData constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Динамическая обработка методов
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|null
     */
    public function __call(string $name, array $arguments)
    {
        $property = $this->toSnakeCase(lcfirst(preg_replace('/^get/', '', $name)));

        // Если свойство существует как ключ
        if (Arr::exists($this->data, $property)) {
            $value = $this->data[$property];

            if (is_array($value)) {
                return new self($value);
            }

            return $value;
        }

        // Если массив индексированный (только числовые индексы)
        if (is_numeric($property) && isset($this->data[(int) $property])) {
            $value = $this->data[(int) $property];

            if (is_array($value)) {
                return new self($value);
            }

            return $value;
        }

        return null;
    }

    /**
     * Преобразование в строку
     *
     * @return string
     */
    public function __toString(): string
    {
        if (is_array($this->data) && count($this->data) > 0 && is_string(current($this->data))) {
            return implode(", ", $this->data);
        }

        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Преобразование строки в snake_case
     *
     * @param string $string
     * @return string
     */
    private function toSnakeCase(string $string): string
    {
        if ($string === strtolower($string)) {
            return $string;
        }

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}


