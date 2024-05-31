<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WarehouseControllerTest extends WebTestCase
{

    public function testGetWarehouseInventory()
    {
        $client = static::createClient();
        $client->request('GET', '/api/warehouse/1/inventory');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data);

        $this->assertEquals('Товар 1', $data[0]['product_name']);
        $this->assertEquals(50, $data[0]['quantity']);
        $this->assertEquals(0, $data[0]['reserved_quantity']);
    }
}
