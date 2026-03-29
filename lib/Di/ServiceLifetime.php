<?php

declare(strict_types=1);

namespace RRB\Di;

enum ServiceLifetime: string
{
    case SINGLETON = 'singleton';
    case TRANSIENT = 'transient';
}
