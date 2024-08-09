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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Tag(name:"Users", description: "Routes about Users")]
class UserController extends AbstractController
{
    /*
     *  Register a new user
     * */
    #[Route('/api/register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/register',
        description: 'Register a new user.',
        summary: 'Register a user',
        requestBody: new OA\RequestBody(
            description: 'User registration data',
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.index']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully registered',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.index']))
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request'
            )
        ]
    )]
    public function register(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['password'],
            'groups' => ['user.index']
        ]);
        $dataArray = json_decode($request->getContent(), true);
        $password = $passwordHasher->hashPassword($user, $dataArray["password"]);
        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user.index']]);
    }

    /*
     *  List all users
     * */
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/users', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users',
        description: 'Retrieve a list of all users.',
        summary: 'List all users',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: User::class, groups: ['user.index']))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No users found'
            )
        ]
    )]
    public function index(UserRepository $repository): JsonResponse
    {
        $users = $repository->findAll();
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => ['user.index']
        ]);
    }

    /*
     *  Present a user by id
     * */
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/user/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/{id}',
        description: 'Retrieve a user by its Id.',
        summary: 'Get a user by Id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the user to retrieve',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User details',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.index']))
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    public function show(UserRepository $repository, int $id): Response
    {
        $user = $repository->find($id);
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.index']
        ]);
    }

    /*
     *  Update an existing user limited by role ADMIN
     * */
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/user/{id}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/user/{id}/update',
        description: 'Update an existing user limited by role ADMIN.',
        summary: 'Update a simple user ',
        requestBody: new OA\RequestBody(
            description: 'Updated user data',
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.update']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the user to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.update']))
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden for admin to edit another admin'
            )
        ]
    )]
    public function update(Request $request, int $id, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())&&(!in_array('ROLE_SUPER_ADMIN',$user->getRoles()))) {
            return new JsonResponse("admin can't edit another admin", Response::HTTP_FORBIDDEN);
        }
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'password'],
            'groups' => ['user.update']
        ]);
        $dataArray = json_decode($request->getContent(), true);
        $password = $passwordHasher->hashPassword($user, $dataArray["password"]);
        if ($password) {
            $user->setPassword($password);
        }
        $entityManager->flush();
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user.update']);
    }
    /*
     *  Update an existing user or admin
     * */
    #[IsGranted("ROLE_SUPER_ADMIN")]
    #[Route('/api/admin/{id}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/admin/{id}/update',
        description: 'Update an existing user.',
        summary: 'Update a user or admin',
        requestBody: new OA\RequestBody(
            description: 'Updated user data',
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.update']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the user to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.update']))
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),

        ]
    )]
    public function adminUpdate(Request $request, int $id, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'password'],
            'groups' => ['user.update']
        ]);
        $dataArray = json_decode($request->getContent(), true);
        $password = $passwordHasher->hashPassword($user, $dataArray["password"]);
        if ($password) {
            $user->setPassword($password);
        }
        $entityManager->flush();
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user.update']);
    }

    /*
     *  Update a simple user role
     * */
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/setRole/user-{userId}/role-{roleItem}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/setRole/user-{userId}/role-{roleItem}/update',
        description: 'Update a simple user role.',
        summary: 'Update simple user role',
        parameters: [
            new OA\Parameter(
                name: 'userId',
                description: 'Id of the user to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'roleItem',
                description: 'Role item to assign',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User role updated successfully',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.index']))
            ),
            new OA\Response(
                response: 404,
                description: 'User not found or role not found'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden for admin to update another admin'
            )
        ]
    )]
    public function updateRole(EntityManagerInterface $entityManager, int $userId, int $roleItem): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())&&(!in_array('ROLE_SUPER_ADMIN',$user->getRoles()))) {
            return new JsonResponse("admin can't edit another admin", Response::HTTP_FORBIDDEN);
        }
        switch ($roleItem) {
            case 0:
                $user->setRoles(["ROLE_USER"]);
                break;
            case 1:
                $user->setRoles(["ROLE_EDIT"]);
                break;
            case 2:
                $user->setRoles(["ROLE_GRANT_EDIT", "ROLE_EDIT"]);
                break;
            case 3:
                $user->setRoles(["ROLE_ADMIN", "ROLE_GRANT_EDIT", "ROLE_EDIT"]);
                break;
            default:
                return new JsonResponse("role not found", Response::HTTP_NOT_FOUND);
        }
        $entityManager->flush();
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user.index']);
    }
    /*
     *  Update a user or an admin role
     * */
    #[IsGranted("ROLE_SUPER_ADMIN")]
    #[Route('/api/setRole/admin-{adminId}/role-{roleItem}/update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/setRole/admin-{adminId}/role-{roleItem}/update',
        description: 'Update a  user or an admin role.',
        summary: 'Update a user or an admin role',
        parameters: [
            new OA\Parameter(
                name: 'adminId',
                description: 'Id of the user to update',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'roleItem',
                description: 'Role item to assign',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User role updated successfully',
                content: new OA\JsonContent(ref: new Model(type: User::class, groups: ['user.index']))
            ),
            new OA\Response(
                response: 404,
                description: 'User not found or role not found'
            )
        ]
    )]
    public function adminUpdateRole(EntityManagerInterface $entityManager, int $adminId, int $roleItem): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($adminId);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        switch ($roleItem) {
            case 0:
                $user->setRoles(["ROLE_USER"]);
                break;
            case 1:
                $user->setRoles(["ROLE_EDIT"]);
                break;
            case 2:
                $user->setRoles(["ROLE_GRANT_EDIT", "ROLE_EDIT"]);
                break;
            case 3:
                $user->setRoles(["ROLE_ADMIN", "ROLE_GRANT_EDIT", "ROLE_EDIT"]);
                break;
            case 4:
                $user->setRoles(["ROLE_SUPER_ADMIN","ROLE_ADMIN", "ROLE_GRANT_EDIT", "ROLE_EDIT"]);
                break;

            default:
                return new JsonResponse("role not found", Response::HTTP_NOT_FOUND);
        }
        $entityManager->flush();
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user.index']);
    }

    /*
     *  Delete a user not admin
     * */
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/user/{id}/delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/user/{id}/delete',
        description: 'Delete a user by its Id.',
        summary: 'Delete a user',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the user to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'User successfully deleted'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden to delete another admin'
            )
        ]
    )]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())&&(!in_array('ROLE_SUPER_ADMIN',$user->getRoles()))) {
            return new JsonResponse("admin can't delete another admin", Response::HTTP_FORBIDDEN);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    /*
 *  Delete a user or admin
 * */
    #[IsGranted("ROLE_SUPER_ADMIN")]
    #[Route('/api/admin/{id}/delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/admin/{id}/delete',
        description: 'Delete a user or admin by its Id.',
        summary: 'Delete a user or admin',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Id of the user to delete',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'User successfully deleted'
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),

        ]
    )]
    public function adminDelete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


}
