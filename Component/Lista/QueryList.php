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
     * @param array|string $fields Cada campo debe tener los siguientes valores: 0 => nombre del campo en la consulta, 1 => tipo de campo, 2 => label (string) o array de opciones (debe incluir el label)
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

    /**
     * @param ListaAbstractBuilder $builder
     * @param array $options
     * @return $this|ListaAbstractType
     */
    public function buildList(ListaAbstractBuilder $builder, array $options)
    {
        foreach($this->fields as $field) {
            $name = $field[0];
            $type = isset($field[1]) ? $field[1] : 'text';
            $fieldOptions = [];
            // Si está definido el tercer parámetro de las opciones
            if(isset($field[2])) {
                // Si es un array, usar como array de opciones
                if(is_array($field[2])) {
                    $fieldOptions = $field[2];
                } else { // Si no, crear array de opciones incluyendo el label
                    $fieldOptions = array('label' => $field[2]);
                }
            } else { // Si no hay label, utilizamos el name del campo
                $fieldOptions = array('label' => $name);
            }
            $builder->add($name, $type, $fieldOptions);
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
            if(isset($action[4])) {
                $builder->addAction($action[0], $action[1], $options, $action[4]);
            } else {
                $builder->addAction($action[0], $action[1], $options);
            }
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
            'fields' => $fields,
            'searchableFields' => $this->searchableFields
            );
    }
}
