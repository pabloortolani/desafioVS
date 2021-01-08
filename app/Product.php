<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class Product
 * @package App
 */
class Product extends Model implements JWTSubject
{

    /** @var string */
    protected $table = "products";
    /** @var string */
    protected $primaryKey = "id";

    /** @var bool */
    public $timestamps = true;
    //public const CREATED_AT = 'created_at';
    //public const UPDATED_AT = 'updated_at';

    //Campos que podem ser alterados nas execuções SQL
    /** @var string[] */
    protected $fillable = ['name', 'brand', 'price', 'stock_quantity'];

    //Campos que não podem ser alterados nas execuções SQL
    /** @var array */
    protected $guarded = [];

    /**
     * @param $value
     */
    public function setPriceAttribute($value){
        if(empty($value)){
            $this->attributes['price'] = null;
        }else {
            $this->attributes['price'] = floatval($this->convertStringToDouble($value));
        }
    }

    /**
     * @param $value
     * @return string|null
     */
    public function getPriceAttribute($value){
        if(empty($value)){
            return null;
        }else {
            return number_format($value, 2, ',', '.');
        }
    }

    /**
     * @param $param
     * @return string|string[]|null
     */
    private function convertStringToDouble($param){
        if(empty($param)){
            return null;
        }
        return str_replace(',', '.', str_replace('.', '', $param));
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
