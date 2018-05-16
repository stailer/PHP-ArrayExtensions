# PHP-ArrayExtensions
For PhalconPHP - automatic cast from array


# Class mapping :

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
     * @format Y-m-d H:i:s
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

}


# Simple example :

		$results = $this->modelsManager->createBuilder()
            ->columns('Clients.*, ClientsCommandes.*')
            ->from('Clients')
            ->innerJoin('ClientsCommandes')
            ->getQuery()
            ->execute();

        $data = array();
        $obj = new ArrayExtensions();

        foreach ($results as $result)
        {
            $res = $obj->toObject( array_merge( $result->clients->toArray(), $result->clientsCommandes->toArray()), new ClientsCommandesSimple());

            if ($res instanceof ClientsCommandesSimple) // for automplete object in EDI
            {
                $data[] = $res;
            }
        }

        return $data;