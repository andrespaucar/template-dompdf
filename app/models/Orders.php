<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model{
    protected $table = 'orders';
    protected $fillable = ['pedido','state','user_id','updated_at'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function scopeUser($query){
        return $query->with(['user:id,name,surname']);
    }

    
}