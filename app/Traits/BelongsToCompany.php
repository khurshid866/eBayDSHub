<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

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
        });

        static::creating(function ($model) {
            if (empty($model->company_id) && Auth::check()) {
                $user = Auth::user();
                if ($user->isSuperAdmin()) {
                    $selectedCompanyId = Session::get('active_company_id');
                    $model->company_id = $selectedCompanyId ?: Company::first()?->id;
                } else {
                    $model->company_id = $user->company_id;
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
