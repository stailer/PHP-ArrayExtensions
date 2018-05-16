<?php
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Backend\Memory;
/**
 * Class ArrayExtensions
 * @author Jean-François CAMBOT, toArrayRecursive : from PHP DOC
 * @version 1.4
 */
class ArrayExtensions
{
    private $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
    private $session_save_annotations = 'SaveArrayExtensionsAnnotations_';

    /**
     * @var File
     */
    private $cache;


    /**
     * Le constructeur sert à paramétrer le cache d'annotations
     * @param string $cacheDir
     * @param int $lifetime
     */
    public function __construct($cacheDir =  APP_PATH . '/cache/', $lifetime = 172800)
    {
        $frontCache = new Data(['lifetime' => $lifetime]);
        $this->cache = new Memory($frontCache);
    }




    /**
     * Transforme le tableau $arr en objet $object en typant fortement et récursivement les variables qui ont été annotées
     * @param array $arr
     * @param $object mixed
     * @return mixed
     */
    public function toObject(array $arr,  $object)
    {
        $annotations = $this->getClassAnnotations($object);

        foreach($annotations as $annotation)
        {
            if (isset($arr[$annotation['name']]))
            {
                switch( strtolower( $annotation['typage']))
                {
                    case 'string' :   $object->{$annotation['name']} = (string) $arr[$annotation['name']]; break;
                    case 'int' :
                    case 'integer' :   $object->{$annotation['name']} = (int) $arr[$annotation['name']]; break;
                    case 'bool' :
                    case 'boolean' :   $object->{$annotation['name']} = (bool) $arr[$annotation['name']]; break;
                    case 'float'  :   $object->{$annotation['name']} = (float) $arr[$annotation['name']]; break;
                    case 'double'  :   $object->{$annotation['name']} = (double) $arr[$annotation['name']]; break;
                    case 'datetime'  :

                        if ($annotation['format'] == null)
                            throw new Exception('La propriété @var DateTime attend également un attribut @format format_date');


                        $object->{$annotation['name']} = DateTime::createFromFormat(trim($annotation['format']), $arr[$annotation['name']]);

                    break;

                    // tableau d'objets
                    case ( strlen(strstr($annotation['typage'],'[]'))  >  0)  :

                        $objectName = str_replace('[]', '', $annotation['typage']);
                        foreach($arr[$annotation['name']] as $item)
                        {
                            $object->{$annotation['name']}[] =  $this->toObject($item , new $objectName() );
                        }

                        break;

                    // autre objet...
                    default:
                        $object->{$annotation['name']} =  $this->toObject( $arr[$annotation['name']] , new $annotation['typage']() );
                }
            }
        }


        return  (method_exists($object, 'render')) ? $object->render() : $object;
    }


    /**
     * Simple raccourci qui va prendre le POST complet pour tenter de le transformer dans l'objet
     * @param $object
     * @return mixed
     */
    public function postToObject($object)
    {
        return $this->toObject($_POST, $object);
    }



    /**
     * Retourne un tableau avec les annotations contenant les types des variables dans @var
     * @param $class mixed
     * @return array
     */
    private  function getClassAnnotations($class)
    {
        $cacheName = $this->session_save_annotations.get_class($class);


        if ($this->cache->exists($cacheName))
            return  $this->cache->get($cacheName);


        $r = new ReflectionObject($class);
        $pros = $r->getProperties();

        $annotations = array();
        foreach ($pros as $pr)
        {
            preg_match_all($this->pattern, $pr->getDocComment(),$matches, PREG_PATTERN_ORDER );

            $searchAttribute = null;
            $formatAttribute = null;

            foreach($matches[0] as $attribute)
            {
                if (substr($attribute, 0, 4 ) === '@var')
                    $searchAttribute = $attribute;
                else  if (substr($attribute, 0, 7 ) === '@format')
                    $formatAttribute = $attribute;
            }

            if ($searchAttribute != null)
            {
                $t = explode(" ", $searchAttribute);
                $c = ($formatAttribute != null) ?  explode(" ", $formatAttribute) : array();

                if (isset($c[1]))
                {
                    array_shift($c);
                    $c = implode(' ', $c);
                }
                else
                    $c = null;

                if (isset($t[0]) && trim($t[0]) == '@var' ) {
                    $annotations[] = array('name' => $pr->getName(), 'typage' => trim($t[1]), 'format' => $c  );
                }
            }
        }

        $this->cache->save($cacheName, $annotations);

        return $annotations;
    }


    /**
     * Retourne récursivement un objet en tableau, sans cast
     * Méthode récupérée sur la doc PHP
     * @param $object
     * @param bool $assoc
     * @param string $empty
     * @return array
     */
    public  function toArrayRecursive($object, $assoc = true, $empty='')
    {
        $out_arr = array();
        $assoc = (!empty($assoc));

        if (!empty($object)) {
            $arrObj = is_object($object) ? get_object_vars($object) : $object;

            $i=0;
            foreach ($arrObj as $key => $val) {
                $akey = ($assoc !== FALSE) ? $key : $i;
                if (is_array($val) || is_object($val)) {
                    $out_arr[$key] = (empty($val)) ? $empty : $this->toArrayRecursive($val);
                }
                else {
                    $out_arr[$key] = (empty($val)) ? $empty : (string)$val;
                }
                $i++;
            }

        }

        return $out_arr;
    }


}