<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class OffersController extends Controller
{
    // Publica una oferta en la base datos con el id de una carta, cantidad de esa carta, precio total de esta venta y el id del usuario que esté loggeado
    // Recibe: El id de la carta, la cantidad de cartas y el precio total.
    public function addCardAndOffer(Request $request){
        $answer = ['status' => 1, 'msg' => ''];
        $httpCode = 200;
        $data = $request->getContent();
        try {
            $data = json_decode($data, true);
            $cardValidator = Validator::make(
                $data,
                [
                    'cardId' => 'required|integer|exists:cards,id',
                    'quantity' => 'required|integer|min:1',
                    'totalPrice' => 'required|numeric|min:0.01',
                ],
                [
                    'cardId.required' => 'The card id field is mandatory',
                    'cardId.integer' => 'The card id is not a valid number',
                    'cardId.exists' => 'The card id is not exsits',
                    'quantity.required' => 'The quantity field is mandatory',
                    'quantity.integer' => 'The quantity is not a valid number',
                    'quantity.min' => 'The quantity must be at leat 1',
                    'totalPrice.required' => 'The quantity field is mandatory',
                    'totalPrice.double' => 'The quantity is not a valid number',
                    'totalPrice.min' => 'The quantity must be at leat 1cent (0.01)',
                    ]
                );

                if ($cardValidator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$cardValidator->errors()->all());
                    $httpCode = 409;
                } else {
                    $offer = new Offer();
                    $offer->cardId = $data['cardId'];
                    $offer->userId = $request->loggedUser->id;
                    $offer->quantity = $data['quantity'];
                    $offer->price = $data['totalPrice'];
                    $offer->save();
                    $answer['msg'] = "Offer added";
                    $answer['offer'] = $offer;
                }
            } catch (\Exception $e){
                $answer['status'] = 0;
                $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                $httpCode = 500;
            }
            return response()->json($answer)->setStatusCode($httpCode);
        }

        // Permite realizar una búsqueda de cartas.
        // Recibe un string que se usará como parámetro de búsqueda.
        // Devuelve un listado de cartas que contenga los caracteres enviados en su nombre.
        public function searchCard(Request $request){
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 200;
            $data = $request->getContent();
            try {
                $data = json_decode($data, true);
                $cardValidator = Validator::make(
                    $data,
                    ['name' => 'required'],
                    ['name.required' => 'The card id field is mandatory']
                    );

                    if ($cardValidator->fails()) {
                        $answer['status'] = 0;
                        $answer['msg'] = $cardValidator->errors()->all();
                        $httpCode = 409;
                    } else {
                        $cards = Card::where('name', 'LIKE', '%' .$data['name'] .'%')->get();
                        if(!empty($cards[0])){
                            $answer['cards'] = $cards;
                            $answer['msg'] = "Done";
                        } else {
                            $answer['msg'] = "There's no card with that search parameter";
                        }
                    }
                } catch (\Exception $e){
                    $answer['status'] = 0;
                    $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                    $httpCode = 500;
                }
                return response()->json($answer)->setStatusCode($httpCode);
            }


            // Permite buscar ofertas según un parámetro de búsqueda.
            // Recibe un string que se usará como parámetro de búsqueda.
            // Devuelve una lista de cartas a la venta que contenga los caracteres enviados en su nombre, ordenada de menor a mayor precio.
            // Los campos serán: nombre de la carta, cantidad de cartas de la oferta, precio total de esta y el nickname del usuario que la ofrece.
            public function searchOffer(Request $request){
                $answer = ['status' => 1, 'msg' => ''];
                $httpCode = 200;
                $data = $request->getContent();
                try {
                    $data = json_decode($data, true);
                    $cardValidator = Validator::make(
                        $data,
                        ['name' => 'required'],
                        ['name.required' => 'The card id field is mandatory']
                    );

                    if ($cardValidator->fails()) {
                        $answer['status'] = 0;
                        $answer['msg'] = $cardValidator->errors()->all();
                        $httpCode = 409;
                    } else {
                        if(DB::table('cards')->where('cards.name', 'LIKE', '%' .$data['name'] .'%')->exists()){
                            $offers = DB::table('offers')->selectRaw('cards.name, offers.quantity, offers.price, users.nickname')
                            ->where('cards.name', 'LIKE', '%' .$data['name'] .'%')
                            ->join('cards', 'offers.cardId', '=', 'cards.id')
                            ->join('users', 'offers.userId', '=', 'users.id')
                            ->orderBy('offers.price')
                            ->get();
                            $answer['offers'] = $offers;
                            $answer['msg'] = "Done";
                        } else {
                            $answer['msg'] = "There's no card with that search parameter";
                        }
                    }
                } catch (\Exception $e){
                    $answer['status'] = 0;
                    $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                    $httpCode = 500;
                }
                return response()->json($answer)->setStatusCode($httpCode);
            }
        }
