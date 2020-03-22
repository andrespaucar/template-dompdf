<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model {
    protected $table = 'pedidos';
    protected $fillable = ['cantidad','product_id'];

    public function product(){
        return $this->belongsTo('\App\Models\Products')->with(['unit']);
    }

    // COPES 
    public function scopeProducto($query){
        return $query->with(['product:id,name,price,unit_id']);
    }
}