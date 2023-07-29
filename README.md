# Mon Suivi Crypto

*Mon Suivi Crypto* est une application de démonstration qui n'est pas destinée à être déployée en production.

## Installation

*Mon Suivi Crypto* est une application PHP/MySQL basée sur le framework Symfony.

### Prérequis

Selon le mode d'installation:
 * Docker ou
 * PHP8.2, MySQL/MariaDB ou PostGreSQL ou SQLite, Apache avec mod PHP ou n'importe quel serveur web faisant du reverse proxy avec PHP-FPM. Composer pour installer les dépendances.

Concernant PHP, les extansions suivantes sont nécessaires:
 * XML
 * Curl

### Docker

Lancez la commande

    docker run

### Installation sans Docker

Copier/coller les fichiers PHP dans un serveur web possédant les prerequis listés précédement.
Placez-vous à la racine du projet (l'endroit de la copie)

#### Installation du backend

Prérequis:
 - PHP 8.2
 - Composer (PHP)
 - Serveur BDD ou SQLite
 - nodejs et yarn

Exécuter la commande `composer install`
Générez les clés de sessions JWT à l'aide de la commande `php bin/console lexik:jwt:generate-keypair`
Editez la variable `DATABASE_URL` dans le fichier `.env` avec vos préférences en matière de base de données
Cet utilisateur doit exister en base de données et avoir les droits suffisants pour créer la BDD de l'application.
Lancez la commande `php bin/console doctrine:database:create`
Si la base a déjà été créée, vous pouvez installer le schéma avec la commande `php bin/console doctrine:migrations:migrate`
Si vous souhaitez explorer l'application avec un jeu de données, lancez la commande `php bin/console doctrine:fixtures:load`
Un utilisateur de référence avec le login bbalet et le mote de passe identique au login est alors créé.


#### Installation du frontend

Toujours à la racine du projet, installez les dépendances avec la commande `yarn install`
Compilez les ressources graphiques à l'aide de la commande `yarn build`

à des fins de démo/dév, vous pouvez lancer le serveur web inclut avec Symfony à l'aide de la commande `symfony server:start`.

## Utilisation



