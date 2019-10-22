<?php
include_once 'vendor/autoload.php';
include_once 'ItemsListInterface.php';

use PHPUnit\Framework\TestCase;

class testItemList extends TestCase
{
    private $itemListInterface;

    protected function setUp(): void
    {
        $this->itemListInterface = new ItemsListInterface();
    }
    public function testCreateClass(): void
    {
        $this->assertTrue(!empty($this->itemListInterface));
    }

    /**
     * @param $a
     * @param $b
     * @dataProvider getValueProvider
     */
    public function testGetValue($a, $b): void
    {
        $this->assertSame($a, $this->itemListInterface->getValue($b));
    }
    public function getValueProvider()
    {
        return [
            ['sdgadgdasgad','specialists_people_0_items_0_job'],
            ['100% конфедициальность','consultation_person_0_item'],
            ['Преимущество 1 - Заголовок','mainPage_advantages_0_mainPage_advantages_head'],
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ','promoBox_video'],
            ['1','weWorkWith_enabled'],
        ];
    }

    public function testBoolGetValue(): void
    {
        $this->itemListInterface->setTypeValue('weWorkWith_enabled', 'boolean');
        $this->assertIsBool($this->itemListInterface->getValue('weWorkWith_enabled'));
    }

    public function testArrayGetValue(): void
    {
        $this->itemListInterface->setTypeValue('promoBox_special-offer', 'array');
        $this->assertIsArray($this->itemListInterface->getValue('promoBox_special-offer'));
    }

    public function testGetBranch(): void
    {
        $this->assertIsArray($this->itemListInterface->getBranch('weWorkWith'));
    }

    public function testFilter(): void
    {
        $this->itemListInterface->setFilter('people');
        $this->assertEmpty($this->itemListInterface->getValue('specialists_people_0_items_1_job'));
    }

    public function testFunction(): void
    {
        $this->itemListInterface->setParseFunction('prices_0_prices-title', function ($a){return $a.$a.$a;});
        $this->assertSame('Консультация банковского юристаКонсультация банковского юристаКонсультация банковского юриста', $this->itemListInterface->getValue('prices_0_prices-title'));
    }

    public function testGetBranchInterface(): void
    {
        $ili = $this->itemListInterface->getBranchInterface('likbez_0');
        $this->assertSame('Юридическое сопровождение банковских дел', $ili->getValue('likbez_content_0_likbez_content_title'));
    }
}