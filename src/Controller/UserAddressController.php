<?php

namespace App\Controller;

use App\DTO\UserAddressDto;
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

            $addressDto = new UserAddressDto();

            if (isset($data['street'])) {
                $addressDto->street = $data['street'];
            }

            if (isset($data['housenumber'])) {
                $addressDto->housenumber = $data['housenumber'];
            }

            if (isset($data['postcode'])) {
                $addressDto->postcode = $data['postcode'];
            }

            if (isset($data['city'])) {
                $addressDto->city = $data['city'];
            }

            if (isset($data['additional'])) {
                $addressDto->additional = $data['additional'];
            }

            $violations = $this->validator->validate($addressDto);

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
