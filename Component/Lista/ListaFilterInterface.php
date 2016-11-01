<?php

namespace Trendix\AdminBundle\Component\Lista;


interface ListaFilterInterface
{
    /**
     * @param $child
     * @param null $type
     * @param array $options
     *
     * @return ListaFilterInterface
     */
    public function add($child, $type = null, array $options = array());
}