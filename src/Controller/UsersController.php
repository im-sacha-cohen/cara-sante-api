<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\UsersService;
use App\Repository\UsersRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/user')]
class UsersController extends AbstractController
{
    private $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    #[Route('', name: 'users_new', methods: ['OPTIONS', 'POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits pour créer un utilisateur')]
    public function new(Request $request): JsonResponse
    {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        }
        
        $create = $this->usersService->new($data);

        if (!isset($create['status'])) {
            $status = 201;
        } else {
            $status = $create['status'];
        }

        return new JsonResponse($create, $status);
    }

    #[Route('/me', name: 'users_me', methods: ['OPTIONS', 'GET'])]
    public function me(): JsonResponse
    {
        $me = $this->getUser()->jsonSerialize();

        return new JsonResponse($me, 200);
    }

    #[Route('/me', name: 'users_me_update', methods: ['OPTIONS', 'PUT'])]
    public function meUpdate(Request $request): JsonResponse
    {
        if ($request->getContentType() === 'json') {
            $data = json_decode($request->getContent(), true);
        }

        $me = $this->usersService->updateMe($data, $this->getUser());

        return new JsonResponse($this->getUser()->jsonSerialize(), 200);
    }

    #[Route('/', name: 'users_all', methods: ['OPTIONS', 'GET'])]
    public function all(): JsonResponse
    {
        $users = $this->usersService->findAllExceptCurrent($this->getUser());

        return new JsonResponse($users, 200);
    }
}
