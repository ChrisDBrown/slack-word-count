<?php

namespace App\Controller;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ResultsController extends AbstractController
{
    private const PAGE_LIMIT = 5;

    /**
     * @Route("/{term}", name="search_term")
     */
    public function searchForTerm(
        string $term,
        Session $session,
        CacheInterface $cache
    ) {
        $slackToken = $session->get('slack_token');

        if (!$slackToken instanceof AccessTokenInterface) {
            return $this->redirectToRoute('slack_connect');
        }

        $term = strtolower($term);
        $teamId = $slackToken->getValues()['team_id'];
        $cacheKey = sprintf('%s_%s', $teamId, $term);
        $term = urldecode($term);

        if ($cache->has($cacheKey)) {
            $results = json_decode($cache->get($cacheKey), true);
        } else {
            $results = $this->getMessagesForQuery($term, $slackToken->getToken());

            $cache->set($cacheKey, json_encode($results));
        }

        $users = [];

        foreach ($results as $result) {
            if (!$result['channel']['is_channel'] || $result['channel']['is_private'] || $result['user'] === '') continue;

            if (!isset($users[$result['username']])) {
                $users[$result['username']] = [];
            }

            $users[$result['username']][] = $result;
        }

        usort($users, function($a, $b) {
            return count($b) <=> count($a);
        });

        return $this->render('results/index.html.twig', [
            'users' => $users,
            'term' => $term
        ]);
    }

    private function getMessagesForQuery(string $query, string $token): array
    {
        $search = $this->doSearch($query, $token);

        $results = $search['messages']['matches'];

        $pageCount = $search['messages']['pagination']['page_count'];

        if ($pageCount > 1) {
            $pageLimit = $pageCount > self::PAGE_LIMIT ? self::PAGE_LIMIT : $pageCount;

            for ($i = 2; $i <= $pageLimit; $i++) {
                $search = $this->doSearch($query, $token, $i);

                $results = array_merge($results, $search['messages']['matches']);
            }
        }

        return $results;
    }

    private function doSearch(string $query, string $token, int $page = 1): array
    {
        $curl = new \CurlWrapper();

        $search = $curl->get('https://slack.com/api/search.messages', [
            'token' => $token,
            'query' => $query,
            'count' => 100,
            'page' => $page
        ]);

        return json_decode($search, true);
    }
}
