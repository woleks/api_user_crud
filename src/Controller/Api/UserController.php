<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api/users", name="api_")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     * @param int $id
     * @return JsonResponse|Response
     */
    public function show($id)
    {
        if ($user = $this->userRepository->find($id)) {
            return $this->json(['data' => $user]);
        }

        return $this->json([
            'error' => [
                'code' => 404,
                'message' => 'User not found.'
            ]
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/", name="user_list", methods={"GET"})
     * @return JsonResponse|Response
     */
    public function list()
    {
        return $this->json(['data' => $this->userRepository->findAll()]);
    }

    /**
     * @Route("/{id}", name="user_edit", methods={"PUT"})
     * @param Request $request
     * @param int $id
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */
    public function update(Request $request, int $id, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        if ($user = $this->userRepository->find($id)) {
            $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
            $formData = json_decode($request->getContent(), true);
            $form->submit($formData);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $formData['password']
                ));
                $em->persist($user);
                $em->flush();

                return $this->json(['message' => 'User updated!']);
            }

            return new JsonResponse([$this->getErrorsFromForm($form)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            'error' => [
                'code' => 404,
                'message' => 'User not found.'
            ]
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && $childErrors = $this->getErrorsFromForm($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }
        return $errors;
    }

    /**
     * @Route("/", name="user_create", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */
    public function create(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
        $formData = json_decode($request->getContent(), true);
        $form->submit($formData);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
                $formData['password']
            ));
            $em->persist($user);
            $em->flush();
            return $this->json(['message' => 'User created!'], JsonResponse::HTTP_CREATED, [
                'Location' => $this->generateUrl('api_user_show', ['id' => $user->getId()])
            ]);
        }

        return $this->json(['errors' => $this->getErrorsFromForm($form)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        if ($user = $this->userRepository->find($id)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            return $this->json(['message' => 'User deleted!']);
        }

        return $this->json([
            'error' => [
                'code' => 404,
                'message' => 'User not found.'
            ]
        ], JsonResponse::HTTP_NOT_FOUND);
    }
}
