<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WarehouseControllerTest extends WebTestCase
{

    public function testGetWarehouseInventoryNotFound()
    {
        $client = static::createClient();
        $client->request('GET', '/api/warehouse/999/inventory');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testGetWarehouseInventory()
    {
        $client = static::createClient();
        $client->request('GET', '/api/warehouse/1/inventory');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertCount(2, $responseContent); // 2 products linked to warehouse 1
    }

    public function testReserveProductsNotFound()
    {
        $client = static::createClient();
        $client->request('POST', '/api/warehouse/999/reserve', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'products' => ['T001']
        ]));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testReserveProductsNotEnoughQuantity()
    {
        $client = static::createClient();
        $client->request('POST', '/api/warehouse/1/reserve', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'products' => ['T003']
        ]));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testReserveProductsSuccessfully()
    {
        $client = static::createClient();
        $client->request('POST', '/api/warehouse/1/reserve', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'products' => ['T001']
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testUnreserveProductsNotFound()
    {
        $client = static::createClient();
        $client->request('POST', '/api/warehouse/999/unreserve', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'products' => ['T001']
        ]));

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testUnreserveProductsSuccessfully()
    {
        $client = static::createClient();
        $client->request('POST', '/api/warehouse/1/unreserve', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'products' => ['T001']
        ]));

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
