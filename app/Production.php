<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $table = 'productions';

    protected $fillable = [
        'id_emp', 'production'
    ];

    public function prodWithEmployeeName()
    {
        return $this->belongsTo('App\Employee_names', 'id_emp', 'id_emp');
    }
}
