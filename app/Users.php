<?php
namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;


use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
   use Authenticatable, Authorizable;

   protected $fillable = ['first_name','last_name','username','email','password','status'];
   protected $hidden = [
   'password',
   'api_key'
   ];
   /*
   * Get All firneds
   *
   */
  protected $redirectTo = '/';

  
   public function getFriends()
   {
        return $this->hasMany(UsersConnect::class, 'user_id', 'id');
   }

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