<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Product;
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

class ProductController extends AbstractController
{

    #[IsGranted("ROLE_USER")]
    #[Route('/api/products', methods: [ 'GET' ])]
    public function index(ProductRepository $repository): JsonResponse
    {
        $products = $repository->findAll();
        if(! $products){
            return  new JsonResponse('product not found in database', Response::HTTP_FOUND);
        }
        return $this->json($products,Response::HTTP_OK,[],[
          'groups' => ['product.index']
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/api/product/{id}', methods: [ 'GET' ])]
    public function show(ProductRepository $repository,int $id): JsonResponse
    {
        $product = $repository->find($id);
        if(! $product){
            return  new JsonResponse('product not found ', Response::HTTP_FOUND);
        }
        return $this->json($product,Response::HTTP_OK,[],[
            'groups' => ['product.show']
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/api/products/category/{categoryId}', methods: [ 'GET' ])]
    public function category(CategoryRepository $repository,int $categoryId): JsonResponse
    {
        $category =$repository->find($categoryId);
        if(! $category){
            return  new JsonResponse('category not found ', Response::HTTP_FOUND);
        }
        $products = $category->getProducts();
        if(! $products){
            return  new JsonResponse('product not found in this category', Response::HTTP_FOUND);
        }
        return $this->json($products,Response::HTTP_OK,[],[
            'groups' => ['product.category']
        ]);
    }

    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/create', methods: [ 'POST' ])]
    public function create(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): JsonResponse
    {
       $product = new Product();
       $product = $serializer->deserialize($request->getContent(), Product::class, 'json'
           ,[
               AbstractNormalizer::OBJECT_TO_POPULATE => $product,
               AbstractNormalizer::IGNORED_ATTRIBUTES => ['images','createdAt','id'],
               'groups' => ['product.create']
           ]
       );
       $dataArray = json_decode($request->getContent(), true);
       $categoryId = $dataArray['category']['id'];
        if(! $categoryId){

            return  new JsonResponse('please insert categoryId in your request inside category object', Response::HTTP_FOUND);
        }
       $category = $entityManager->getRepository(Category::class)->find($categoryId);
        if(! $category){
            return  new JsonResponse('category not found ', Response::HTTP_FOUND);
        }
       $product->setCategory($category);
       $image = new Image();
       $imageUrl = $dataArray['mainImage']['url'];
        if(! $imageUrl){
            return  new JsonResponse('please insert imageUrl into your request inside mainImage object', Response::HTTP_FOUND);
        }
       $imageDescription = $dataArray['mainImage']['description'];
       $image->setUrl($imageUrl);
        if($imageDescription){
            $image->setDescription($imageDescription);
        }
       $product->setMainImage($image);

       $product->setCreatedAt(new \DateTimeImmutable());
       $entityManager->persist($image);
       $entityManager->persist($product);
       $entityManager->flush();
       return $this->json($product,Response::HTTP_CREATED,[],[
           'groups' => 'product.create'
       ]);
    }

    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/{id}/update', methods:[ 'PATCH' ])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer):JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if(! $product){
            return  new JsonResponse('product not found ', Response::HTTP_FOUND);
        }
        $product = $serializer->deserialize($request->getContent(),Product::class,'json',
        [
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['images','createdAt','id'],
            'groups' => ['product.create']
        ]);
        $dataArray = json_decode($request->getContent(), true);
        $categoryId = $dataArray['category']['id'];
        if(! $categoryId){

            return  new JsonResponse('please insert categoryId in your request inside category object', Response::HTTP_FOUND);
        }
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        if(! $category){
            return  new JsonResponse('category not found ', Response::HTTP_FOUND);
        }
        $product->setCategory($category);
        $image = new Image();
        $imageUrl = $dataArray['mainImage']['url'];
        if(! $imageUrl){
            return  new JsonResponse('please insert imageUrl into your request inside mainImage object', Response::HTTP_FOUND);
        }
        $imageDescription = $dataArray['mainImage']['description'];
        $image->setUrl($imageUrl);
        if($imageDescription){
            $image->setDescription($imageDescription);
        }
        $product->setMainImage($image);
        $entityManager->flush();
        return $this->json($product,Response::HTTP_OK,[],[
            'groups' => 'product.create'
        ]);
    }

    #[IsGranted("ROLE_EDIT")]
    #[Route('/api/product/{id}/delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        if (! $product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
