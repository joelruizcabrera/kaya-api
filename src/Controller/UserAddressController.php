<?php

namespace App\Controller;

use App\Entity\UserAddress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/user/address', name: 'api_user_address_')]
class UserAddressController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addAddress(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'error' => 'User not found'
                ], Response::HTTP_UNAUTHORIZED);
            }
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'error' => 'Invalid data'
                ], Response::HTTP_BAD_REQUEST);
            }

            $constraints = new Assert\Collection([
                'postcode' => new Assert\NotBlank(), new Assert\Length(['max' => 255]),
                'street' => new Assert\NotBlank(), new Assert\Length(['max' => 255]),
                'housenumber' => new Assert\NotBlank(), new Assert\Length(['max' => 255]),
                'city' => new Assert\NotBlank(), new Assert\Length(['max' => 255]),
                'additional' => new Assert\Optional(), new Assert\Length(['max' => 255]),
            ]);

            $violations = $this->validator->validate($data, $constraints);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                return $this->json([
                    'error' => 'Validation failed',
                    'details' => $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $address = new UserAddress();
            $address->setStreet($data['street']);
            $address->setPostcode($data['postcode']);
            $address->setHousenumber($data['housenumber']);
            $address->setCity($data['city']);
            if ($data['additional']) {
                $address->setAdditional($data['additional']);
            }

            $user->addAddress($address);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Address added successfully',
                'address' => [
                    'id' => $address->getId(),
                    'street' => $address->getStreet(),
                    'postcode' => $address->getPostcode(),
                    'housenumber' => $address->getHousenumber(),
                    'city' => $address->getCity(),
                    'additional' => $address->getAdditional(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
