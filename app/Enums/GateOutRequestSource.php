<?php

namespace App\Enums;

/**
 * Where the gate out request of a certification comes from (T81): SIGLA 1.0, through its API, or the gate out
 * requests that SIGLA 2.0 will have in its own module. The reference of the request is unique only within its source.
 */
enum GateOutRequestSource: string
{
    case Api = 'api';
    case Web = 'web';
}
