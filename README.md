# exportBAP
export de BAP de Zeendoc vers ebp

## installation

### prérequis

- serveur web (apache)
```bash
sudo apt install apache2
```

- php
```bash
sudo apt install php
```

- phpmyadmin
```bash
sudo apt install phpmyadmin
```

- mysql
```bash
sudo apt install mysql-server
```

- git
```bash
sudo apt install git
```

### installation

- cloner le projet dans le dossier /var/www/html
```bash
git clone https://github.com/Mhivelin/exportBAPDELTIC.git
```

- créer la base de données






## site web

### pages :

#### index.php

> permet de consulter/ajouter les comptes lié a l'api







## base de données

### tables :

#### clients

| colonne      | type         | description            |
| ------------ | ------------ | ---------------------- |
| id_client    | TINYINT      | identifiant du client  |
| url_client   | VARCHAR(255) | url du client          |
| login        | VARCHAR(50)  | login du client        |
| mot_de_passe | VARCHAR(50)  | mot de passe du client |

#### classeurs

| colonne     | type       | description             |
| ----------- | ---------- | ----------------------- |
| id_classeur | VARCHAR(9) | identifiant du classeur |
| index_BAP   | VARCHAR(7) | index du classeur       |
| id_client   | TINYINT    | identifiant du client   |







### sql

### clients

```sql
CREATE TABLE CLIENT(
   id_client TINYINT,
   url_client VARCHAR(255) NOT NULL,
   login VARCHAR(50),
   mot_de_passe VARCHAR(50),
   PRIMARY KEY(id_client)
);

CREATE TABLE CLASSEUR(
   id_classeur VARCHAR(9),
   index_BAP VARCHAR(7),
   id_client TINYINT NOT NULL,
   PRIMARY KEY(id_classeur),
   FOREIGN KEY(id_client) REFERENCES CLIENT(id_client)
);

```

### requêtes

| requête        | description                                                                                |
| -------------- | ------------------------------------------------------------------------------------------ |
| connect.php    | permet de se connecter a la base de données                                                |
| **insert.php** | permet d'ajouter un client a la base de données en verifiant si il est valide dans Zeendoc |
| suppr.php      | permet de supprimer un client de la base de données                                        |


## ajout d'un client via le site web

| champ        | description                    |
| ------------ | ------------------------------ |
| url          | url Zeendoc du client          |
| login        | login Zeendoc du client        |
| mot de passe | mot de passe Zeendoc du client |


| champ automatique | description                                                                 |
| ----------------- | --------------------------------------------------------------------------- |
| id_classeur       | identifiant du classeur (tout les classeurs avec un index BAP sont ajoutés) |
| index_BAP         | id de l'index BAP (custom_XX)                                               |

