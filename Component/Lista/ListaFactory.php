<?php

namespace Trendix\AdminBundle\Component\Lista;


use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Trendix\AdminBundle\Component\Lista\DataType\JoinType;

class ListaFactory
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var ListaAbstractBuilder
     */
    protected $listaBuilder;

    /**
     * @var array
     */
    protected $arrayResults;

    /**
     * @var array
     */
    protected $labels;

    /**
     * @var int
     */
    protected $pag;
    /**
     * @var integer
     */
    protected $fistResult;

    /**
     * @var integer
     */
    protected $maxResults;

    /**
     * @var ListaAbstractType
     */
    protected $lista;

    /**
     * @var array
     */
    protected $options;

   public function __construct(Container $container, EntityManager $em)
    {
        $this->em = $em;
        $this->container = $container;
        $this->qb = null;
        $this->listaBuilder = new ListaBuilder();
        $this->arrayResults = array();
        $this->lables = array();
        $this->request = Request::createFromGlobals();
        $this->firstResult = 0; //Empezar por el principio
        $this->maxResults = 25; //Mostrar 25 elementos por pagina
        $this->lista = null;
        $this->pag = 1;
        $this->joinList = 1;
        if ($this->request->request->get('epp')) {
            $this->maxResults = $this->request->request->get('epp');
        }

        if ($this->request->request->get('pag')) {
            $this->pag = $this->request->request->get('pag');
            $this->firstResult = ($this->pag-1) * $this->maxResults;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(ListaAbstractType $lista, $options = array())
    {
        $this->lista = $lista;
        $this->options = array_merge($lista->getOptions(), $options);
        return $this->createBuilder($lista, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(ListaAbstractType $lista)
    {
        $lista->buildList($this->listaBuilder, $this->options);
        $this->qb = $this->em->getRepository($lista->getClass())->createQueryBuilder('a');

        $this->selectBuilder(); //Select Fields
        $this->filterBuilder(); //Generate filters
        $this->orderBuilder(); //Order Options
        $this->pagerBuilder(); //Page Options

        //Obtain raw array results
        $arrayResults = $this->qb->getQuery()->getArrayResult();

        //Return data transformed array resutls with label key
        $this->arrayResults = $this->transformerBuilder($arrayResults, $lista);

        return $this;
    }

    /**
     * @return $this
     */
    private function selectBuilder()
    {
        $select = array();
        foreach ($this->listaBuilder->getChilds() as $dataType) {
            if ($dataType instanceof DataAbstractType) {
                if ($dataType->getName() != 'join') {
                    $select[] = 'a.'.$dataType->getKey();
                }
            }
        }

        $this->qb->select($select);

        $this->configureJoins();
        $this->qb->where('1=1');
        return $this;
    }

    private function configureJoins()
    {
        foreach ($this->listaBuilder->getChilds() as $dataType) {
            if ($dataType instanceof JoinType) {
                list($table, $field) = $this->getJoinTableAndField($dataType);
                $this->qb->addSelect($this->getSelectsJoinType($dataType));
                $this->qb->join('a.'.$table, 'a'.$field);
                $this->joinList++;
            }
        }
    }

    /**
     * @param $dataType
     */
    private function getSelectsJoinType($dataType)
    {
        list($table, $field) = $this->getJoinTableAndField($dataType);
        $selector = 'a'.$this->joinList.'.';
        $options = $dataType->getOptions();
        return 'a'.$field.'.'.$field.' AS '.$table.'__'.$field;
    }

    private function countResults()
    {
        $this->qb = $this->em->getRepository($this->lista->getClass())->createQueryBuilder('a');

        $this
            ->countBuilder()
            ->filterBuilder()
        ;

        return $this->qb->getQuery()->getSingleScalarResult();
    }

    private function countBuilder()
    {
        $this->qb->select('COUNT(a.id)');
        return $this;
    }

    private function filterBuilder()
    {
        if (isset($this->options['permanent_filter'])) {
            $this->qb->andWhere($this->options['permanent_filter']);
        }
        return $this;
    }

    private function orderBuilder()
    {
        return $this;
    }

    private function pagerBuilder()
    {
        $this->qb->setFirstResult($this->firstResult);
        $this->qb->setMaxResults($this->maxResults);
        return $this;
    }

    private function transformerBuilder($result, ListaAbstractType $lista)
    {
        $dataTypes = $this->listaBuilder->getChilds();
        $finalResult = array();
        foreach ($result as $line => $row) {
            foreach ($row as $key => $field) {
                $key = str_replace('__', '.', $key);
                $dt = $dataTypes[$key];
                if ($dt instanceof DataAbstractType) {
                    if ($dt->getLabel() == 'Id' || $dt->getLabel() == 'id') {
                        $finalResult[$line]['original_id'] = $dt->getDataTransformer($field, $dt->getOptions());
                    }

                    if ($dt instanceof JoinType) {
                        $finalResult[$line][$dt->getLabel()] = $dt->getDataTransformer($field, $dt->getOptions());
                    }

                    $finalResult[$line][$dt->getLabel()] = $dt->getDataTransformer($field, $dt->getOptions());
                    if (!isset($this->labels[$dt->getLabel()])) {
                        $this->labels[$dt->getLabel()] = $dt->getLabel();
                    }
                }
            }

            $finalResult[$line]['actions'] = $this->getActions($row);
        }

        return $finalResult;
    }

    /**
     * @return null|string
     */
    private function getPrimaryAction()
    {
        $action = $this->listaBuilder->getPrimaryAction();
        return array(
            'action' => $action->getAction(),
            'icon' => $action->getIcon(),
            'url' => $this->getUrlFromAction($action),
            'text' => $action->getText()
        );
    }

    private function getActions($row) {
        $actions = array();
        foreach ($this->listaBuilder->getActions() as $key => $action) {
            if ($key == 'primary') {
                continue;
            }

            $actions[$key] = array(
                'action' => $action->getAction(),
                'icon' => $action->getIcon(),
                'text' => $action->getText(),
                'url' => $this->getUrlFromAction($action, $row)
            );
        }

        return $actions;
    }

    /**
     * @param $action
     * @return null|string
     */
    private function getUrlFromAction($action, $row = array())
    {
        $url = null;
        if ($action == null) {
            return $url;
        }

        $router = $this->container->get('router');
        $options = $action->getOptions();
        if (isset($options['route'])) {
            $params = $this->getUrlParams($options, $row);
            $url = $router->generate($options['route'], $params);
        }

        if (isset($options['url'])) {
            $url = $options['url'];
        }

        return $url;
    }

    private function getUrlParams($options, $row = array())
    {
        $params = $options['route_params'];
        foreach ($options['route_list_params'] as $urlParam => $rowKey) {
            // If the key does not exist, we try using it as a value
            if(isset($row[$rowKey])) {
                $params[$urlParam] = $row[$rowKey];
            } else {
                $params[$urlParam] = $rowKey;
            }
        }

        return $params;
    }

    public function createView()
    {
        return array(
            'data' => $this->arrayResults,
            'labels' => $this->labels,
            'totalResults' => $this->countResults(),
            'currentPage' => $this->pag,
            'totalPage' => intval(ceil($this->countResults()/$this->maxResults)),
            'primaryAction' => $this->getPrimaryAction()
        );
    }

    /**
     * @param $dataType
     * @return array
     */
    private function getJoinTableAndField($dataType)
    {
        $table = explode('.', $dataType->getKey());
        $field = $table[1];
        $table = $table[0];
        return array($table, $field);
    }
}
