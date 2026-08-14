<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BelongsToCompany
{
    protected static bool $isBootingCompanyScope = false;

    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (static::$isBootingCompanyScope) {
                return;
            }

            if (Auth::check()) {
                static::$isBootingCompanyScope = true;
                try {
                    $user = Auth::user();
                    if ($user) {
                        if ($user->isSuperAdmin()) {
                            // If Super Admin selected a specific company context from company switcher
                            $selectedCompanyId = Session::get('active_company_id');
                            if ($selectedCompanyId) {
                                $builder->where($builder->getModel()->getTable() . '.company_id', $selectedCompanyId);
                            }
                        } else {
                            // Scoped strictly to the user's assigned company
                            $builder->where($builder->getModel()->getTable() . '.company_id', $user->company_id);
                        }
                    }
                } finally {
                    static::$isBootingCompanyScope = false;
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->isSuperAdmin()) {
                    if (empty($model->company_id)) {
                        $selectedCompanyId = Session::get('active_company_id');
                        $model->company_id = $selectedCompanyId ?: Company::first()?->id;
                    }
                } else {
                    // Force company_id to the user's assigned company for non-SuperAdmins
                    $model->company_id = $user->company_id;
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                if (!$user->isSuperAdmin() && $model->isDirty('company_id')) {
                    // Revert company_id change if non-SuperAdmin attempts to mutate it
                    $model->company_id = $model->getOriginal('company_id');
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
