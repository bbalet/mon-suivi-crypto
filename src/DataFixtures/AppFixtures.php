<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\CryptoCurrency;
use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private Generator $faker;
    private array $cryptos;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        //Create the currencies
        $this->cryptos = array(
            'BTC' => 'Bitcoin',
            'ETH' => 'Ethereum',
            'XRP' => 'Ripple',
            'LTC' => 'Litecoin',
            'BCH' => 'Bitcoin Cash',
            'ADA' => 'Cardano',
            'DOT' => 'Polkadot',
            'LINK' => 'Chainlink',
            'XLM' => 'Stellar',
            'DOGE' => 'Dogecoin',
            'UNI' => 'Uniswap',
            'AAVE' => 'Aave',
            'XTZ' => 'Tezos',
            'ATOM' => 'Cosmos',
            'EOS' => 'EOS',
            'XMR' => 'Monero',
        );
        foreach ($this->cryptos as $symbol => $name) {
            $crypto = new CryptoCurrency();
            $crypto->setSymbol($symbol);
            $crypto->setName($name);
            $manager->persist($crypto);
            $this->addReference($symbol, $crypto);
        }
        // create a reference user and 20 more users
        $this->createUser($manager, 'Benjamin', 'BALET');
        for ($i = 0; $i < 20; $i++) {
            $this->createUser($manager, $this->faker->firstName(), $this->faker->lastName());
        }
        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $firstName, string $lastName) {
        $user = new User();
        $username = mb_strtolower(mb_substr($firstName, 0, 1) . $lastName);
        $user->setUsername($username);
        $password = $this->hasher->hashPassword($user, $username);
        $user->setPassword($password);
        $user->setFirstname($firstName);
        $user->setLastname($lastName);
        $user->setEmail($username . '@example.org');
        $user->setBirthdate($this->faker->dateTimeBetween('-70 year', '-18 year'));
        $manager->persist($user);

        //Attach a random number of cryptos to the wallet
        $wallet = new Wallet();
        $wallet->setUser($user);
        $positions = $this->faker->randomElements(
            array_keys($this->cryptos), $this->faker->numberBetween(3, count($this->cryptos) - 5)
        );
        foreach ($positions as $symbol) {
            $wallet->addCryptocurrency($this->getReference($symbol));
        }
        $manager->persist($wallet);
    }
}