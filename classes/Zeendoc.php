<?php
class ZeenDoc
{
    public $wsdl;               // URL du fichier WSDL
    public $service_location;   // URL de l'emplacement du service
    public $service_uri;        // URI du service
    public $client;             // Client SOAP
    public $result;             // Résultat du client

    public function __construct($UrlClient = "deltic_demo")
    {
        $this->wsdl = "https://armoires.zeendoc.com/" . $UrlClient . "/ws/3_0/wsdl.php?WSDL";
        $this->service_location = "https://armoires.zeendoc.com/" . $UrlClient . "/ws/3_0/Zeendoc.php";
        $this->service_uri = "https://armoires.zeendoc.com/" . $UrlClient . "/ws/3_0/";
    }

    public function connect($userLogin, $userCPassword)
    {
        ini_set('soap.wsdl_cache_enabled', "0");

        $options = array(
            'location' => $this->service_location,
            'uri' => $this->service_uri,
            'trace' => true,
            'exceptions' => true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS + SOAP_USE_XSI_ARRAY_TYPE
        );

        try {
            $this->client = new SoapClient($this->wsdl, $options);

            // Appel de la méthode 'login' du service SOAP
            $result = $this->client->__soapCall(
                'login',
                array(
                    'Login' => $userLogin,
                    'Password' => '',
                    'CPassword' => $userCPassword
                )
            );

            if (isset($result->Error_Msg)) {
                echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
            } else {
                return $result;
            }
        } catch (SoapFault $fault) {
            return $fault;
        }
    }

    public function getNBDocument($collId)
    {
        // fonction qui permet de récupérer le nombre de document d'une collection

        // Appel de la méthode 'getNbDoc' du service SOAP
        $result = $this->client->__soapCall(
            'getNbDoc',
            array(
                'Coll_Id' => $collId,                                           // Identifiant de la collection
                'IndexList' => new SoapParam('', 'IndexList'),                  // Liste vide d'index
                'StrictMode' => new SoapParam('', 'StrictMode'),                // Mode strict désactivé
                'Fuzzy' => new SoapParam('', 'Fuzzy'),                          // Recherche floue désactivée
                'Order_Col' => new SoapParam('', 'Order_Col'),                  // Aucune colonne de tri spécifiée
                'Order' => new SoapParam('', 'Order'),                          // Ordre de tri par défaut
                'Saved_Query_Id' => new SoapParam(240, 'Saved_Query_Id'),       // Identifiant de la requête sauvegardée non spécifié
                'Query_Operator' => new SoapParam('', 'Query_Operator')         // Opérateur de requête par défaut
            )
        );

        if (isset($result->Error_Msg)) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
        } else {
            return $result;
        }
    }

    public function getDocument($collId, $resId)
    {
        $Wanted_Columns = 'Filename;custom_n7';
        // fonction qui permet de récupérer les documents d'une collection
        // champs souhaités : Code journal;Date;N° de compte;Compte auxiliaire;Pièce;Document;Libellé;Débit;Crédit;Date de l'échéance;Moyen de paiement;N° de ligne pour les documents associés;Documents associés;N° de ligne pour les ventilations analytiques;Plan analytique;Poste analytique;Montant de la ventilation analytique;Notes;Intitulé du compte;Information libre 1;
        // champs souhaités (custom) : 
        $result = $this->client->__soapCall(
            "getDocument",
            array(
                'Coll_Id' => $collId,                                                           // Identifiant de la collection
                'Res_Id' => new SoapParam($resId, 'Res_Id'),                                    // Identifiant du document
                'Upload_Id' => new SoapParam('', 'Upload_Id'),                                  // Identifiant de l'upload
                'Comments' => new SoapParam('', 'Comments'),                                    // Commentaires
                'Lines_ConfigFileName' => new SoapParam('', 'Lines_ConfigFileName'),            // Nom du fichier de configuration des lignes
                'Wanted_Columns' => new SoapParam($Wanted_Columns, 'Wanted_Columns')      // Colonnes souhaitées
            )
        );

        if (isset($result->Error_Msg)) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
        } else {
            return $result;
        }
    }

    public function getRights()
    {
        // fonction qui permet de récupérer toutes les informations de l'utilisateur connecté
        $result = $this->client->__soapCall(
            'getRights',
            array(
                'Get_ConfigSets' => 1
            )
        );

        $result = json_decode($result, true);

        if (isset($result['Error_Msg'])) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result['Error_Msg'] . "</div>";
        } else {
            return $result;
        }
    }

    public function getInfoPerso()
    {
        //fonction qui permet de récupérer les informations de l'utilisateur connecté
        $result = $this->getRights();

        $infosUser = $result['User'];

        return $infosUser;
    }

    public function getClassList()
    {
        //fonction qui permet de récupérer la liste des classeurs de l'utilisateur connecté
        $result = $this->getRights();

        $collections = $result['Collections'];

        foreach ($collections as $key => $value) {
            $collList[] = $value['Coll_Id'];
        }
        return $collList;
    }

    public function getIndexBAP()
    {
        //fonction qui permet de récupérer la liste des index BAP
        $result = $this->getRights();

        $result = $result['Collections'];

        $indexBAP = array();
        foreach ($result as $classeur) {
            foreach ($classeur['Index'] as $index) {
                if ($index['Label'] == 'BAP') {
                    $indexBAP[] = [
                        'Coll_Id' => $classeur['Coll_Id'],
                        'Index_Id' => $index['Index_Id'],

                    ];
                }
            }
        }




        return $indexBAP;
    }

    public function getNbBAPDoc($collId, $indexCustom)

    {
        // fonction qui permet de récupérer le nombre de document BAP à exporter
        $indexList = array(
            array(
                'Id' => 1,
                'Label' => $indexCustom,
                'Value' => 1
            )
        );



        // Appel de la méthode 'getNbDoc' du service SOAP
        $result = $this->client->__soapCall(
            'getNbDoc',
            array(
                'Coll_Id' => $collId,                                           // Identifiant de la collection
                'IndexList' => $indexList,                                      // recherche sur l'index BAP
                'StrictMode' => new SoapParam('', 'StrictMode'),                // Mode strict désactivé
                'Fuzzy' => new SoapParam('', 'Fuzzy'),                          // Recherche floue désactivée
                'Order_Col' => new SoapParam('', 'Order_Col'),                  // Aucune colonne de tri spécifiée
                'Order' => new SoapParam('', 'Order'),                          // Ordre de tri par défaut
                'Saved_Query_Id' => new SoapParam(240, 'Saved_Query_Id'),       // Identifiant de la requête sauvegardée non spécifié
                'Query_Operator' => new SoapParam('', 'Query_Operator')         // Opérateur de requête par défaut
            )
        );


        if (isset($result->Error_Msg)) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
        } else {
            $result = json_decode($result, true);
            return $result;
        }
    }





    private function searchDoc($collId, $indexList, $wantedColumns, $strictMode = 1, $orderCol = '', $order = '', $savedQueryId = '', $savedQueryName = '', $queryOperator = '', $from = '', $nbResults = '', $value1 = '', $makeUrlIndependentFromWebClientIP = '')
    {
        $param = array(
            'Coll_Id' => $collId,                       // Identifiant de la collection
            'IndexList' => $indexList,                  // Liste d'index
            'StrictMode' => $strictMode,                // Mode strict
            'Order_Col' => $orderCol,                   // Colonne de tri
            'Order' => $order,                          // Ordre de tri
            'Saved_Query_Id' => $savedQueryId,          // Identifiant de la requête sauvegardée
            'Saved_Query_Name' => $savedQueryName,      // Nom de la requête sauvegardée
            'Query_Operator' => $queryOperator,         // Opérateur de requête
            'From' => $from,                            // A partir de
            'Nb_Results' => $nbResults,                 // Nombre de résultats
            'Value_1' => $value1,                       // Valeur 1
            'Make_Url_Independant_From_WebClient_IP' => $makeUrlIndependentFromWebClientIP, // Indépendant de l'IP du client
            'Wanted_Columns' => $wantedColumns          // Colonnes souhaitées
        );

        // Appel de la méthode 'searchDoc' du service SOAP
        $result = $this->client->__soapCall(
            'searchDoc',
            $param
        );


        if (isset($result->Error_Msg)) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
        } else {
            return $result;
        }
    }



    public function searchAllDoc()
    {

        $collId = '';
        $indexList = array();
        $wantedColumns = 'filename';

        // Appeler la méthode searchDoc avec les paramètres de recherche
        return $this->searchDoc($collId, $indexList, $wantedColumns);
    }



    public function searchBAPDoc($Coll_Id)
    {

        $indexList = array(
            array(
                'Id' => 1,
                'Label' => 'custom_n7',
                'Value' => 1
            )
        );
        $wantedColumns = 'custom_n7';


        $res = $this->searchDoc($Coll_Id, $indexList, $wantedColumns);


        $res = json_decode($res, true);
        $docs = $res['Document'];

        return $docs;
    }




    private function updateDoc($collId, $resId, $indexList, $mode = 'UpdateGiven')
    {
        $param = array(
            'Coll_Id' => $collId,           // Identifiant de la collection
            'Res_Id' => $resId,             // Identifiant du document
            'IndexList' => $indexList,      // Liste d'index
            'Mode' => $mode                 // Mode de mise à jour
        );



        // Appel de la méthode 'updateDoc' du service SOAP
        $result = $this->client->__soapCall(
            'updateDoc',
            $param
        );

        if (isset($result->Error_Msg)) {
            echo "<div class='alert alert-danger' role='alert'>Erreur : " . $result->Error_Msg . "</div>";
        } else {
            return $result;
        }
    }

    public function changeBAP($collId, $resId)
    {
        $indexList = array(
            array(
                'Id' => 1,
                'Label' => 'custom_n7',
                'Value' => 2
            )
        );

        return $this->updateDoc($collId, $resId, $indexList);
    }

    public function changeAllBAP($collList)
    {

        foreach ($collList as $coll) {
            try {
                $docs = $this->searchBAPDoc($coll);

                foreach ($docs as $doc) {
                    $this->changeBAP($coll, $doc['Res_Id']);
                    echo "doc : " . $doc['Res_Id'] . " classeur : " . $coll . "<br>";
                }
            } catch (Exception $e) {
                // on passe
            }
        }
    }

    public function getSavedQueries($collId, $getNbResults = 1)
    {
        $param = array(
            'Coll_Id' => $collId,                           // Identifiant de la collection
            'Get_Nb_Results' => $getNbResults               // Identifiant du document
        );

        $result = $this->client->__soapCall(
            'getSavedQueries',
            $param
        );

        return $result;
    }
}