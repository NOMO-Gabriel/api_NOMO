<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Groups(['image.index','image.show'])]
class ImageController extends AbstractController
{
    #[Route('/api/images', methods: ['GET'])]
    public function index(ImageRepository $repository): JsonResponse
    {
        $images = $repository->findAll();
        return $this->json($images,Response::HTTP_OK,[],[
            'groups' => ['image.index']
        ]);
    }
    #[Route('/api/image/{id}' , methods: ['GET'])]
    public function show(ImageRepository $repository, int $id): Response
    {
        $users = $repository->find($id);
        return $this->json($users,Response::HTTP_OK,[],[
            'groups' => ['image.index']
        ]);
    }
    #[Route('/api/image/{id}/delete' , methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id):JsonResponse
    {
        $image = $entityManager->getRepository(Image::class)->find($id);
        $entityManager->remove($image);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }
    #[Route('/api/image/product/{productId}/create' , methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $productId):JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($productId);
        if (!$product){
            return new JsonResponse(null,Response::HTTP_FOUND);
        }
        $image = new Image();
        $image = $serializer->deserialize($request->getContent(),Image::class,'json',[
            AbstractNormalizer::OBJECT_TO_POPULATE => $image,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id','product'] ,  'groups' => ['image.create']
        ]);
        $image->setProduct($product);
        $entityManager->persist($image);
        $entityManager->flush();
        return $this->json($image,Response::HTTP_CREATED, [],['groups'=> ['image.create']]);
    }
    #[Route('/api/image/{id}/update' , methods: ['PATCH'])]
    public function update(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, int $id):JsonResponse
    {
        $image = $entityManager->getRepository(Image::class)->find($id);
        if (!$image){
            return new JsonResponse(null,Response::HTTP_FOUND);
        }

        $image = $serializer->deserialize($request->getContent(),Image::class,'json',[
            AbstractNormalizer::OBJECT_TO_POPULATE => $image,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id','product'],
            'groups' => ['image.create']
        ]);

        $arrayData = json_decode($request->getContent(),true);
        $productId = $arrayData['product']['id'];
        $product = $entityManager->getRepository(Product::class)->find($productId);
        if($product){
            $image->setProduct($product);
        }
        $entityManager->persist($image);
        $entityManager->flush();
        return $this->json($image,Response::HTTP_OK,[],['groups'=>['image.create']]);
    }
}
