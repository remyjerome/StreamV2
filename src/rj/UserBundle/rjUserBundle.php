<?php

namespace rj\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class rjUserBundle extends Bundle
{
  public function getParent()
    {
        return 'FOSUserBundle';
    }
}
