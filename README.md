# PHP-ArrayExtensions
For PhalconPHP - automatic cast from array


Class mapping :

class ClientsCommandesSimple
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var string
     */
    public $prenom;

    /**
     * @var DateTime
     * @format Y-m-d
     */
    public $date_commande;

    /**
     * @var string
     */
    public $numero;

    /**
     * @var float
     */
    public $montant;

    /**
     * @var string
     */
    public $formateuro;


    /**
     * @var string
     */
    public $date_commande_str ;
}


Simple example :

 $results = $this->modelsManager->createBuilder()
            ->columns('Clients.*, ClientsCommandes.*')
            ->from('Clients')
            ->innerJoin('ClientsCommandes')
            ->getQuery()
            ->execute();

       
        $obj = new ArrayExtensions();
        $data = array();
        foreach ($results as $result)
        {
            $res = array_merge( $result->clients->toArray(), $result->clientsCommandes->toArray());
            
            $data[] = $obj->toObject($res, new ClientsCommandesSimple());
        }

        return $data;
