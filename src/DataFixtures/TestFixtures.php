<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Warehouse;
use App\Entity\WarehouseProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Создание тестового склада (существующий склад)
        $warehouse1 = new Warehouse();
        $warehouse1->setName('Test Warehouse 1');
        $warehouse1->setIsAvailable(true);
        $manager->persist($warehouse1);

        // Создание тестового склада, который будет не найден
        $warehouse2 = new Warehouse();
        $warehouse2->setName('Test Warehouse 2');
        $warehouse2->setIsAvailable(false);
        $manager->persist($warehouse2);

        // Создание тестового товара (существующий товар)
        $product1 = new Product();
        $product1->setName('Test Product 1');
        $product1->setSize('Small');
        $product1->setUniqueCode('T001');
        $manager->persist($product1);

        // Создание тестового товара, который будет не найден
        $product2 = new Product();
        $product2->setName('Test Product 2');
        $product2->setSize('Medium');
        $product2->setUniqueCode('T002');
        $manager->persist($product2);

        // Создание тестового товара с недостаточным количеством для резервирования
        $product3 = new Product();
        $product3->setName('Test Product 3');
        $product3->setSize('Large');
        $product3->setUniqueCode('T003');
        $manager->persist($product3);

        // Связь существующего склада и существующего товара
        $warehouseProduct1 = new WarehouseProduct();
        $warehouseProduct1->setWarehouse($warehouse1);
        $warehouseProduct1->setProduct($product1);
        $warehouseProduct1->setQuantity(50);
        $warehouseProduct1->setReservedQuantity(10);
        $manager->persist($warehouseProduct1);

        // Связь существующего склада и тестового товара с недостаточным количеством
        $warehouseProduct2 = new WarehouseProduct();
        $warehouseProduct2->setWarehouse($warehouse1);
        $warehouseProduct2->setProduct($product3);
        $warehouseProduct2->setQuantity(5);
        $warehouseProduct2->setReservedQuantity(5); // Весь товар уже зарезервирован
        $manager->persist($warehouseProduct2);

        // Сохранение всех данных в базу
        $manager->flush();
    }
}
