<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use App\UserCard;
use Auth;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $card = UserCard::where('user_id', Auth::user()->id)->first();
        // $new_cards = $cards;

        if ($card) {
            // foreach ($cards as $key => $card) {
            //     $response = Curl::to('https://api.flutterwave.com/v3/virtual-cards/'.$card->card_id)
            //     ->withHeader('Content-Type: application/json')
            //     ->withHeader('Authorization: Bearer FLWSECK_TEST-2b3f3862386bce594393f94c261f8184-X')
            //     ->asJson( true )
            //     ->get(); 

            //     $new_cards[$key]->data = $response['data'];
            // }    

            $response = Curl::to('https://api.flutterwave.com/v3/virtual-cards/'.$card->card_id)
                ->withHeader('Content-Type: application/json')
                ->withHeader('Authorization: Bearer FLWSECK_TEST-2b3f3862386bce594393f94c261f8184-X')
                ->asJson( true )
                ->get(); 

            $newCard = (array)$card;
            $newCard['data'] = $response['data'];
            $card = (object)$newCard;
                  
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'success',
            'data' => $card
        ]);       
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = Curl::to('https://api.flutterwave.com/v3/virtual-cards')
            ->withHeader('Content-Type: application/json')    
            ->withHeader('Authorization: Bearer FLWSECK_TEST-2b3f3862386bce594393f94c261f8184-X')    
            ->withData( array( 
                "currency" => $request->currency,
                "amount" => $request->amount,
                "billing_name" => Auth::user()->name
            ) )
            ->asJson( true )
            ->post();

            if ($response['status'] == 'success') {
                $card = UserCard::create([
                    'user_id' => Auth::user()->id,
                    'card_id' => $response['data']['id'],
                    'account_id' => $response['data']['account_id'],
                    'currency' => $response['data']['currency'],
                    'label' => $request->label,
                    'default' => $request->default,
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'success',
                    'data' => $response['data']
                ]);
            }else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response
                ], 406);
            }
               
    }


    /**
     * fund account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fund(Request $request)
    {
        $card = UserCard::where('user_id', Auth::user()->id)->first();
        $response = Curl::to('https://api.flutterwave.com/v3/virtual-cards/'.$card->card_id.'/fund')
            ->withHeader('Content-Type: application/json')
            ->withHeader('Authorization: Bearer FLWSECK_TEST-2b3f3862386bce594393f94c261f8184-X')    
            ->withData( array( 
                "debit_currency" => $request->currency,
                "amount" => $request->amount,
            ) )
            ->asJson( true )
            ->post();

            if ($response['status'] == 'success') {               
                return response()->json([
                    'status' => 'success',
                    'message' => 'success',
                    'data' => $response['data']
                ]);
            }else {
                return response()->json([
                    'status' => 'error',
                    'message' => $response
                ], 406);
            }
               
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
        //
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
