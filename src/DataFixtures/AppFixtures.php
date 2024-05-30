<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Warehouse;
use App\Entity\WarehouseProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Создание складов
        $warehouse1 = new Warehouse();
        $warehouse1->setName('Склад 1');
        $warehouse1->setIsAvailable(true);
        $manager->persist($warehouse1);

        $warehouse2 = new Warehouse();
        $warehouse2->setName('Склад 2');
        $warehouse2->setIsAvailable(false);
        $manager->persist($warehouse2);

        // Создание товаров
        $product1 = new Product();
        $product1->setName('Товар 1');
        $product1->setSize('Маленький');
        $product1->setUniqueCode('P001');
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setName('Товар 2');
        $product2->setSize('Средний');
        $product2->setUniqueCode('P002');
        $manager->persist($product2);

        // Связь складов и товаров
        $warehouseProduct1 = new WarehouseProduct();
        $warehouseProduct1->setWarehouse($warehouse1);
        $warehouseProduct1->setProduct($product1);
        $warehouseProduct1->setQuantity(50);
        $warehouseProduct1->setReservedQuantity(0);
        $manager->persist($warehouseProduct1);

        $warehouseProduct2 = new WarehouseProduct();
        $warehouseProduct2->setWarehouse($warehouse2);
        $warehouseProduct2->setProduct($product2);
        $warehouseProduct2->setQuantity(150);
        $warehouseProduct2->setReservedQuantity(0);
        $manager->persist($warehouseProduct2);

        // Сохранение всех данных в базу
        $manager->flush();
    }
}
