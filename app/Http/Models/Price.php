<?php 
 
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model {

	protected $fillable = ['id_object', 'body_price_time', 'created_at'];

	protected $dates = [];

	public static $rules = [

	];

}
