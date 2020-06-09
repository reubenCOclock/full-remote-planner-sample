# full-remote-planner
## pour lancer en local 
  ###verfier que tu est bien dans le projet avec la commande ls,si tu est dans le projet, tu verra une liste de fichiers et dossiers, sinon tu verra un seul dossiers
  ### a la racine du projet lancer la commande composer install 
  ### apres cette commande lancer php bin/console server:run 

## informations bdd en local 
  ### pour verifier le nom de la bdd, consulter le fichier .env et consulter l'url DATABASE_URL, tu devrai trouver la bdd fullremote, si elle n'est pas present, crée la dans phpmyadmin puis lancer les commandes suivantes: php bin/console make:migration et apres php/bin/console doctrine:migrations:migrate 

  ### dans la table user, il y a un champ is_hashed, si tu t'insere directement en tant que admin, mets le champ is_hashed a false dans la bdd (0), apres to premiere connexion tu sera automatiquement hashé et tu n'aura plus de souces a te faire

  ### pour lancer les fixtures lancer la commande php bin/console doctrine:fixtures:load 