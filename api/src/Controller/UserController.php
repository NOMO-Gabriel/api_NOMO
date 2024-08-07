<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;


class UserController extends AbstractController
{
    #[Route('/api/users', methods: ['GET'])]
    public function index(UserRepository $repository): JsonResponse
    {
        $users = $repository->findAll();
        return $this->json($users,Response::HTTP_OK,[],[
            'groups' => ['user.index']
        ]);
    }


    #[Route('/api/user/{id}', methods: ['GET'])]
    public function show(UserRepository $repository, int $id): Response
    {
        $users = $repository->find($id);
        return $this->json($users,Response::HTTP_OK,[],[
            'groups' => ['user.index']
        ]);
    }


    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager,SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher):JsonResponse
    {
       $user = new User();
       $user = $serializer->deserialize($request->getContent(),User::class,'json',[
           AbstractNormalizer::OBJECT_TO_POPULATE => $user,
           AbstractNormalizer::IGNORED_ATTRIBUTES => ['password'],
           'groups'=> ['user.index']
       ]);
        $dataArray = json_decode($request->getContent(), true);
        $password = $passwordHasher->hashPassword($user, $dataArray["password"]);
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
       return $this->json($user, Response::HTTP_CREATED,[],['groups'=>['user.index']]);
    }


    #[Route('/api/login', methods: ['POST'])]
    public function login():JsonResponse
    {
        return new JsonResponse("Bonjour");
    }

    #[Route('/api/user/{id}/update', methods: ['PATCH'])]
    public function update(Request $request,int $id, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher):JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if(! $user){
            return new JsonResponse("user not found",Response::HTTP_NOT_FOUND);
        }
        $user = $serializer->deserialize($request->getContent(),User::class,'json',[
           AbstractNormalizer::OBJECT_TO_POPULATE=>$user,
            AbstractNormalizer::IGNORED_ATTRIBUTES=>['id','password'],
            'groups' => ['user.update']
        ]);
        $dataArray = json_decode($request->getContent(), true);
        $password = $passwordHasher->hashPassword($user, $dataArray["password"]);
        $user->setPassword($password);
        $entityManager->flush();
        return $this->json($user,Response::HTTP_OK,[],['groups'=>'user.update']);
    }


    #[Route('/api/roles/user-{userId}/role-{roleItem}/update', methods: ['PATCH'])]
    public function updateRoles(EntityManagerInterface $entityManager, int $userId, int $roleItem):JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($userId);
        if(! $user){
            return new JsonResponse("user not found",Response::HTTP_NOT_FOUND);
        }
        switch ($roleItem){
            case 0:
                $user->setRoles(["ROLE_USER"]);
                break;
            case 1:
                $user->setRoles(["ROLE_EDIT"]);
                break;
            case 2:
                $user->setRoles(["ROLE_ADMIN"]);
                break;
            default:
            return new JsonResponse("role not found",Response::HTTP_NOT_FOUND);
                break;
        }
        $entityManager->flush();
        return $this->json($user,Response::HTTP_OK,[],['groups'=>['user.index']]);
    }


    #[Route('/api/user/{id}/delete' , methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id):JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null,Response::HTTP_NO_CONTENT);
    }

}
