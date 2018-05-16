# PHP-ArrayExtensions
For PhalconPHP - automatic cast from array


# Class mapping :

trait TraitClientsCommandesSimple
{
    /**
     * @var string
     */
    public $formateuro;

    public function render()
    {
        $this->formateuro =  $this->montant .' euro(s)';
        return $this;
    }
}


class ClientsCommandesSimple
{
    use TraitClientsCommandesSimple;

    /**
     * id de l'utilisateur
     * @var int
     */
    public $id;

    /**
     * Nom
     * @var string
     */
    public $nom;

    /**
     * Prénom
     * @var string
     */
    public $prenom;


    /**
     * Date de la commande
     * @var DateTime
     * @format Y-m-d H:i:s
     */
    public $date_commande;

    /**
     * Numéro de commande
     * @var string
     */
    public $numero;

    /**
     * Montant de commande
     * @var float
     */
    public $montant;



}


# Simple example :

		$sql = $this->modelsManager->createBuilder()
            ->columns('Clients.id as id, nom, prenom, date_commande, numero, montant')
            ->from('Clients')
            ->innerJoin('ClientsCommandes')
            ->getQuery()
            ->getSql();

        $results = $this->getDI()->get('adnDb')->fetchAll($sql['sql']);
        
        $obj = new ArrayExtensions();

        foreach ($results as $result)
        {
            $res =  $obj->toObject( $result,  new ClientsCommandesSimple());
            if ($res instanceof ClientsCommandesSimple) // for automplete object in EDI
            {
                $data[] = $res;
            }
        }