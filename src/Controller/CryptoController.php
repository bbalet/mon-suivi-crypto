<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Wallet;

class CryptoController extends AbstractController
{
    /**
     * Extract the prices an timestamp for the return of the API call
     * This is an intermediary data one step before being suitable for chartjs
     * @param string $json
     * @return array associtive array with prices (data) over time (labels)
     */
    private function extractSellPricesOverTime(string $json): array
    {
        $cryptoData = json_decode($json, true);
        $labels = [];
        $data = [];
        $sellArray = array_filter($cryptoData['data'], function($arr) {
            return $arr['side'] == 'sell';
        });
        $labels = array_column($sellArray, 'time');
        $data = array_column($sellArray, 'price');
        //Convert from nano to milleseconds timestamps
        array_walk($labels, function ($value, $key)  use (&$labels){
            $labels[$key] = round(intval($value) / 1E6);
          });
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    #[Route('/crypto/{symbol}', name: 'app_crypto_symbol')]
    public function index(string $symbol, EntityManagerInterface $entityManager, LoggerInterface $logger, HttpClientInterface $client): Response
    {
        $cache = new FilesystemAdapter('cache.app.cryptos.history');
        $logger->debug('Trying to hit the cache of cryptos history');
        $value = $cache->get($symbol, function (ItemInterface $item) use ($client, $symbol, $logger): string {
            $logger->debug('Cache miss for ' . $symbol);
            $item->expiresAfter(3600);
            //Les crypto monnaies sont côtées par paires (par ex. entre elles)
            //Nous prenons l'hypothèse (au moins pour la première itération) que les
            //utilisateurs sont intéressés par la paire crypto/USDT
            $response = $client->request(
                'GET',
                'https://api.kucoin.com/api/v1/market/histories?symbol=' . $symbol . '-USDT'
            );
            $content = $response->getContent();
            $tradeInfos = $this->extractSellPricesOverTime($content);
            return serialize($tradeInfos);
        });
        $tradeInfos = unserialize($value);

        return $this->render('crypto/index.html.twig', [
            'symbol' => $symbol,
            'labels' => implode(",", $tradeInfos['labels']),
            'data' => implode(",", $tradeInfos['data']),
        ]);
    }
}
