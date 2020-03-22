<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model{
    protected $table = 'company';
    protected $fillable = ['ruc','razon_social','logo','address','telefono','mail','printpos','ubigeo_id'];
    
    public function ubigeo(){
        return $this->belongsTo('App\Models\Ubigeos');
    }
}