<?php

namespace App\Controller;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request, Session $session)
    {
        $slackToken = $session->get('slack_token');

        if (!$slackToken instanceof AccessTokenInterface) {
            return $this->redirectToRoute('slack_connect');
        }

        if ($request->get('term')) {
            return $this->redirectToRoute('search_term', ['term' => urlencode($request->get('term'))]);
        }

        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }
}
