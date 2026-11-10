<?php

namespace App\Services;

enum Sigla1LookupStatus: string
{
    case Found = 'found';
    case NotFound = 'not_found';
    case Unavailable = 'unavailable';
}
