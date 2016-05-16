<?php

namespace Trendix\AdminBundle\Component\Lista;


use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

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
    public function create(ListaAbstractType $lista, array $options = array())
    {
        $this->lista = $lista;
        $this->options = array_merge($lista->getOptions(), $options);
        return $this->createBuilder($lista, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(ListaAbstractType $lista, array $options = array())
    {
        $lista->buildList($this->listaBuilder, $options);
        $this->qb = $this->em->getRepository($lista->getClass())->createQueryBuilder('a');

        $this
            ->selectBuilder() //Select Fields
            ->filterBuilder($options) //Generate filters
            ->orderBuilder($options) //Order Options
            ->pagerBuilder($options) //Page Options
        ;

        //Obtain raw array results
        $arrayResults = $this->qb->getQuery()->getArrayResult();

        //Return data transformed array resutls with label key
        $this->arrayResults = $this->transformerBuilder($arrayResults, $lista, $options);

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
                $select[] = 'a.'.$dataType->getKey();
            }
        }

        $this->qb->select($select);
        $this->qb->where('1=1');
        return $this;
    }

    private function countResults()
    {
        $this->qb = $this->em->getRepository($this->lista->getClass())->createQueryBuilder('a');

        $this
            ->countBuilder()
            ->filterBuilder($this->options)
        ;

        return $this->qb->getQuery()->getSingleScalarResult();
    }

    private function countBuilder()
    {
        $this->qb->select('COUNT(a.id)');
        return $this;
    }

    private function filterBuilder($options)
    {
        if (isset($options['permanent_filter'])) {
            $this->qb->andWhere($options['permanent_filter']);
        }
        return $this;
    }

    private function orderBuilder($options)
    {
        return $this;
    }

    private function pagerBuilder($options)
    {
        $this->qb->setFirstResult($this->firstResult);
        $this->qb->setMaxResults($this->maxResults);
        return $this;
    }

    private function transformerBuilder($result, ListaAbstractType $lista, $options = array())
    {
        $dataTypes = $this->listaBuilder->getChilds();
        $finalResult = array();
        foreach ($result as $line => $row) {
            foreach ($row as $key => $field) {
                $dt = $dataTypes[$key];
                if ($dt instanceof DataAbstractType) {
                    if ($dt->getLabel() == 'Id' || $dt->getLabel() == 'id') {
                        $finalResult[$line]['original_id'] = $dt->getDataTransformer($field, $dt->getOptions());
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
        return $this->getUrlFromAction($action);
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
            $params[$urlParam] = $row[$rowKey];
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
}
