<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	public function investors()
	{
		return $this->belongsToMany('App\User', 'investors', 'company_id', 'user_id');
	}

	public function employees()
	{
		return $this->belongsToMany('App\User')->withTimestamps()->withPivot('admin');
	}
}
