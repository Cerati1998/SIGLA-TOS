<?php

namespace App\Enums;

/**
 * What happened when correcting a lot (H28).
 */
enum SecurityDeviceLotCorrection
{
    /**
     * The data of the lot changed and its serial numbers stayed the same.
     */
    case Updated;

    /**
     * The series changed, so every device of the lot was built again.
     */
    case Regenerated;

    /**
     * Nothing was saved: the series changed while a device of the lot is no longer available.
     */
    case SeriesLocked;

    /**
     * Nothing was saved: a new serial number already exists in another lot.
     */
    case SerialNumbersTaken;
}
