<?php

namespace Trendix\AdminBundle\Component\Lista;


use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
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

    /**
     * @var integer
     */
    protected $joinList;

    /**
     * @var array
     */
    protected $joinListField;

    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $searchableFields;

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
        $this->joinListField = array();
        $this->orderColumn = 'default';
        $this->orderDirection = null;
        $this->sql = null;

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
        // Save original elements per page
        if(array_key_exists('maxResults', $this->options)) {
            $this->maxResults = $this->options['maxResults'];
        }
        // Save original page
        if(array_key_exists('page', $this->options)) {
            $this->pag = $this->options['page'];
            $this->firstResult = ($this->pag-1) * $this->maxResults;
        }
        // Save original order column and direction if not existing already
        if(array_key_exists('order_column', $this->options) && $this->orderColumn == 'default') {
            $this->orderColumn = $this->options['order_column'];
        }
        if(array_key_exists('order_direction', $this->options) && $this->orderDirection === null) {
            $this->orderDirection = $this->options['order_direction'];
        } elseif($this->orderDirection === null) {
            $this->orderDirection = 0;
        }
        // Save SQL code if needed
        if(array_key_exists('sql', $this->options)) {
            $this->sql = $this->options['sql'];
            if($this->orderColumn == 'default') {
                $this->orderColumn = null;
            }
            $this->searchableFields = $this->options['searchableFields'];
        } elseif($this->orderColumn == 'default') {
            $this->orderColumn = 'a.id';
        }
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

        if($this->sql) {
            $arrayResults = $this->getSQLResult(true);
        } else {
            $this->qb = $this->em->getRepository($lista->getClass())->createQueryBuilder('a');
            $this->selectBuilder(); //Select Fields
            $this->filterBuilder(); //Generate filters
            $this->orderBuilder(); //Order Options
            $this->pagerBuilder(); //Page Options
            //Obtain raw array results
            $arrayResults = $this->qb->getQuery()->getArrayResult();
        }

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
        $joinedTables = array();
        foreach ($this->listaBuilder->getChilds() as $dataType) {
            if ($dataType instanceof JoinType or $dataType->getParent() == JoinType::class) {
                list($table, $field) = $this->getJoinTableAndField($dataType);
                $this->qb->addSelect($this->getSelectsJoinType($dataType));
                if (!in_array($table, $joinedTables)) {
                    $this->qb->leftJoin('a.'.$table, 'a'.$this->joinListField[$table]);
                    $joinedTables[] = $table;
                }
            }
        }
    }

    /**
     * @param $dataType
     * @return string
     */
    private function getSelectsJoinType($dataType)
    {
        list($table, $field) = $this->getJoinTableAndField($dataType);
        if (!isset($this->joinListField[$table])) {
            $this->joinListField[$table] = $this->joinList;
            $this->joinList++;
        }

        return 'a'.$this->joinListField[$table].'.'.$field;
    }

    private function countResults()
    {
        if($this->sql) {
            return count($this->getSQLResult(false));
        }
        $this->qb = $this->em->getRepository($this->lista->getClass())->createQueryBuilder('a');

        $this
            ->countBuilder()
            ->filterBuilder()
        ;

        return $this->qb->getQuery()->getArrayResult()[0][1];
    }

    private function countBuilder()
    {
        $this->qb->select('COUNT(a.id)');
        $this->configureJoins();
        return $this;
    }

    private function filterBuilder()
    {
        if (isset($this->options['permanent_filter'])) {
            $this->qb->andWhere($this->options['permanent_filter']);
        }

        // Rellenamos la query para la búsqueda
        $whereClause = '0 = 1';
        if(isset($this->search) && $this->search != '') {
            $fields = $this->em->getClassMetadata($this->lista->getClass())->getFieldNames();

            foreach ($this->listaBuilder->getChilds() as $dataType) {
                if ($dataType instanceof JoinType or $dataType->getParent() == JoinType::class) {
                    list($table, $field) = $this->getJoinTableAndField($dataType);
                    $fields[] = $this->joinListField[$table].'.'.$field;
                }
            }


            foreach($fields as $field) {
                if(strpos($field, '.')) {
                    $alias = 'a'.$field;
                } else {
                    $alias = 'a.' . $field;
                }

                $type = $this->em->getClassMetadata($this->lista->getClass())->getTypeOfField($field);
                if(is_numeric($this->search) && ($type == 'integer' || $type == 'float')) {
                    $whereClause .= ' OR '. $alias . ' = :search_num';
                } else if($type != 'integer' && $type != 'float') {
                    $whereClause .= ' OR '. $alias . ' LIKE :search';
                }
            }

            $this->qb->andWhere($whereClause);
            $this->qb->setParameter('search', '%'.$this->search.'%');
            if(is_numeric($this->search)) {
                $this->qb->setParameter('search_num', floatval($this->search));
            }
        }

        return $this;
    }

    private function orderBuilder()
    {
        $dataTypes = $this->listaBuilder->getChilds();
        $col = $this->orderColumn;
        // We need the complete column for ordering
        foreach ($dataTypes as $key => $dataType) {
            $key2 = $key;
            if (strstr($key, '.')) {
                $key2 = explode('.', $key)[1];
            }
            if ($dataType instanceof JoinType or $dataType->getParent() == JoinType::class && $key2 == $this->orderColumn) {
                list($table, $field) = $this->getJoinTableAndField($dataType);
                $col = 'a'.$this->joinListField[$table].'.'.$field;
            } elseif ($key2 == $this->orderColumn) {
                $col = 'a.' . $this->orderColumn;
            }
        }

        $direction = $this->orderDirection ? 'DESC' : 'ASC';
        $this->qb->orderBy($col, $direction);
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
            foreach ($dataTypes as $key => $dt) {
                if (strstr($key, '.')) {
                    $key = explode('.', $key)[1];
                }
                $field = $row[$key];
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
        //var_dump($this->sql);
        //die();
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
            'downloadable' => isset($this->options['downloadable']) ? $this->options['downloadable'] : false,
            'sql' => $this->sql ? $this->sql : null,
            'listClass' => get_class($this->lista),
            'listCall' => isset($this->options['listCall']) ? $this->options['listCall'] : '',
        );
    }

    /**
     * @return array Data needed to generate an excel file:
     *  'data' => results, 'labels' => labels, 'totalResults' => matches count
     */
    public function getFileData()
    {
        if($this->sql) {
            $arrayResults = $this->getSQLResult(false);
        } else {
            $this->qb = $this->em->getRepository($this->lista->getClass())->createQueryBuilder('a');

            $this->selectBuilder(); //Select Fields
            $this->filterBuilder(); //Generate filters
            $this->orderBuilder(); //Order Options
            //Obtain raw array results
            $arrayResults = $this->qb->getQuery()->getArrayResult();
        }
        //Return data transformed array resutls with label key
        $fileResults = $this->transformerBuilder($arrayResults, $this->lista);
        return array(
            'data' => $fileResults,
            'labels' => $this->labels,
            'totalResults' => $this->countResults(),
        );
    }

    /**
     * @param $dataType
     * @return array
     */
    private function getJoinTableAndField($dataType)
    {
        if($dataType instanceof DataAbstractType) {
            $table = explode('.', $dataType->getKey());
        } else {
            $table = explode('.', $dataType);
        }
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

    /**
     * @param $paged
     * @return array
     */
    private function getSQLResult($paged)
    {
        $sql = $this->sql;

        $rsm = new ResultSetMapping();
        // First, add the select fields:
        foreach ($this->listaBuilder->getChilds() as $dataType) {
            $rsm->addScalarResult($dataType->getKey(), str_replace('.', '__', $dataType->getKey()));
        }

        // Filter if needed
        if(isset($this->search) && $this->search != '') {
            // Use searchable field in the search
            $selectClause = str_replace('SELECT ', '', $sql);
            $selectClause = substr($selectClause, 0, strpos($selectClause, "FROM"));
            $columns = explode(',', $selectClause);
            $sqlFilter = '';
            $i = 0;
            // Then generate the where clause
            foreach ($this->searchableFields as $alias => $expression) {
                if($i > 0) {
                    $sqlFilter .= ' OR ';
                }
                $sqlFilter .= " CAST($expression AS CHAR) LIKE '%$this->search%' ";
                $i++;
            }
            $sqlFilter = '(' . $sqlFilter . ')';

            // If there is an order by clause, put the code before it, if not, put it at the end, always checking
            // if there already is a WHERE clause.
            if(strpos($sql, 'GROUP BY') !== false) {
                $this->putWhereInSQL("GROUP BY", $sql, $sqlFilter);
            } else {
                $this->putWhereInSQL("ORDER BY", $sql, $sqlFilter);
            }
        }

        // Order if needed:
        $column = $this->orderColumn;
        $direction = $this->orderDirection ? 'DESC' : 'ASC';
        if($column) {
            $sql = strpos($sql, "ORDER BY") ? substr($sql, 0, strpos($sql, "ORDER BY")) : $sql;
            $sql .= ' ORDER BY ' . $column . ' ' . $direction;
        }
        // Then, paginate if needed:
        if ($paged) {
            $sql .= ' LIMIT ' . $this->firstResult . ', ' . $this->maxResults;
        }
        $query = $this->em->createNativeQuery($sql, $rsm);
        $arrayResults = $query->getArrayResult();
        return $arrayResults;
    }

    /**
     * @param $clause
     * @param $sql
     * @param $sqlFilter
     */
    private function putWhereInSQL($clause, &$sql, $sqlFilter)
    {
        $parts = explode($clause, $sql);
        if (strpos($sql, 'WHERE') !== false) {
            $parts[0] .= 'AND' . $sqlFilter;
        } else {
            $parts[0] .= 'WHERE' . $sqlFilter;
        }
        if (isset($parts[1])) {
            $parts[0] .= " $clause " . $parts[1];
        }
        $sql = $parts[0];
    }
}
