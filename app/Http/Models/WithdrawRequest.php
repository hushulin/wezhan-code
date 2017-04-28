<?php 
 
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model {

	protected $fillable = [];

	protected $dates = [];

	public static $rules = [

	];

    public function user() {
        return $this->belongsTo('App\Http\Models\User', 'id_user');
    }

}
