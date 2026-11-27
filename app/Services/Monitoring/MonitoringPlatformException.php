<?php

namespace App\Services\Monitoring;

use RuntimeException;

/**
 * The monitoring platform did not give what was asked: it did not answer, it rejected the credentials, it does not
 * have the asset or it answered something that cannot be read. The message is the one shown on screen.
 */
class MonitoringPlatformException extends RuntimeException {}
