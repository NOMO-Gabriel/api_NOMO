<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Tag(name:"Images", description: "Routes about Images")]
class ImageController extends AbstractController
{
    /*
     *  List all images
     * */
    #[Route('/api/images', methods: ['GET'])]
    #[OA\Get(
        path: '/api/images',
        description: 'Retrieve a list of all images in the system.',
        summary: 'List all images',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Image::class, groups: ['image.show']))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No images found'
            )
        ]
    )]
    public function index(ImageRepository $repository): JsonResponse
    {
        $images = $repository->findAll();
        if (! $images ){
            return new JsonResponse("images not found in database",Response::HTTP_NOT_FOUND);
        }
        return $this->json($images,Response::HTTP_OK,[],[
            'groups' => ['image.show']
        ]);
    }

    /*
     *  Present an image by id
     * */
    #[Route('/api/image/{id}' , methods: ['GET'])]
    #[OA\Get(
        path: '/api/image/{id}',
        description: 'Retrieve an image by its Id.',
        summary: 'Get an image by Id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the image to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image details',
                content: new OA\JsonContent(ref: new Model(type: Image::class, groups: ['image.show']))
            ),
            new OA\Response(
                response: 404,
                description: 'Image not found in database'
            )
        ]
    )]
    public function show(ImageRepository $repository, int $id): Response
    {
        $image = $repository->find($id);
        if (! $image ){
            return new JsonResponse("image not found",Response::HTTP_NOT_FOUND);
        }
        return $this->json($image,Response::HTTP_OK,[],[
            'groups' => ['image.show']
        ]);
    }

    /*
     *  Create a new image
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/image/product/{productId}/create' , methods: ['POST'])]
    #[OA\Post(
        path: '/api/image/product/{productId}/create',
        description: 'Create a new image associated with a product.',
        summary: 'Create an image',
        requestBody: new OA\RequestBody(
            description: 'Image data to create',
            content: new OA\JsonContent(ref: new Model(type: Image::class, groups: ['image.create']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'productId',
                description: 'Id of the product to associate the image with',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Image created successfully',
                content: new OA\JsonContent(ref: new Model(type: Image::class, groups: ['image.create']))
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $productId): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product){
            return new JsonResponse("product not found", Response::HTTP_NOT_FOUND);
        }
        $image = new Image();
        $image = $serializer->deserialize($request->getContent(), Image::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $image,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'product'],
            'groups' => ['image.create']
        ]);
        $image->setProduct($product);
        $entityManager->persist($image);
        $entityManager->flush();
        return $this->json($image, Response::HTTP_CREATED, [], ['groups' => ['image.create']]);
    }

    /*
     *  Update an existing image
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/image/{id}/update' , methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/image/{id}/update',
        description: 'Update an existing image.',
        summary: 'Update an image',
        requestBody: new OA\RequestBody(
            description: 'Updated image data',
            content: new OA\JsonContent(ref: new Model(type: Image::class, groups: ['image.create']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the image to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image updated successfully',
                content: new OA\JsonContent(ref: new Model(type: Image::class, groups: ['image.create']))
            ),
            new OA\Response(
                response: 404,
                description: 'Image or product not found'
            )
        ]
    )]
    public function update(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $id): JsonResponse
    {
        $image = $entityManager->getRepository(Image::class)->find($id);
        if (!$image){
            return new JsonResponse("image not found", Response::HTTP_NOT_FOUND);
        }

        $image = $serializer->deserialize($request->getContent(), Image::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $image,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'product'],
            'groups' => ['image.create']
        ]);

        $arrayData = json_decode($request->getContent(), true);
        $productId = $arrayData['product']['id'];
        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product){
            return new JsonResponse("product not found", Response::HTTP_NOT_FOUND);
        }
        $image->setProduct($product);
        $entityManager->persist($image);
        $entityManager->flush();
        return $this->json($image, Response::HTTP_OK, [], ['groups' => ['image.create']]);
    }

    /*
     *  Delete an image
     * */
    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/image/{id}/delete' , methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/image/{id}/delete',
        description: 'Delete an image by its Id.',
        summary: 'Delete an image',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the image to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Image deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Image not found'
            )
        ]
    )]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $image = $entityManager->getRepository(Image::class)->find($id);
        if (!$image){
            return new JsonResponse("image not found", Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($image);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
