<?php

use App\Services\ActivityLog;

if (! function_exists('activity')) {
    function activity(): ActivityLog
    {
        return app(ActivityLog::class);
    }
}
