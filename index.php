<?php
include_once 'ItemsListInterface.php';

/**
 * СОздаем и инициализируем базу
 */
$itemsListInterface = new ItemsListInterface();


// Обрататываем POST запрос
if(isset($_POST['act']))
{
    switch ($_POST['act'])
    {
        case 1:
            // setValue
            if(isset($_POST['key']))
            {
                if(strlen($_POST['key'])>3)
                    echo $itemsListInterface->getValue($_POST['key']);
            }
            break;

        case 2:
            // setTypeValue
            if(isset($_POST['key']))
            {
                if(strlen($_POST['key'])>3)
                    echo $itemsListInterface->setTypeValue($_POST['key'], $_POST['val']);
            }
            break;

        case 3:
            // setFilter
            if(isset($_POST['val']))
            {
                if(strlen($_POST['val'])>3)
                    echo $itemsListInterface->setFilter($_POST['val']);
            }
            break;

        case 4:
            //getBranch
            if(isset($_POST['key']))
            {
                if(strlen($_POST['key'])>3)
                    echo $itemsListInterface->getBranchJSON($_POST['key']);
            }
            break;

        case 5:
            // clear
            $itemsListInterface->createFromDB();
            $itemsListInterface->setFilter('');
            break;
    }
    // сохраняем в кэш
    $itemsListInterface->cacheSave();
}
else
{
    // загрузить шаблон
    echo file_get_contents('template.html');
}



//$itemsListInterface->createFromDB();
//$itemsListInterface->setParseFunction('prices_0_prices-title', function ($a){return $a.$a.$a;});
//$itemsListInterface->cacheSave();
//$itemsListInterface->cacheLoad();
//$itemsListInterface->setFilter('advantages');
//echo $itemsListInterface->getValue('mainPage_advantages_1_mainPage_advantages_head');

//echo $itemsListInterface->getValue('mainPage_positions_0_item');

//echo $itemsListInterface->getJSON();
//echo $itemsListInterface->getValue('weWorkWith_enabled');
//echo '<p>';
//$arr = unserialize($itemsListInterface->getValue('promoBox_special-offer'));
//var_dump( $arr);

//echo '1' ? 'true' : 'false';

//echo gettype($itemsListInterface->getValue('promoBox_special-offer'));
//$itemsListInterface->setTypeValue('weWorkWith_enabled', 'boolean');
//$itemsListInterface->setFilter('sectionTwo');
//echo var_dump($itemsListInterface->getBranch('weWorkWith'));

//$itemsListInterface->setTypeValue('promoBox_special-offer', 'array');
//var_dump($itemsListInterface->getValue('promoBox_special-offer'));



//$ili = $itemsListInterface->getBranchInterface('likbez_0');

//echo $ili->getValue('likbez_content_0_likbez_content_title');
//echo $ili->getJSON();