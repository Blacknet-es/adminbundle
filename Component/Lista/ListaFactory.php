<?php

namespace Trendix\AdminBundle\Component\Lista;


use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Trendix\AdminBundle\Component\Lista\DataType\GalleryType;
use Trendix\AdminBundle\Component\Lista\DataType\JoinManyType;
use Trendix\AdminBundle\Component\Lista\DataType\JoinType;
use Trendix\AdminBundle\Component\Lista\DataType\TextType;

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
     * @var QueryBuilder QueryBuilder use to get the numbers for pagination
     */
    protected $countQb;

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
     * @var boolean
     */
    protected $strictSearch = false;

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

    /**
     * @var array
     */
    protected $specificSearchFields;

    /**
     * @var array Guarda como claves los alias del select, y como valores la cadena utilizada para seleccionar el valor (por ejemplo: a.id)
     * Para columnas normales, el alias será nombreColumna_posicion.
     * Para columnas Join, el alias será aliasTabla_nombreColumna.
     */
    protected $selectAliases;

    public function __construct(Container $container, EntityManager $em)
    {
        $this->em = $em;
        $this->container = $container;
        $this->clean();
    }

    /**
     * {@inheritdoc}
     */
    public function create(ListaAbstractType $lista, $options = array())
    {
        $this->clean();
        $this->lista = $lista;

        $this->options = array_merge($lista->getOptions(), $options);
        if(array_key_exists('strictSearch', $this->options)) {
            $this->strictSearch = $this->options['strictSearch'];
        }
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
        }
        if(array_key_exists('specific_search_fields', $this->options) && $this->specificSearchFields === null) {
            $this->specificSearchFields = $this->options['specific_search_fields'];
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
        if(!empty($this->listaBuilder->getFilters()) && !$this->specificSearchFields) {
            $this->specificSearchFields = $this->listaBuilder->getFilters();
        }

        if($this->sql) {
            $arrayResults = $this->getSQLResult(true);
        } else {
            $this->qb = $this->em->getRepository($lista->getClass())->createQueryBuilder('a');
            $this->countQb = $this->em->getRepository($lista->getClass())->createQueryBuilder('a');
            $this->selectBuilder(); //Select Fields
            $this->filterBuilder(); //Generate filters
            $this->orderBuilder(); //Order Options
            $this->pagerBuilder(); //Page Options
            //Obtain raw array results
            $query = $this->qb->getQuery();
            //var_dump($query->getDQL()); exit;
            // Check if the translatable extension is being used
            if($this->container->hasParameter('translatable')) {
                $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
            }
            $arrayResults = $query->getArrayResult();
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
        $i = 0;
        foreach ($this->listaBuilder->getChilds() as $dataType) {
            if ($dataType instanceof DataAbstractType) {
                if (!$dataType instanceof JoinType && $dataType->getParent() != JoinType::class
                    && !$dataType->isHidden()) {
                    $selectDql = 'a.'.$dataType->getKey();
                    $selectAlias = $dataType->getKey() . '_' . $i;
                    $select[] = $selectDql . ' AS ' . $selectAlias;
                    $this->selectAliases[$selectAlias] = $selectDql;
                }
            }
            $i++;
        }

        $this->qb->select($select);
        $this->qb->groupBy(implode(', ', array_values($this->selectAliases)));
        $this->countQb->select('COUNT(a.id)');

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
                if($dataType instanceof JoinManyType) {
                    $addSelect = $this->getSelectsJoinManyType($dataType);
                } else {
                    $addSelect = $this->getSelectsJoinType($dataType);
                }
                if(!$dataType->isHidden()) {
                    $this->qb->addSelect($addSelect);
                }
                if (!in_array($table, $joinedTables)) {
                    $this->qb->leftJoin('a.'.$table, 'a'.$this->joinListField[$table]);
                    $this->countQb->leftJoin('a.'.$table, 'a'.$this->joinListField[$table]);
                    $joinedTables[] = $table;
                }
            }
        }
    }

    /** Obtiene la parte del select a añadir para mostrar un JoinType
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

        $alias = 'a' . $this->joinListField[$table] . '_' . $field;
        $dql = 'a' . $this->joinListField[$table] . '.' . $field;
        $this->selectAliases[$alias] = $dql;
        return $dql . ' AS ' . $alias;
    }

    /** Obtiene la parte del select a añadir para mostrar un JoinManyType
     * @param JoinManyType $dataType
     * @return string
     */
    private function getSelectsJoinManyType($dataType)
    {
        list($table, $field) = $this->getJoinTableAndField($dataType);
        if (!isset($this->joinListField[$table])) {
            $this->joinListField[$table] = $this->joinList;
            $this->joinList++;
        }

        $alias = 'a' . $this->joinListField[$table] . '_' . $field;
        $dql = $dataType->getDqlTransformer('a' . $this->joinListField[$table], $field);
        $this->selectAliases[$alias] = $dql;
        return $dql . ' AS ' . $alias;
    }

    private function countResults()
    {
        if($this->sql) {
            return count($this->getSQLResult(false));
        }

        $this->countQb->setMaxResults(1);
        return $this->countQb->getQuery()->getArrayResult()[0][1];
    }

    private function countBuilder()
    {
        $this->qb->select('COUNT(a.id)');
        $this->configureJoins();

        $this->qb->addGroupBy('a.id');
        return $this;
    }

    private function buildSpecificQuery($where)
    {
        $fields = [];
        $fieldsValues = [];
        $used = false;

        if (isset($this->specificSearchFields)) {
            $used = true;
            foreach ($this->specificSearchFields as $field => $opt) {
                if($opt['value']) {
                    if (strstr($field, '.') !== false) { // If this is a joined field
                        list($table, $field) = $this->getJoinTableAndField($field);
                        // If the table has not been previously joined, do it now
                        if(!array_key_exists($table, $this->joinListField)) {
                            $this->joinListField[$table] = max($this->joinListField) + 1;
                        }
                        $fields[] = $this->joinListField[$table].'.'.$field;
                        $fieldsValues[$this->joinListField[$table].'.'.$field] = $opt;
                    } else {
                        $fields[] = $field;
                        $fieldsValues[$field] = $opt;
                    }
                }
            }

            $where .= ' AND ( ';
            foreach($fields as $field) {
                if(strpos($field, '.')) {
                    $alias = 'a'.$field;
                } else {
                    $alias = 'a.' . $field;
                }

                $type = $fieldsValues[$field]['type'];
                if(is_numeric($this->search) && ($type == 'integer' || $type == 'float')) {
                    $where .= ' AND '. $alias . ' = ' . $fieldsValues[$field]['value'];
                } else if($type == 'choice') {
                    $where .= ' AND '. $alias . " = '{$fieldsValues[$field]['value']}'";
                } else {
                    $where .= ' AND '. $alias . " LIKE '%{$fieldsValues[$field]['value']}%'";
                }
            }

            $where .= ')';
        }
        return [$used, $where];
    }

    private function filterBuilder()
    {
        // Rellenamos la query para la búsqueda
        if ($this->search != '') {
            [$used, $whereClause] = $this->buildSpecificQuery('0 = 1');
        } else {
            [$used, $whereClause] = $this->buildSpecificQuery('1 = 1');
        }
        if (isset($this->options['permanent_filter'])) {
            $this->qb->andWhere($this->options['permanent_filter']);
            $this->countQb->andWhere($this->options['permanent_filter']);
        }
        if($this->isLooseSearch()) {
            if($used) {
                $whereClause .= ' AND (0 = 1 ';
            }
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
            $this->qb->setParameter('search', '%'.$this->search.'%');
            $this->countQb->setParameter('search', '%'.$this->search.'%');
            if(is_numeric($this->search)) {
                $this->qb->setParameter('search_num', floatval($this->search));
                $this->countQb->setParameter('search_num', floatval($this->search));
            }
            if($used) {
                $whereClause .= ' ) ';
            }
        }

        $this->qb->andWhere($whereClause);
        $this->countQb->andWhere($whereClause);

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
            if (($dataType instanceof JoinType or $dataType->getParent() == JoinType::class) && $key2 == $this->orderColumn) {
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

    /**
     * @param $result
     * @param ListaAbstractType $lista
     * @return array
     * @throws \Exception
     */
    private function transformerBuilder($result, ListaAbstractType $lista)
    {
        $dataTypes = $this->listaBuilder->getChilds();
        $finalResult = array();
        $sqlFields = null;

        // If this is a QueryList, add the fields options:
        if(array_key_exists('sql', $this->options)) {
            $fields = unserialize(explode('/;/', $this->options['listCall'])[1]);
            $sqlFields = array();
            foreach($fields as $field) {
                $sqlFields[$field[0]] = $field;
            }
        }

        $aliases = array_flip($this->selectAliases);

        foreach ($result as $line => $row) {
            foreach ($dataTypes as $key => $dt) {
                //var_dump($key);
                // Obtenemos la key del campo y, si no tiene la tabla incluida, es porque es la del from, con alias 'a',
                // por lo que concatenamos el alias de tabla, y buscamos el alias para ese caso.
                if (strstr($key, '.')) {
                    // si hay un ., hay que sustituir el punto del join por su alias
                    $joinTableName = explode('.', $key);
                    $key = 'a' . $this->joinListField[$joinTableName[0]] . '.' . $joinTableName[1];
                    if($dt instanceof JoinManyType) {
                        $key = $dt->getDqlTransformer('a' . $this->joinListField[$joinTableName[0]], $joinTableName[1]);
                    }
                    $key = $aliases[$key];
                } elseif(!$this->sql && isset($aliases['a.' . $key])) {
                    $key = $aliases['a.' . $key];
                } elseif(!$this->sql && !isset($aliases['a.' . $key])) { // Si no existe la clave, es un campo hidden que nos podemos saltar
                    continue;
                }

                if(isset($row[$key])) {
                    $field = $row[$key];
                    if ($dt instanceof DataAbstractType && !$dt->isHidden()) {
                        $options = $dt->getOptions();
                        if($sqlFields != null && isset($sqlFields[$key]) && isset($sqlFields[$key][3])) {
                            $options = array_merge($options, $sqlFields[$key][3]);
                        }

                        if ($dt->getLabel() == 'Id' || $dt->getLabel() == 'id') {
                            $finalResult[$line]['original_id'] = $dt->getDataTransformer($field, $options);
                            $row['id'] = $dt->getDataTransformer($field, $options);
                        }

                        if ($dt instanceof JoinType or $dt->getParent() == JoinType::class) {
                            $finalResult[$line][$key] = $dt->getDataTransformer($field, $options);
                        }

                        if($dt instanceof TextType && isset($options['translate']) && $options['translate']) {
                            $value = $this->container->get('translator')->trans($field);
                        } else {
                            $value = $field;
                        }

                        $finalResult[$line][$key] = $dt->getDataTransformer($value, $options);
                        if (!isset($this->labels[$key])) {
                            $this->labels[$key] = $dt->getLabel();
                        }
                    }
                } else {
                    $finalResult[$line][$key] = '';
                    if ($dt instanceof DataAbstractType && !$dt->isHidden() && !isset($this->labels[$key])) {
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

    /**
     * @return null|string
     */
    private function getBulkActions()
    {
        $actions = [];
        $bulkActions = $this->listaBuilder->getBulkActions();

        foreach ($bulkActions as $action) {
            if ($action instanceof ActionType) {
                $actions[] =  array(
                    'action' => $action->getAction(),
                    'icon' => $action->getIcon(),
                    'url' => $this->getUrlFromAction($action),
                    'text' => $action->getText(),
                    'advanced' => $action->getView() ? true : false,
                    'view' => $action->getView(),
                    'viewData' => $action->getViewData(),
                );
            }
        }

        return $actions;
    }

    private function getActions($row) {
        $actions = array();
        foreach ($this->listaBuilder->getActions() as $key => $action) {
            if ($key == 'primary') {
                continue;
            }

            $isAdvanced = $action instanceof AdvancedActionType;
            $actions[$key] = array(
                'action' => $action->getAction(),
                'icon' => $action->getIcon(),
                'text' => $action->getText(),
                'url' => $this->getUrlFromAction($action, $row),
                'advanced' => $isAdvanced,
                'view' => $isAdvanced ? $action->getView() : '',
                'viewData' => $isAdvanced ? $action->getViewData() : [],
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
            'bulkActions' => $this->getBulkActions(),
            'search' => $this->search,
            'maxResults' => $this->maxResults,
            'orderColumn' => $this->orderColumn,
            'orderDirection' => $this->orderDirection,
            'downloadable' => isset($this->options['downloadable']) ? $this->options['downloadable'] : false,
            'sql' => $this->sql ? $this->sql : null,
            'listClass' => get_class($this->lista),
            'listCall' => isset($this->options['listCall']) ? $this->options['listCall'] : '',
            'strictSearch' => isset($this->options['strictSearch']) ? $this->options['strictSearch'] : false,
            'specificSearchFields' => $this->specificSearchFields,
            'specificSearchFieldsUsed' => $this->specificSearchFieldsUsed()
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

    /** Para un campo del listado, separa la parte de antes y después del punto para obtener el alias de la tabla y la columna en cuestión
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

    private function buildSpecificSqlQuery($where)
    {
        $fields = [];
        $fieldsValues = [];
        $used = false;

        if (isset($this->specificSearchFields)) {
            $used = true;
            foreach ($this->specificSearchFields as $field => $opt) {
                $fields[] = $field;
                $fieldsValues[$field] = $opt;
            }

            $where .= ' AND ( 1 = 1 ';
            foreach($fields as $field) {
                $type = $fieldsValues[$field]['type'];
                if(is_numeric($this->search) && ($type == 'integer' || $type == 'float')) {
                    $where .= ' AND '. $field . ' = ' . $fieldsValues[$field]['value'];
                } else if($type == 'choice') {
                    $where .= ' AND '. $field . " = '{$fieldsValues[$field]['value']}'";
                } else {
                    $where .= ' AND '. $field . " LIKE '%{$fieldsValues[$field]['value']}%'";
                }
            }

            $where .= ')';
        }
        return [$used, $where];
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
        $sqlFilter = '';
        if($this->specificSearchFields) {
            [$used, $sqlFilter] = $this->buildSpecificSqlQuery('1 = 1 ');
            if($used && isset($this->search) && $this->search != '') {
                $sqlFilter .= ' AND (0 = 1 ';
            } else if (!$used) {
                $sqlFilter .= '(';
            }
        } elseif($this->isLooseSearch()) {
            $used = false;
            $sqlFilter = '(';
        }

        if($this->isLooseSearch() || $this->specificSearchFields) {
            // Use searchable field in the search
            $selectClause = str_replace('SELECT ', '', $sql);
            $selectClause = substr($selectClause, 0, strpos($selectClause, "FROM"));
            $columns = explode(',', $selectClause);
            $i = 0;
            // Then generate the where clause
            foreach ($this->searchableFields as $alias => $expression) {
                if($i > 0) {
                    $sqlFilter .= ' OR ';
                }
                if($this->strictSearch) {
                    $sqlFilter .= " CAST($expression AS CHAR) = '$this->search' ";
                } else {
                    $sqlFilter .= " CAST($expression AS CHAR) LIKE '%$this->search%' ";
                }
                $i++;
            }
            if($this->isLooseSearch()) {
                $sqlFilter = $sqlFilter . ')';
            }

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
            $parts[0] .= ' AND ' . $sqlFilter;
        } else {
            $parts[0] .= ' WHERE ' . $sqlFilter;
        }
        if (isset($parts[1])) {
            $parts[0] .= " $clause " . $parts[1];
        }
        $sql = $parts[0];
    }

    private function clean()
    {
        $this->qb = null;
        $this->listaBuilder = new ListaBuilder();
        $this->arrayResults = array();
        $this->selectAliases = array();
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

        if ($this->request->request->get('specific_search_fields')) {
            $filters = json_decode($this->request->request->get('specific_search_fields'), true);
            $this->specificSearchFields = $filters;
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
     * @return bool
     */
    private function isLooseSearch(): bool
    {
        return isset($this->search) && $this->search != '';
    }

    private function specificSearchFieldsUsed() : bool
    {
        if (isset($this->specificSearchFields)) {
            foreach ($this->specificSearchFields as $field => $opt) {
                if($opt['value']) {
                    return true;
                }
            }
        }
        return false;
    }
}
