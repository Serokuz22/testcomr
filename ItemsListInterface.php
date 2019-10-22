<?php
/**
 * Основной интерфес ветки.
 * @author Kurianov S.A. <massivbarn@gmail.com>
 * @package ItemsListInterface
 * @version 1.0.0
 *
 * Предполгается что пользователь получает либо значение 1го элемента
 * Либо массив со значениями ввиде дерева
 *
 * При создании класса проверяется есть ли файл itemslist.cache если есть то он загружается
 * если нет данные загружаются из БД
 *
 * Методы:
 *
 * setFilter() - назначить фильтр
 *
 * createFromDB() - создать дерево из базы данных
 *
 * cacheSave() - сохранить в файл itemslist.cache
 *
 * cacheLoad() - загрузить из файла itemslist.cache
 *
 * getValue($key, string='_') - получить значение из Item $key - путь
 *
 * setParseFunction($key, $func, $delimiter='_') - назначить функцию для обработки выдачи результатов getValue
 *
 * setTypeValue($key, $type, $delimiter='_') - назначить типа integer, boolean, double, array по умолчаю string
 *
 * getBranchInterface($key, $delimiter='_') - получить ветку как отдельный класс ItemsListInterface
 *
 * getBranchJSON($key, $delimiter='_') - получить ветку в json
 *
 * getBranch($key, $delimiter='_') - получить ветку в массиве
 *
 * getJSON() - получить все без фильтрации в формате json
 */

include_once 'Item.php';
include_once 'ItemsList.php';

class ItemsListInterface
{
    /**
     * @var ItemsList
     */
    private $itemsList;

    /**
     * @var string
     */
    private $filter;

    /**
     * ItemsListInterface constructor.
     */
    public function __construct(ItemsList $itemsList = null)
    {
        if(isset($itemsList))
        {
            $this->filter = '';
            $this->itemsList = &$itemsList;
        }
        else
        {
            $this->filter = '';
            $this->init();
        }
    }

    /**
     * @param string $filter
     */
    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Создаем подключение в бд
     * @return mysqli
     */
    private function &createConnectDB()
    {
        // Параметры для подключения
        $cfg = [];
        $cfg['dbserver'] = 'localhost';
        $cfg['dbuser'] = 'root';
        $cfg['dbpass'] = '';
        $cfg['dbname'] = 'test1';

        // Подключаемся
        $db = new mysqli($cfg['dbserver'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname']);
        if ($db->connect_errno)
        {
            echo "Номер ошибки: " . $db->connect_errno . "\n";
            echo "Ошибка: " . $db->connect_error . "\n";
            exit;
        }

        // Прописываем нашу таблицу символов
        if (!$db->set_charset("utf8mb4"))
        {
            echo "Ошибка при загрузке набора символов:". $db->error . "\n";
            exit;
        }
        return $db;
    }

    /**
     * Загружаем из БД
     * @return bool
     */
    public function createFromDB() : bool
    {
        // запрос
        $sql = 'SELECT * FROM object_meta_entries';
        // подключаемся
        $db = $this->createConnectDB();
        if (!$result = $db->query($sql))
        {
            echo "Номер ошибки: " . $db->errno . "\n";
            echo "Ошибка: " . $db->error . "\n";
            exit;
        }

        //инициализируем ItemsList
        $this->itemsList = new ItemsList();

        // разбираем записи таблицы
        while ($rect = $result->fetch_assoc())
        {
            $r = explode('_',$rect['meta_key']);
            $rrr = array_key_last ($r);

            $tt = &$this->itemsList->search($r,0);

            if($tt)
                $tt->addOption(new Item(
                        intval($rect['object_id']),
                        $r[$rrr],
                        $rect['meta_value']
                    )
                );
        }
        $db->close();

        $this->filter='';

        return true;
    }

    /**
     * сохраняем сериализованные данные
     * @return bool
     */
    public function cacheSave() : bool
    {
        file_put_contents('itemslistinterface.cache', $this->filter);
        $ser = serialize($this->itemsList);
        if(file_put_contents('itemslist.cache', $ser))
            return true;
        return false;
    }

    /**
     * загружаем сериализованные данные
     * @return bool
     */
    public function cacheLoad() : bool
    {
        if(is_readable('itemslistinterface.cache'))
        {
            $f = file_get_contents('itemslistinterface.cache');
            $this->setFilter($f);
        }
        if(is_readable('itemslist.cache'))
        {
            $ser = file_get_contents('itemslist.cache');
            if($ser)
            {
                $this->itemsList =unserialize($ser);
                if($this->itemsList )
                    return true;
            }
        }
        return false;
    }

    /**
     * первая инициализация класса
     * @return void
     */
    public function init()
    {
        if(!$this->cacheLoad())
        {
            $this->createFromDB();
        }
        $this->cacheSave();
    }

    /**
     * Получить значение по ключу
     * @param string $key
     * @param string $delimiter
     * @return mixed
     */
    public function getValue(string $key, string $delimiter='_')
    {
        return $this->itemsList->getValue($key, $this->filter, $delimiter);
    }

    /**
     * Назначить функцию элементу для обоработки значения
     * @param string $key
     * @param $func
     * @param string $delimiter
     */
    public function setParseFunction(string $key, $func, string $delimiter='_')
    {
        return $this->itemsList->setParseFunction($key, $func, $delimiter);
    }

    /**
     * Назначить тип
     * @param string $key
     * @param string $type
     * @param string $delimiter
     */
    public function setTypeValue(string $key, string $type, string $delimiter='_')
    {
        return $this->itemsList->setTypeValue($key, $type, $delimiter);
    }

    /**
     * Получить ветку
     * @param string $key
     * @param string $delimiter
     * @return array
     */
    public function getBranch(string $key, string $delimiter='_') : array
    {
        return json_decode($this->getBranchJSON($key, $delimiter), true);
    }

    /**
     * Получить ветку json
     * @param string $key
     * @param string $delimiter
     * @return string
     */
    public function getBranchJSON(string $key, string $delimiter='_') : string
    {
        $itemList = &$this->itemsList->getBranch($key, $this->filter, $delimiter);
        if($itemList->getName() == 'NULL')
        {
            return array();
        }

        $json = $itemList->getJSON($this->filter);

        if($itemList->getName() != '')
        {
            $json ='{'.$json.'}';
        }
        return $json;
    }

    /**
     * Получить ветку как отдельный класс
     * @param string $key
     * @param string $delimiter
     * @return string
     */
    public function getBranchInterface(string $key, string $delimiter='_') : ItemsListInterface
    {
        $itemList = &$this->itemsList->getBranch($key, $this->filter, $delimiter);
        if($itemList->getName() == 'NULL')
        {
            return null;
        }

        $result = new ItemsListInterface($itemList);

        return $result;
    }

    /**
     * Получить все в json
     * @return string
     */
    public function getJSON()
    {
        return $this->itemsList->getJSON('');
    }
}