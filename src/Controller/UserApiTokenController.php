<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api-tokens")
 */
class UserApiTokenController extends AbstractController
{
    /**
     * @Route("/", name="user_api_token_index", methods={"GET"})
     * @param ApiTokenRepository $apiTokenRepository
     * @return Response
     */
    public function index(ApiTokenRepository $apiTokenRepository): Response
    {
        return $this->render('user_api_token/index.html.twig', [
            'tokens' => $apiTokenRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/new", name="user_api_token_new", methods={"GET","POST"})
     * @return Response
     */
    public function new(): Response
    {
        $apiToken = new ApiToken($this->getUser());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($apiToken);
        $entityManager->flush();

        return $this->redirectToRoute('user_api_token_index');
    }

    /**
     * @Route("/{id}", name="user_api_token_show", methods={"GET"})
     * @param ApiToken $apiToken
     * @return Response
     */
    public function show(ApiToken $apiToken): Response
    {
        return $this->render('user_api_token/show.html.twig', [
            'token' => $apiToken,
        ]);
    }

    /**
     * @Route("/{id}", name="user_api_token_delete", methods={"DELETE"})
     * @param Request $request
     * @param ApiToken $apiToken
     * @return Response
     */
    public function delete(Request $request, ApiToken $apiToken): Response
    {
        if ($this->isCsrfTokenValid('delete' . $apiToken->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($apiToken);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_api_token_index');
    }
}
