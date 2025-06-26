<?php

namespace App\Controller;

use App\DTO\UserRegistrationDto;
use App\DTO\UserLoginDto;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                UserRegistrationDto::class,
                'json'
            );

            // DTO validieren
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Validierungsfehler',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->registerUser($dto);

            return new JsonResponse([
                'success' => true,
                'message' => 'Benutzer erfolgreich registriert',
                'data' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ], Response::HTTP_CREATED);

        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                UserLoginDto::class,
                'json'
            );

            // DTO validieren
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Validierungsfehler',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->findUserByEmail($dto->email);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Ungültige Anmeldedaten'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // JWT Token erstellen
            $token = $this->jwtManager->create($user);

            return new JsonResponse([
                'success' => true,
                'message' => 'Erfolgreich angemeldet',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ein Fehler ist aufgetreten'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nicht authentifiziert'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }
}
