<?php

namespace Trendix\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TrendixAdminBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
