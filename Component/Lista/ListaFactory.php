<?php

namespace Trendix\AdminBundle\Component\Lista;


use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Trendix\AdminBundle\Component\Lista\DataType\GalleryType;
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
    protected $firstResult;

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

    /**
     * @var string
     */
    protected $search;

    /**
     * @var integer
     */
    protected $orderColumn;

    /**
     * @var integer
     */
    protected $orderDirection;

    /**
     * @var string
     */
    protected $toggleField;

   public function __construct(Container $container, EntityManager $em)
    {
        $this->em = $em;
        $this->container = $container;
        $this->qb = null;
        $this->listaBuilder = new ListaBuilder();
        $this->arrayResults = array();
        $this->labels = array();
        $this->request = Request::createFromGlobals();
        $this->firstResult = 0; //Empezar por el principio
        $this->maxResults = 25; //Mostrar 25 elementos por pagina
        $this->lista = null;
        $this->pag = 1;
        $this->joinList = 1;
        $this->orderColumn = 'id';
        $this->orderDirection = 0;

        if ($this->request->request->get('epp')) {
            $this->maxResults = $this->request->request->get('epp');
        }

        if ($this->request->request->get('max')) {
            $this->maxResults = intval($this->request->request->get('max'));
        }

        if ($this->request->request->get('pag')) {
            $this->pag = $this->request->request->get('pag');
            $this->firstResult = ($this->pag-1) * $this->maxResults;
        }

        if ($this->request->request->get('search')) {
            $this->search = addslashes($this->request->request->get('search'));
        }

        if ($this->request->request->get('order_column')) {
            $this->orderColumn = $this->request->request->get('order_column');
        }

        if ($this->request->request->get('order_direction')) {
            $this->orderDirection = $this->request->request->get('order_direction');
        }

        if ($this->request->request->get('update_field')) {
            $this->toggleField = $this->request->request->get('update_field');
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
        // We need to make all modifications of the entities before we get the result, and after the list is initialized
        if($this->toggleField) {
            $this->updateRow($this->toggleField);
        }
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
                if (!$dataType instanceof JoinType && $dataType->getParent() != JoinType::class) {
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
            if ($dataType instanceof JoinType or $dataType->getParent() == JoinType::class) {
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
        if(isset($this->search) && $this->search != '') {
            $fields = $this->em->getClassMetadata($this->lista->getClass())->getFieldNames();
            $orClause = '1=0';
            foreach($fields as $field) {
                $type = $this->em->getClassMetadata($this->lista->getClass())->getTypeOfField($field);
                if($type == 'string') {
                    $orClause .= ' OR a.' . $field . ' LIKE \'%' . $this->search . '%\'';
                } elseif(is_numeric($this->search)) {
                    $orClause .= ' OR a.' . $field . ' = ' . $this->search;
                }
            }
            $this->qb->andWhere($orClause);
        }
        return $this;
    }

    private function orderBuilder()
    {
        $direction = $this->orderDirection ? 'DESC' : 'ASC';
        $this->qb->orderBy('a.'.$this->orderColumn, $direction);
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

                    if ($dt instanceof JoinType or $dt->getParent() == JoinType::class) {
                        $finalResult[$line][$dt->getLabel()] = $dt->getDataTransformer($field, $dt->getOptions());
                    }

                    $finalResult[$line][$dt->getLabel()] = $dt->getDataTransformer($field, $dt->getOptions());
                    if (!isset($this->labels[$key])) {
                        $this->labels[$key] = $dt->getLabel();
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

        if (!$action instanceof ActionType) {
            return array();
        }

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
            'primaryAction' => $this->getPrimaryAction(),
            'search' => $this->search,
            'maxResults' => $this->maxResults,
            'orderColumn' => $this->orderColumn,
            'orderDirection' => $this->orderDirection,
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

    /** Updates and element field
     * @param $values string Comma separated: element id, column position in the list, value to store
     *
     */
    private function updateRow($values)
    {
        list($id, $column, $value) = explode(',', $values);
        $qb = $this->em->getRepository($this->lista->getClass())->createQueryBuilder('a');
        $q = $qb->update($this->lista->getClass(), 'a')
            ->set('a.'.$column, '?1')
            ->where('a.id = ?2')
            ->setParameter(1, $value)
            ->setParameter(2, $id)
            ->getQuery();
        $q->execute();
    }
}
