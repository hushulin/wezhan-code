<?php 
 
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

	protected $fillable = [];

	protected $dates = [];

	public static $rules = [

	];

    public function user() {
        return $this->belongsTo('App\Http\Models\User', 'id_user');
    }

    public function object() {
        return $this->belongsTo('App\Http\Models\Object', 'id_object');
    }

}
