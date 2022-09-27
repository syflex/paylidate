<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Instandpay = DB::select('select * from migrations');
        return $Instandpay;
       
    }
    public function indexx1($id)
    {

        if($id == 12345){

            DB::insert('insert into withdrawals (user_id, transaction_id, payment_id, bank_id, narration, debit_currency, f_withdrawal_id, status) values (?, ?, ?, ?, ?, ?, ?, ?)', 
            [33, 77, 40, 7, 'Payment for Real Estate Theme', 'NGN', 29655266, true]);
            return 'good';
        }
        else{
            return 'bad';

        }
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
        $user->name =  $request->name;
        $user->email =  $request->name;
        $user->password =  $request->name;
        $user->phone =  $request->name;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'success',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
