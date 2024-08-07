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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CategoryController extends AbstractController
{

    #[IsGranted("ROLE_USER")]
    #[Route('/api/categories', methods: [ 'GET' ])]
    public function index(CategoryRepository $repository): JsonResponse
    {
        $categories = $repository->findAll();
        if (! $categories){
            return new JsonResponse("category not found in database",Response::HTTP_FOUND);
        }
        return $this->json($categories,Response::HTTP_OK,[],[
            'groups' => ['category.index']
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/api/category/{id}', methods: [ 'GET' ])]
    public function show(CategoryRepository $repository,int $id): JsonResponse
    {
        $category = $repository->find($id);
        if (! $category){
            return new JsonResponse("category not found in database",Response::HTTP_FOUND);
        }
        return $this->json($category,Response::HTTP_OK,[],[
            'groups' => ['category.show']
        ]);
    }

    #[IsGranted("ROLE_GRANT_EDIT")]
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

    #[IsGranted("ROLE_GRANT_EDIT")]
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

    #[IsGranted("ROLE_GRANT_EDIT")]
    #[Route('/api/category/{id}/delete' , methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id):JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if(! $category){
            return  new JsonResponse('category not found', Response::HTTP_FOUND);
        }
        $entityManager->remove($category);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }
    
    }
