<?php


namespace Trendix\AdminBundle\Component\Lista;

class QueryList extends ListaAbstractType
{
    private $sql;
    private $fields;
    private $actions;
    private $downloadable;
    private $searchableFields;

    /**
     * QueryList constructor.
     * @param string $sql
     * @param array|string $fields
     * @param array|string $actions
     * @param bool $downloadable
     * @param array $searchableFields Map indication <alias in select> => <reference in where clause>
     */
    public function __construct($sql, $fields, $actions, $downloadable = false, $searchableFields = array())
    {
        $this->sql = $sql;
        $this->fields = !is_array($fields) ? unserialize(trim($fields)) : $fields;
        $this->actions = !is_array($actions) ? unserialize(trim($actions)) : $actions;
        $this->downloadable = $downloadable;
        $this->searchableFields = $searchableFields;
    }


    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        foreach($this->fields as $field) {
            $name = $field[0];
            $type = isset($field[1]) ? $field[1] : 'text';
            $label = isset($field[2]) ? $field[2] : $name;
            $builder->add($name, $type, array('label' => $label));
        }

        foreach($this->actions as $action) {
            if($action[2]) {
                $options = array('route' => $action[2]);
                if(isset($action[3])) {
                    $options['route_list_params'] = $action[3];
                }
            } else {
                $options['url'] = $action[3];
            }
            $builder->addAction($action[0], $action[1], $options);
        }

        return $this;
    }

    public function getOptions()
    {
        $fields = serialize($this->fields);
        $actions = serialize($this->actions);
        return array(
            'sql' => $this->sql,
            'downloadable' => $this->downloadable,
            'listCall' => $this->sql . '/;/' . $fields . '/;/' . $actions,
            'searchableFields' => $this->searchableFields
            );
    }
}