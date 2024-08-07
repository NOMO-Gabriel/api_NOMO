<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{
    #[Route('/api/categories', methods: [ 'GET' ])]
    public function index(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findAll();
        return $this->json($categories,Response::HTTP_OK,[],[
            'groups' => ['category.index']
        ]);
    }
    #[Route('/api/category/{id}', methods: [ 'GET' ])]
    public function show(CategoryRepository $repository,int $id): JsonResponse
    {
        $category = $repository->find($id);
        return $this->json($category,Response::HTTP_OK,[],[
            'groups' => ['category.show']
        ]);
    }
    #[Route('/api/category/create' , methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, SerializerInterface $serializer,Request $request):JsonResponse
    {
            $category = new Category();
            $category = $serializer->deserialize($request->getContent(),Category::class,'json',[
                AbstractNormalizer::OBJECT_TO_POPULATE => $category,
                AbstractNormalizer::IGNORED_ATTRIBUTES=>['id','products'],
                'groups' => ['category.create']
            ]);
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->json($category,Response::HTTP_CREATED,[],[
                'groups'=>['category.create']
            ]);
    }
    #[Route('/api/category/{id}/update' , methods: ['PATCH'])]
    public function update(EntityManagerInterface $entityManager, SerializerInterface $serializer,Request $request, int $id):JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if(! $category){
            return  new JsonResponse('category not found', Response::HTTP_FOUND);
        }
        $category = $serializer->deserialize($request->getContent(),Category::class,'json',[
            AbstractNormalizer::OBJECT_TO_POPULATE => $category,
            AbstractNormalizer::IGNORED_ATTRIBUTES=>['id','products'],
            'groups' => ['category.create']
        ]);
        $entityManager->flush();
        return $this->json($category,Response::HTTP_OK,[],[
            'groups'=>['category.create']
        ]);
    }

    #[Route('/api/category/{id}/delete' , methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id):JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        $entityManager->remove($category);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }


    }
