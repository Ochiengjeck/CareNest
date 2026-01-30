<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    protected array $excludeFields = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function created(Model $model): void
    {
        $this->logAudit($model, 'created', [], $this->filterAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $original = $model->getOriginal();
        $changes = $this->filterAttributes($model->getAttributes());
        $originalFiltered = $this->filterAttributes($original);

        $diff = array_diff_assoc($changes, $originalFiltered);
        if (! empty($diff)) {
            $this->logAudit(
                $model,
                'updated',
                array_intersect_key($originalFiltered, $diff),
                array_intersect_key($changes, $diff)
            );
        }
    }

    public function deleted(Model $model): void
    {
        $this->logAudit($model, 'deleted', $this->filterAttributes($model->getAttributes()), []);
    }

    public function restored(Model $model): void
    {
        $this->logAudit($model, 'restored', [], $this->filterAttributes($model->getAttributes()));
    }

    protected function filterAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip($this->excludeFields));
    }

    protected function logAudit(Model $model, string $action, array $oldValues, array $newValues): void
    {
        $user = auth()->user();
        $request = request();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => ! empty($oldValues) ? $oldValues : null,
            'new_values' => ! empty($newValues) ? $newValues : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $this->generateDescription($model, $action),
        ]);
    }

    protected function generateDescription(Model $model, string $action): string
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        return "{$modelName} '{$identifier}' was {$action}";
    }

    protected function getModelIdentifier(Model $model): string
    {
        // Try common identifier patterns
        if (isset($model->name)) {
            return $model->name;
        }

        if (isset($model->title)) {
            return $model->title;
        }

        // For Resident model with first_name and last_name
        if (isset($model->first_name) && isset($model->last_name)) {
            return "{$model->first_name} {$model->last_name}";
        }

        if (method_exists($model, 'getFullNameAttribute')) {
            return $model->full_name;
        }

        return "#{$model->getKey()}";
    }
}
