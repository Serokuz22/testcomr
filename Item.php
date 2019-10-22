<?php
/**
 * Один элемент.
 * @author Kurianov S.A. <massivbarn@gmail.com>
 * @package Item
 * @version 1.0.0
 */


class Item
{
    /**
     * Значение
     * @var string
     */
    private $value;

    /**
     * Тип значения
     * @var string
     */
    private $typeValue;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Назначаемая функция для обработки $value
     * @var function
     */
    private $parseFunction;

    /**
     * Item constructor.
     * @param int $id
     * @param string $name
     * @param string $val
     */
    public function __construct(int $id = 0, string $name = '', string $val = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $val;
        $this->typeValue = gettype($val);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('value','name','id','typeValue');
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Возвращаем не обработаттые данные
     * @return string
     */
    public function getValueRaw() : string
    {
        return $this->value;
    }

    /**
     * Обрабатываем $value
     * @return mixed
     */
    public function getValue()
    {
        if($this->parseFunction)
        {
            $func = $this->parseFunction;
            return $func($this->value);
        }
        switch ($this->typeValue)
        {
            case 'array':
                return unserialize($this->getValueRaw());
            case 'boolean':
                return boolval(($this->getValueRaw() ? true : false));
            case 'integer':
                return intval($this->getValueRaw());
            case 'double':
                return floatval($this->getValueRaw());
        }

        return $this->getValueRaw();
    }

    /**
     * Назначить тип
     * @param string $type
     */
    public function setTypeValue(string $type)
    {
        $this->typeValue = $type;
    }

    /**
     * Назначаем функцию обратотки
     * @param $func
     */
    public function setParseFunction($func)
    {
            $this->parseFunction = $func;
    }

    /**
     * Возвращаем имя
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
