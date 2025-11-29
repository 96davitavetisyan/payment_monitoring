<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity($action, $model, $oldValues = null, $newValues = null, $status = 'success', $errorMessage = null)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->id ?? null,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // Log failure silently to not break the main operation
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}
