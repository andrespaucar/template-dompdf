<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ubigeos;

class Sales extends Model{
    protected $table = 'sales';
    protected $fillable = ['serie','numero','products_sales','total',
                          'cliente_id', 'user_id','state','deleted_at'];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }
    public function customer(){
        // return $this->belongsTo('App\Models\Customers','cliente_id')->with('ubigeo:id,distrito');
        return $this->belongsTo('App\Models\Customers','cliente_id');
    }

    // SCPOPE
    public function scopeSales($query,$year,$month = 'all'){
        if($month == 'all'){
            return $query->select('id','serie','numero',
                        'total','cliente_id','state','deleted_at',
                        'updated_at','created_at')->whereYear('created_at',$year);
        }else{
            return $query->select('id','serie','numero',
                        'total','cliente_id','state','deleted_at',
                        'updated_at','created_at')->whereYear('created_at',$year)->whereMonth('created_at',$month);

        }
        
    }
    public function scopeSalesDatepe($query,$date){
        return $query->select('id','serie','numero','total','cliente_id','state','deleted_at',
                'updated_at','created_at')->whereDate('created_at',$date);
    }
    
    public function scopeSalesAll($query,$serie,$numero){
        return $query->select('id','serie','numero','products_sales',
                    'total','cliente_id','user_id','created_at')
                    ->where('serie','=',$serie)
                    ->where('numero',$numero);
    }

    public function scopeSalesEdit($query, $id){
        return $query->select('id','serie','numero','products_sales',
                    'total','cliente_id','user_id','created_at')
                    ->where('id','=',$id);
    }

    public function scopeUser($query){
        return $query->with(['user:id,name,surname,rol_id']);
    }
    public function scopeCustomer($query){
        return $query->with(['customer:id,doc,business_name,address,document_id']);
    }
    // public function scopeCustomerreport($query){
    //     return $query->with(['customer:id,doc,business_name']);
    // }
    public function scopeCustomerEdit($query){
        return $query->with(['customer:id,doc,business_name,address,document_id,ubigeo_id']);
    }



    // public function scopeUbigeope($query,$id){
    //     return $query->select('id','name')
    //             ->where('id','=',$id);
    // }       
    






                
}


