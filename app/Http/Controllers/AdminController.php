<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Company;
use App\Payment;
use App\Invoice;
use DB;

class AdminController extends Controller
{
    public function index()
    {
    	$cards = [
    		[
    			'title' => 'All Users',
    			'type' => 'value',
    			'value' => User::count()
    		],
    		[
    			'title' => 'All Companies',
    			'type' => 'value',
    			'value' => Company::count()
    		],
    		[
    			'title' => 'Unpaid Invoices',
    			'type' => 'value',
    			'value' => Invoice::where('status', 'sent')->count()
    		],
            [
                'title' => 'Employees',
                'type' => 'value',
                'value' => User::has('companies')->count()
            ],
    		[
    			'title' => 'Admins',
    			'type' => 'value',
    			'value' => User::whereHas('companies', function (Builder $query) {
                    $query->where('admin', '=', 1);
                })->count()
    		],
    		[
    			'title' => 'Paid Invoices',
    			'type' => 'chart',
    			'color' => '#6be6c1',
    			'value' => $this->getChart(Invoice::where('status', 'paid'), 'issue_date')
    		],
    		[
    			'title' => 'Deposited Payments',
    			'type' => 'chart',
    			'color' => '#6be6c1',
    			'value' => $this->getChart(Payment::where('status', 'deposited'), 'payment_date')
    		],
    		[
    			'title' => 'Undeposited Funds',
    			'type' => 'value',
    			'value' => Payment::where('status', 'undeposited')->count()
    		]
    	];

    	return response()
    		->json(['cards' => $cards]);
    }

    public function getChart($model, $column)
    {
    	// $valueFormat = DB::raw("DATE_FORMAT(".$column.", '%d') as value");
    	$valueFormat = DB::raw("strftime( '%d', ".$column.") as value");
    	$start = now()->startOfMonth();
    	$end = now()->endOfMonth();

    	$dates = [];

    	$run = $start->copy();

    	while($run->lte($end)) {
    		$dates = array_add($dates, $run->copy()->format('d'), 0);
    		$run->addDay(1);
    	}

    	$res = $model->groupBy($column)
    		->select(DB::raw('count(*) as total'), $valueFormat)
    		->pluck('total', 'value');

    	$all = $res->toArray() + $dates;

    	ksort($all);

    	return collect(array_values($all))->map(function($item) {
    		return ['value' => $item];
    	});
    }
}
