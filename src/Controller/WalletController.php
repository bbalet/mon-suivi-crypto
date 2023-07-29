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

class WalletController extends AbstractController
{
    /**
     * Display the wallet of the connected user
     *
     * @return Response
     */
    #[Route('/wallet', name: 'app_wallet')]
    public function index(EntityManagerInterface $entityManager, LoggerInterface $logger, HttpClientInterface $client): Response
    {
        //Get the connected user object and its cryptos
        $user = $this->getUser();
        $wallet = $entityManager->getRepository(Wallet::class)->findBy(['user' => $user])[0];
        $cryptosInDB = $wallet->getCryptoCurrencies();

        //Let's get the 24h history of the crypto, first by the cache, and then
        //do a request in case of cache miss
        $cache = new FilesystemAdapter('cache.app.cryptos.history.24h');
        $cryptosInWallet = [];
        foreach ($cryptosInDB as $crypto) {
            $logger->debug('Trying to hit the cache of cryptos history');
            $value = $cache->get($crypto->getSymbol(), function (ItemInterface $item) use ($client, $crypto, $logger): string {
                $logger->debug('Cache miss for ' . $crypto->getSymbol());
                $item->expiresAfter(3600);
                //Les crypto monnaies sont côtées par paires (par ex. entre elles)
                //Nous prenons l'hypothèse (au moins pour la première itération) que les
                //utilisateurs sont intéressés par la paire crypto/USDT
                $response = $client->request(
                    'GET',
                    'https://api.kucoin.com/api/v1/market/stats?symbol=' . $crypto->getSymbol() . '-USDT'
                );
                $content = $response->getContent();
                $cryptoData = json_decode($content);
                $cryptoHist = [
                    'id' => $crypto->getId(),
                    'symbol' => $crypto->getSymbol(),
                    'high' => $cryptoData->data->high,
                    'low' => $cryptoData->data->low,
                    'volume' => round(floatval($cryptoData->data->volValue)),
                    'sell' => $cryptoData->data->sell,
                    'last' => $cryptoData->data->last,
                ];
                return serialize($cryptoHist);
            });
            array_push($cryptosInWallet, unserialize($value));
        }


        return $this->render('wallet/index.html.twig', [
            'controller_name' => 'WalletController',
            'cryptos' => $cryptosInWallet
        ]);
    }
}
