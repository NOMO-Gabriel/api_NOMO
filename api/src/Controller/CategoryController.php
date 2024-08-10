<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

#[OA\Tag(name:"Categories", description: "Routes about Categories")]
class CategoryController extends AbstractController
{
    /*
     *  List all categories
     * */
    #[Route('/api/categories', methods: ['GET'])]
    #[OA\Get(
        path: '/api/categories',
        description: 'Retrieve a list of all categories in the system.',
        summary: 'List all categories',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of categories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Category::class, groups: ['category.show']))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No categories found'
            )
        ]
    )]
    public function index(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findAll();
        if (!$categories) {
            return new JsonResponse("No categories found", Response::HTTP_NOT_FOUND);
        }
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }


    /*
     *  Present a category by id
     * */
    #[Route('/api/category/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/category/{id}',
        description: 'Retrieve a category by its ID.',
        summary: 'Get a category by ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the category to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category details',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category.show']))
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
            )
        ]
    )]
    public function show(CategoryRepository $repository, int $id): JsonResponse
    {
        $category = $repository->find($id);
        if (!$category) {
            return new JsonResponse("Category not found", Response::HTTP_NOT_FOUND);
        }
        return $this->json($category, Response::HTTP_OK, [], [
            'groups' => ['category.show']
        ]);
    }

    /*
     *  Create a new category
     * */
    #[IsGranted("ROLE_GRANT_EDIT")]
    #[Route('/api/category/create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/category/create',
        description: 'Create a new category.',
        summary: 'Create a new category',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category.create']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category.create']))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input data'
            )
        ]
    )]
    public function create(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $category = new Category();
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $category,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'products'],
            'groups' => ['category.create']
        ]);
        $entityManager->persist($category);
        $entityManager->flush();
        return $this->json($category, Response::HTTP_CREATED, [], [
            'groups' => ['category.create']
        ]);
    }

    /*
     *  Update an existing category
     * */
    #[IsGranted("ROLE_GRANT_EDIT")]
    #[Route('/api/category/{id}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/category/{id}/update',
        description: 'Update an existing category.',
        summary: 'Update a category',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category.create']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the category to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category.create']))
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input data'
            )
        ]
    )]
    public function update(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request, int $id): JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            return new JsonResponse("Category not found", Response::HTTP_NOT_FOUND);
        }
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $category,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'products'],
            'groups' => ['category.create']
        ]);
        $entityManager->flush();
        return $this->json($category, Response::HTTP_OK, [], [
            'groups' => ['category.create']
        ]);
    }

    /*
     *  Delete a category
     * */
    #[IsGranted("ROLE_GRANT_EDIT")]
    #[Route('/api/category/{id}/delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/category/{id}/delete',
        description: 'Delete a category by its ID.',
        summary: 'Delete a category',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID of the category to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Category deleted'
            ),
            new OA\Response(
                response: 404,
                description: 'Category not found'
            )
        ]
    )]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            return new JsonResponse("Category not found", Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($category);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
