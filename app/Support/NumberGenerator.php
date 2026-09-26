<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class NumberGenerator
{
    /**
     * Generate a unique number with prefix-YYYYMMDD-NNN format.
     *
     * @param  string  $prefix  The prefix (e.g., 'ORD', 'TRX')
     * @param  string  $modelClass  The model class name (for count query)
     */
    public static function next(string $prefix, string $modelClass): string
    {
        $date = now()->toDateString();
        $count = $modelClass::query()->whereDate('created_at', $date)->count();

        return sprintf('%s-%s-%03d', strtoupper($prefix), now()->format('Ymd'), $count + 1);
    }
}
