# Dictionnaire de données

## Récapitulatif mensuel (`monthly_summary`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de notre récapitulatif|
|title|VARCHAR(255)|NOT NULL|Le titre de notre récapitulatif|
|number_of_day|TINYINT|NOT NULL, UNSIGNED, DEFAULT 0|nombre de jour travailé|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création du récapitulatif|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour du récapitulatif|


## Congé (`vacation`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant des congés|
|type_of_vacation|VARCHAR(64)|NOT NULL|Le type de congé|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création des congés|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour des congés|
|start_date|TIMESTAMP|NOT NULL|La date du début des congés|
|end_date|TIMESTAMP|NOT NULL|La date de fin des congés|
|is_validated|BOOLEAN|NOT NULL|Si les congés sont validé ou non|


## Maladie (`sick_day`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant des arrêts maladie|
|created_At|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création de l'arrêt maladie|
|updated_At|TIMESTAMP|NULL|La date de la dernière mise à jour de l'arrêt maladie|
|start_date|TIMESTAMP|NULL|La date du début de l'arrêt maladie|
|end_date|TIMESTAMP|NULL|La date de fin de l'arrêt maladie|
|description|VARCHAR(64)|NULL|La description de l'arrêt maladie|



## Formation (`formation`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant des formations|
|title|VARCHAR(255)|NOT NULL|Le titre de la formation|
|created_At|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création des formations|
|updated_At|TIMESTAMP|NULL|La date de la dernière mise à jour des formations|
|start_date|TIMESTAMP|NOT NULL|La date du début des formations|
|end_date|TIMESTAMP|NOT NULL|La date de fin des formations|
|is_validated|BOOLEAN|NOT NULL|Si les formations sont validé ou non|


## Role (`role`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant du role|
|role_title|VARCHAR(50)|NOT NULL|Le titre de notre role|
|descrption|VARCHAR(255)|NOT NULL|La categorie de notre role|


## My survey (`my_survey`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant des sondages|
|title|VARCHAR(255)|NOT NULL|Le titre des sondages|
|created_At|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création des sondages|
|updated_At|TIMESTAMP|NULL|La date de la dernière mise à jour des sondages|
|start_date|TIMESTAMP|NULL|La date du début des sondages|
|end_date|TIMESTAMP|NOT NULL|La date de fin des sondages|
|is_done|BOOLEAN|NOT NULL|Si les sondages sont éffectués ou non|
|owner|VARCHAR(255)|NOT NULL|Le proprietaire des sondages|


## Project (`project`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant du projet|
|title|VARCHAR(255)|NOT NULL|Le titre du projet|
|created_At|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création du projet|
|updated_At|TIMESTAMP|NULL|La date de la dernière mise à jour du projet|
|start_date|TIMESTAMP|NULL|La date du début du projet|
|end_date|TIMESTAMP|NULL|La date de fin du projet|
|description|TEXT|NULL|La description du projet|


## Document (`my_document`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant du document|
|url|VARCHAR(255)|NOT NULL|L'adresse de notre document|
|category|VARCHAR(255)|NOT NULL|La categorie de notre document|



## User (`user`)

|Champ|Type|Spécificités|Description|
|-|-|-|-|
|id|INT|PRIMARY KEY, NOT NULL, UNSIGNED, AUTO_INCREMENT|L'identifiant de l'user|
|created_at|TIMESTAMP|NOT NULL, DEFAULT CURRENT_TIMESTAMP|La date de création du l'user|
|updated_at|TIMESTAMP|NULL|La date de la dernière mise à jour du l'user|
|begining_date|TIMESTAMP|NULL|La date d'entrée de l'user|
|end_date|TIMESTAMP|NULL|La date de sortie de l'user|
|lastname|VARCHAR(64)|NOT NULL|Le nom de l'user|
|firstname|VARCHAR(64)|NOT NULL|Le prénom de l'user|
|birthday|TIMESTAMP|NULL|La date de naissance de l'user|
|avatar|VARCHAR(255)|NULL|url de l'avatar de l'user|
|SS_id|TINYINT|NOT NULL, UNSIGNED, DEFAULT 0|numéro de sécurité sociale de l'user|
|probation_period|TIMESTAMP|NULL|La date de la fin de la période d'essai|
|contractual_status|VARCHAR(64)|NULL|Le status de l'user|
|e-mail|VARCHAR(64)|NOT NULL|L'e-mail de l'user|
|password|VARCHAR(255)|NOT NULL|Le hash du mot de passe de l'user|
|is_employed|BOOLEAN|NULL|Si l'user est employé ou non|
|phone_number|INT|NULL, UNSIGNED, DEFAULT 0|numéro de téléphone de l'user|
|address|VARCHAR(255)|NULL|L'adresse de l'user|
