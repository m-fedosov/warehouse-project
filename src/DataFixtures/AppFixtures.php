<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Warehouse;
use App\Entity\WarehouseProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // Создание складов
        $warehouses = [];
        for ($i = 0; $i < 10; $i++) {
            $warehouse = new Warehouse();
            $warehouse->setName('Склад ' . ($i + 1));
            $warehouse->setIsAvailable($faker->boolean);
            $manager->persist($warehouse);
            $warehouses[] = $warehouse;
        }

        // Создание товаров
        $products = [];
        for ($i = 0; $i < 100; $i++) {
            $product = new Product();
            $product->setName('Товар ' . ($i + 1));
            $product->setSize($faker->randomElement(['Маленький', 'Средний', 'Большой']));
            $product->setUniqueCode('P' . str_pad($i + 1, 3, '0', STR_PAD_LEFT));
            $manager->persist($product);
            $products[] = $product;
        }

        // Связь складов и товаров
        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                if ($faker->boolean(50)) {
                    $warehouseProduct = new WarehouseProduct();
                    $warehouseProduct->setWarehouse($warehouse);
                    $warehouseProduct->setProduct($product);
                    $warehouseProduct->setQuantity($faker->numberBetween(1, 100));
                    $warehouseProduct->setReservedQuantity($faker->numberBetween(0, $warehouseProduct->getQuantity()));
                    $manager->persist($warehouseProduct);
                }
            }
        }

        // Сохранение всех данных в базу
        $manager->flush();
    }
}
