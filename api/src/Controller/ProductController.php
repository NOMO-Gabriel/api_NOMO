<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name:"Products", description: "Routes about Products")]
class ProductController extends AbstractController
{
    /*
     *  List all products
     * */
    #[Route('/api/products', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products',
        description: 'Retrieve a list of all products.',
        summary: 'List all products | PUBLIC ACCESS',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Product::class, groups: ['product.show']))
                )
            )
        ]
    )]
    public function index(ProductRepository $repository): JsonResponse
    {
        $products = $repository->findAll();
        if (!$products) {
            return new JsonResponse('Product not found in database', Response::HTTP_NOT_FOUND);
        }
        return $this->json($products, Response::HTTP_OK, [], [
            'groups' => ['product.show']
        ]);
    }

    /*
     *  Show a product by its ID
     * */
    #[Route('/api/product/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/product/{id}',
        description: 'Retrieve a product by its ID.',
        summary: 'Show a product by its ID | PUBLIC ACCESS',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the product to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    ref: new Model(type: Product::class, groups: ['product.show'])
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            )
        ]
    )]
    public function show(ProductRepository $repository, int $id): JsonResponse
    {
        $product = $repository->find($id);
        if (!$product) {
            return new JsonResponse('Product not found', Response::HTTP_NOT_FOUND);
        }
        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => ['product.show']
        ]);
    }

    /*
     *  List products by category
     * */
    #[Route('/api/products/category/{categoryId}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/products/category/{categoryId}',
        description: 'Retrieve all products for a specific category.',
        summary: 'List products by category | PUBLIC ACCESS',
        parameters: [
            new OA\Parameter(
                name: 'categoryId',
                description: 'ID of the category to filter products by',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Product::class, groups: ['product.category']))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Category or products not found'
            )
        ]
    )]
    public function category(CategoryRepository $repository, int $categoryId): JsonResponse
    {
        $category = $repository->find($categoryId);
        if (!$category) {
            return new JsonResponse('Category not found', Response::HTTP_NOT_FOUND);
        }
        $products = $category->getProducts();
        if (!$products) {
            return new JsonResponse('No products found in this category', Response::HTTP_NOT_FOUND);
        }
        return $this->json($products, Response::HTTP_OK, [], [
            'groups' => ['product.category']
        ]);
    }

    /*
     *  Create a new product
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/product/create',
        description: 'Create a new product with the given data.',
        summary: 'Create a new product | ROLE_EDIT ACCESS',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Product::class, groups: ['product.create'])
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product created successfully',
                content: new OA\JsonContent(
                    ref: new Model(type: Product::class, groups: ['product.create'])
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input data'
            )
        ]
    )]
    public function create(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $product = new Product();
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['images', 'createdAt', 'id'],
            'groups' => ['product.create']
        ]);

        $dataArray = json_decode($request->getContent(), true);
        $categoryId = $dataArray['category']['id'];
        if (!$categoryId) {
            return new JsonResponse('Please insert categoryId in your request inside category object', Response::HTTP_BAD_REQUEST);
        }

        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            return new JsonResponse('Category not found', Response::HTTP_NOT_FOUND);
        }
        $product->setCategory($category);

        $image = new Image();
        $imageUrl = $dataArray['mainImage']['url'];
        if (!$imageUrl) {
            return new JsonResponse('Please insert imageUrl into your request inside mainImage object', Response::HTTP_BAD_REQUEST);
        }
        $imageDescription = $dataArray['mainImage']['description'];
        $image->setUrl($imageUrl);
        if ($imageDescription) {
            $image->setDescription($imageDescription);
        }
        $product->setMainImage($image);
        $product->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($image);
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json($product, Response::HTTP_CREATED, [], [
            'groups' => 'product.create'
        ]);
    }

    /*
     *  Update an existing product
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/{id}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/product/{id}/update',
        description: 'Update the product with the given ID using the provided data.',
        summary: 'Update an existing product | ROLE_EDIT ACCESS',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: Product::class, groups: ['product.create'])
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the product to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated successfully',
                content: new OA\JsonContent(
                    ref: new Model(type: Product::class, groups: ['product.create'])
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Product or category not found'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input data'
            )
        ]
    )]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            return new JsonResponse('Product not found', Response::HTTP_NOT_FOUND);
        }

        $product = $serializer->deserialize($request->getContent(), Product::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['images', 'createdAt', 'id'],
            'groups' => ['product.create']
        ]);

        $dataArray = json_decode($request->getContent(), true);
        $categoryId = $dataArray['category']['id'];
        if (!$categoryId) {
            return new JsonResponse('Please insert categoryId in your request inside category object', Response::HTTP_BAD_REQUEST);
        }
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            return new JsonResponse('Category not found', Response::HTTP_NOT_FOUND);
        }
        $product->setCategory($category);

        $image = new Image();
        $imageUrl = $dataArray['mainImage']['url'];
        if (!$imageUrl) {
            return new JsonResponse('Please insert imageUrl into your request inside mainImage object', Response::HTTP_BAD_REQUEST);
        }
        $imageDescription = $dataArray['mainImage']['description'];
        $image->setUrl($imageUrl);
        if ($imageDescription) {
            $image->setDescription($imageDescription);
        }
        $product->setMainImage($image);

        $entityManager->flush();

        return $this->json($product, Response::HTTP_OK, [], [
            'groups' => 'product.create'
        ]);
    }

    /*
     *  Delete a product by its ID
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/{id}/delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/product/{id}/delete',
        description: 'Delete the product with the given ID.',
        summary: 'Delete a product by its ID | ROLE_EDIT ACCESS',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the product to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Product deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            )
        ]
    )]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
