<?php
/**
 * Ветка дерева элементов.
 * @author Kurianov S.A. <massivbarn@gmail.com>
 * @package ItemsList
 * @version 1.0.0
 */


class ItemsList
{
    /**
     * Массив вложенных
     * @var array ItemsList
     */
    private $items = [];

    /**
     * @var string
     */
    private $name;

    /**
     * Массив значений
     * @var array Item
     */
    private $options = [];

    /**
     * ItemsList constructor.
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    /**
     * Добавить итем
     * @param Item $item
     */
    public function addOption(Item $item)
    {
        $this->options[] = &$item;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Ищем в массиве $options
     *
     * @param array $arr маршрут
     * @param int $id текущее положение
     * @param string $filter фильтр
     * @return mixed
     */
    public function searchValue(array $arr, int $id, string $filter)
    {
        // если в фильтре возвращаем  null
        if($this->filter($this->getName(), $filter))
            return null;

        $count = count($arr)-1;

        // если последний элемент
        if($count == $id)
        {
            $options = $this->options;
            foreach ($options as $option)
            {
                if($option->getName() == $arr[$id] && !$this->filter($option->getName(), $filter))
                {
                    return $option->getValue();
                }
            }
            return null;
        }

        // Ищем во вложенных
        foreach ($this->items as $item)
        {
            if($item->getName() == $arr[$id])
            {
                return $item->searchValue($arr, $id + 1, $filter);
            }
        }
        return null;
    }

    /**
     * Получить значение
     *
     * @param string $key
     * @param string $filter
     * @param string $delimiter
     * @return mixed
     */
    public function getValue(string $key, string $filter, string $delimiter='_')
    {
        $arr = explode($delimiter, $key);

        return $this->searchValue($arr,0, $filter);
    }

    /**
     * ищем ItemsList с созданием если еще нет
     * @param array $arr
     * @param int $id
     * @return ItemsList
     */
    public function &search(array $arr, int $id)
    {
        $res = null;
        foreach ($this->items as &$item)
        {
            if ($item->getName() == $arr[$id] ) {
                $res =& $item;
            }
        }
        if(!$res)
        {
            $count =count($arr);
            if($count==1)
            {
                $res =&$this;
            }
            else if(isset($arr[$id+1]) && ($id+1)<$count)
            {
                $res = new ItemsList($arr[$id]);
                $this->items[] = &$res;
            }
            else
            {
                $res =&$this;
            }
        }
        if(isset($arr[$id+1]))
        {
            return $res->search($arr,$id+1);
        }
        else
        {
            return $res;
        }
    }

    /**
     * Ищем ItemsList для извлечения параметров
     * @param array $arr
     * @param int $id
     * @param string $filter
     * @return ItemsList
     */
    public function &searchItems(array $arr, int $id, string $filter) : ItemsList
    {
        $res = null;
        foreach ($this->items as &$item)
        {
            if ($item->getName() == $arr[$id] && ($this->filter($item->getName(), $filter)==false))
            {
                $res =&$item;
            }
        }

        if(isset($arr[$id+1]) && isset($res))
        {
            $count = count($arr);
            if($id == $count-2)
                return $res;

            return $res->searchItems($arr, $id+1, $filter);
        }

        if($res)
        {
            return $res;
        }

        $res = new ItemsList("NULL");
        return $res;
    }

    /**
     * Ищем ItemsList
     * @param array $arr
     * @param int $id
     * @param string $filter
     * @return ItemsList
     */
    public function &searchItemsBranch(array $arr, int $id, string $filter) : ItemsList
    {
        $res = null;
        foreach ($this->items as &$item)
        {
            if ($item->getName() == $arr[$id] && ($this->filter($item->getName(), $filter)==false))
            {
                $res =&$item;
            }
        }

        if(isset($arr[$id+1]) && isset($res))
        {
            return $res->searchItemsBranch($arr, $id+1, $filter);
        }

        if($res)
        {
            return $res;
        }

        $res = new ItemsList("NULL");
        return $res;
    }

    /**
     * Ищем параметр в текущем элементе
     *
     * @param string $name
     * @param string $filter
     * @return Item
     */
    public function &searchOption(string $name, string $filter)
    {
        foreach ($this->options as &$option)
        {
            if($option->getName() == $name && ($this->filter($option->getName(), $filter)==false))
                return $option;
        }
        $res = new Item();

        return $res;
    }

    /**
     * Ищем параметр по ключу
     *
     * @param string $key
     * @param string $filter
     * @param string $delimiter
     * @return Item
     */
    public function &getOptionItem(string $key, string $filter, string $delimiter='_')
    {
        $arr = explode($delimiter, $key);

        $items = &$this->searchItems($arr,0, $filter);

        if($items->getName()=='NULL')
        {
            $res = new Item();
            return $res;
        }
        $option = &$items->searchOption($arr[count($arr)-1], $filter);

        return $option;
    }

    /**
     * Назанчит функцию обратоки для Item->value
     *
     * @param string $key
     * @param $func
     * @param string $delimiter
     */
    public function setParseFunction(string $key, $func, string $delimiter='_')
    {
        $option = &$this->getOptionItem($key, '', $delimiter);
        if($option->getId()>0)
        {
            $option->setParseFunction($func);
        }
    }

    /**
     * Назначить тип
     * @param string $key
     * @param string $type
     * @param string $delimiter
     */
    public function setTypeValue(string $key, string $type, string $delimiter='_')
    {
        $option = &$this->getOptionItem($key, '', $delimiter);
        if($option->getId()>0)
        {
            $option->setTypeValue($type);
        }
    }
    /**
     * Получить массив имея: значение текущего элемента
     * @param string $filter
     * @return array
     */
    public function getOptions(string $filter = '') : array
    {
        $result =[];
        $arrFilter = explode(',', $filter);
        foreach ($this->options as $option)
        {
            if(!in_array($option->getName(), $arrFilter, true))
            {
                $result[] = ['name'=>$option->getName(), 'value'=>$option->getValue()];
            }
        }
        return $result;
    }

    /**
     * Ищем ветку
     *
     * @param string $key
     * @param string $filter
     * @param string $delimiter
     * @return ItemsList
     */
    public function &getBranch(string $key, string $filter, string $delimiter='_') : ItemsList
    {
        $arr = explode($delimiter, $key);

        $items = &$this->searchItemsBranch($arr,0, $filter);

        return $items;
    }

    /**
     * Экранируем спец.символы
     * @param string $str
     * @return string
     */
    public function formatStrJSON(string $str) : string
    {
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");

        return  str_replace($escapers, $replacements, $str);
    }

    /**
     * Проверить на присутсвие в фильтре
     *
     * @param string $name
     * @param string $filter
     * @return bool
     */
    public function filter(string $name, string $filter) : bool
    {
        if(strlen($filter)<1)
            return false;
        $arr = explode(',',$filter);
        return in_array($name, $arr, true);
    }

    /**
     * Генерируем json массив
     * @param string $filter
     * @param string $pref
     * @return string
     */
    public function getJSON(string $filter, string $pref='')
    {
        // проверяем есть ли в фильтре
        if($this->filter($this->name, $filter))
            return '';

        $result ='';

        //если первый элемент
        if(strlen($this->name)>0)
        {
            $result .= $pref . "\"" . $this->name . "\":\r\n";
        }
        $result .= $pref."{\r\n";

        // фильтр для параметром, добавляем дубликаты в фильтр чтобы небыло ошибок
        if($filter)
        {
            $filterOptions = $filter.',tabs,person,whatRequired,docs,incases,likbez';
        }
        else
        {
            $filterOptions = 'tabs,person,whatRequired,docs,incases,likbez';
        }
        $options = $this->getOptions($filterOptions);
        $count = count($options);

        // из текущего элемента паарметры
        for($i=0;$i<$count;$i++)
        {
            $value = $this->formatStrJSON($options[$i]['value']);
            //$value = json_encode($value);
            $name = $options[$i]['name'];

            $result .='   '.$pref.'"'.$name.'":"'.$value."\"";
            if($i != ($count-1)) $result .=",\r\n";
            else $result .="";
        }
        $old_count=$count;
        $count = count($this->items);

        // если последний то ненадо запятую
        if($count>0 && $old_count>0) $result .=",\r\n";
        else $result .="\r\n";

        // вложенные элементы
        for($i=0;$i<$count;$i++)
        {
            $res = $this->items[$i]->getJSON($filter, $pref.'   ');
            if(strlen($res)>1)
            {
                if($i>0) $result .=",\r\n";
                else $result .= "\r\n";

                $result .= $this->items[$i]->getJSON($filter, $pref . '   ');
            }
        }
        $result .= "\r\n";

        $result .= $pref."}";

        return $result;
    }
}
