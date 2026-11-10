<?php

namespace App\Services;

enum Sigla1LookupStatus
{
    case Found;
    case NotFound;
    case Unavailable;
}
