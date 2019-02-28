<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SlackAuthController extends AbstractController
{
    /**
     * @Route("/slack/connect", name="slack_connect")
     */
    public function connect(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('slack')->redirect(['search:read']);
    }

    /**
     * @Route("/slack/check", name="slack_check")
     */
    public function check(Session $session, ClientRegistry $clientRegistry)
    {
        $client = $clientRegistry->getClient('slack');
        $token = $client->getAccessToken();
        $session->set('slack_token', $token);

        return $this->redirectToRoute('homepage');
    }
}
