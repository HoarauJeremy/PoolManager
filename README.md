# Config du projet avec docker

## Etape 1: Récupérer les fichiers de config docker

- Verifier si vous avez les fichier nécessaire au bon fonctionnement de docker

## Etape 2:

- Lancer l'app **Docker Desktop**
- Dans un terminal (qui est ouvert dans le projet symfony) lancer la commande :
```sh
docker compose up -d
```

## Etape 3:
- Lancer la 
```sh
> docker exec -it pool-manager bash

> npm i
> npm run build

> composer i

> symfony console d:m:m # ou symfony console d:s:u --force

# pour les test:
> symfony console doctrine:fixtures:load --no-interaction
# ---------------------------

> symfony console cache:clear
```

## Etape 4:

- Vous diriger vers le lien [localhost](http://localhost:8000)

