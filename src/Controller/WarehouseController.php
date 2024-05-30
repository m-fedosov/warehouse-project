<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Warehouse;
use App\Entity\WarehouseProduct;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class WarehouseController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/warehouse/{id}/inventory', name: "warehouse_inventory", methods: ['GET'])]
    #[OA\Get(
        path: '/api/warehouse/{id}/inventory',
        summary: 'Get warehouse inventory'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Warehouse ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 404,
        description: 'Warehouse not found.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the inventory of the specified warehouse.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: "product_id",
                        type: "integer",
                    ),
                    new OA\Property(
                        property: "product_name",
                        type: "string",
                    ),
                    new OA\Property(
                        property: "quantity",
                        type: "integer",
                    ),
                    new OA\Property(
                        property: "reserved_quantity",
                        type: "integer",
                    )
                ]
            )
        )
    )]
    public function getWarehouseInventory($id): JsonResponse
    {
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->find($id);

        if (!$warehouse) {
            return new JsonResponse(['error' => 'Warehouse not found'], 404);
        }

        $inventory = $this->entityManager->getRepository(WarehouseProduct::class)->findBy(['warehouse' => $id]);

        $response = [];
        foreach ($inventory as $item) {
            $response[] = [
                'product_id' => $item->getProduct()->getId(),
                'product_name' => $item->getProduct()->getName(),
                'quantity' => $item->getQuantity(),
                'reserved_quantity' => $item->getReservedQuantity(),
            ];
        }

        return new JsonResponse($response);
    }

    #[Route('/api/warehouse/{id}/reserve', name: "warehouse_reserve", methods: ['POST'])]
    #[OA\Post(
        path: '/api/warehouse/{id}/reserve',
        summary: 'Reserve products in the warehouse'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Warehouse ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [new OA\Property(
                property: 'products',
                type: 'array',
                items: new OA\Items(
                    type: 'string',
                    example: 'uniqueCode'
                )
            )]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Warehouse or Product not found',
    )]
    #[OA\Response(
        response: 400,
        description: 'Not enough quantity of product',
    )]
    public function reserveProducts($id, Request $request): JsonResponse
    {
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->find($id);

        if (!$warehouse) {
            return new JsonResponse(['error' => 'Warehouse not found'], 404);
        }

        if (!$warehouse->getIsAvailable()) {
            return new JsonResponse(['error' => "Warehouse {$warehouse->getName()} not available"], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['products']) || !is_array($data['products'])) {
            return new JsonResponse(['error' => 'Invalid input'], 400);
        }

        $products = $data['products'];
        $repository = $this->entityManager->getRepository(WarehouseProduct::class);

        foreach ($products as $uniqueCode) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['uniqueCode' => $uniqueCode]);
            if (!$product) {
                return new JsonResponse(['error' => "Product with code $uniqueCode not found"], 404);
            }

            $warehouseProduct = $repository->findOneBy(['warehouse' => $id, 'product' => $product->getId()]);
            if (!$warehouseProduct) {
                return new JsonResponse(['error' => "Product with code $uniqueCode not found in warehouse"], 404);
            }

            if ($warehouseProduct->getQuantity() - $warehouseProduct->getReservedQuantity() <= 0) {
                return new JsonResponse(['error' => "Not enough quantity of product with code $uniqueCode"], 400);
            }

            $warehouseProduct->setReservedQuantity($warehouseProduct->getReservedQuantity() + 1);
            $this->entityManager->persist($warehouseProduct);
        }

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Products reserved successfully']);
    }

    #[Route('/api/warehouse/{id}/unreserve', name: "warehouse_unreserve", methods: ['POST'])]
    #[OA\Post(
        path: '/api/warehouse/{id}/unreserve',
        summary: 'Unreserve products in the warehouse'
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Warehouse ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [new OA\Property(
                property: 'products',
                type: 'array',
                items: new OA\Items(
                    type: 'string',
                    example: 'uniqueCode'
                )
            )]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Warehouse or Product not found',
    )]
    #[OA\Response(
        response: 400,
        description: 'Can\'t unreserved product',
    )]
    public function unreserveProducts($id, Request $request): JsonResponse
    {
        $warehouse = $this->entityManager->getRepository(Warehouse::class)->find($id);

        if (!$warehouse) {
            return new JsonResponse(['error' => 'Warehouse not found'], 404);
        }

        if (!$warehouse->getIsAvailable()) {
            return new JsonResponse(['error' => "Warehouse {$warehouse->getName()} not available"], 400);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['products']) || !is_array($data['products'])) {
            return new JsonResponse(['error' => 'Invalid input'], 400);
        }

        $products = $data['products'];
        $repository = $this->entityManager->getRepository(WarehouseProduct::class);

        foreach ($products as $uniqueCode) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['uniqueCode' => $uniqueCode]);
            if (!$product) {
                return new JsonResponse(['error' => "Product with code $uniqueCode not found"], 404);
            }

            $warehouseProduct = $repository->findOneBy(['warehouse' => $id, 'product' => $product->getId()]);
            if (!$warehouseProduct) {
                return new JsonResponse(['error' => "Product with code $uniqueCode not found in warehouse"], 404);
            }

            if ($warehouseProduct->getReservedQuantity() === 0) {
                return new JsonResponse(['error' => "Can't unreserved product with code $uniqueCode, because 0 product reserved"], 400);
            }

            $warehouseProduct->setReservedQuantity($warehouseProduct->getReservedQuantity() - 1);
            $this->entityManager->persist($warehouseProduct);
        }

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Products unreserved successfully']);
    }
}
