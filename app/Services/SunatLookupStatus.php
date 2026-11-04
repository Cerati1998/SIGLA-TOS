<?php

namespace App\Services;

enum SunatLookupStatus
{
    case Found;
    case NotFound;
    case Unavailable;
}
