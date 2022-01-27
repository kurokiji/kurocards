<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Card;
use App\Models\Relation;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class CardsController extends Controller
{
    // Permite agregar una nueva carta a la BD
    // Recibe: Nombre de la carta, descripción de la carta y coleción de la carta
    // La colección debe de existir para poder registrar dicha carta
    public function addCard(Request $request){
        $answer = ['status' => 1, 'msg' => ''];
        $httpCode = 200;
        $data = $request->getContent();
        try {
            $data = json_decode($data, true);
            // comprobar que existe data
            if ($data != null) {

            }
            $validator = Validator::make(
                $data,
                [
                    'name' => 'required|string',
                    'description' => 'required|string',
                    'collection' => 'required|integer'
                ],
                [
                    'name.required' => 'The name field is mandatory',
                    'name.string' => 'The name field is not a character string',
                    'description.required' => 'The description field is mandatory',
                    'description.string' => 'The description is not a character string',
                    'collection.required' => 'The collection field is mandatory',
                    'collection.integer' => 'The collection is not a character string'
                    ]
                );

                if ($validator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$validator->errors()->all());
                    $httpCode = 409;
                } else {
                    if (Collection::where('id', '=', $request->collection)->exists()){
                        $card = new Card();
                        $card->name = $request["name"];
                        $card->description = $request["description"];
                        $card->save();
                        $relation = new Relation();
                        $relation->cardId = $card->id;
                        $relation->collectionId = $request->collection;
                        $relation->save();
                        $answer['newCard'] = $card;
                        $answer['msg'] = "Card added";

                    } else {
                        $answer['status'] = 0;
                        $answer['msg'] = "The collection doesn't exist, please create it from collections petition";
                        $httpCode = 409;
                    }
                }

            } catch (\Exception $e) {
                $answer['status'] = 0;
                $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                $httpCode = 500;
            }
            return response()->json($answer)->setStatusCode($httpCode);
        }

        // Permite agregar una nueva colección. También permite agregar una carta al mismo tiempo.
        // Recibe: Nombre, imagen y fecha de edición de la colección. Si la carta existe, id de la carta. Si la carta no existe: Nombre y descripción de esta.
        public function addCollection(Request $request){
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 200;
            $data = $request->getContent();
            try {
                $data = json_decode($data, true);
                $collectionValidator = Validator::make(
                    $data,
                    [
                        'name' => 'required|string',
                        'image' => 'required|url',
                        'edition' => 'required|date_format:d/m/Y',
                        'card' => 'required'
                    ],
                    [
                        'name.required' => 'The collection name field is mandatory',
                        'name.string' => 'The collection name field is not a character string',
                        'image.required' => 'The collection description field is mandatory',
                        'image.string' => 'The collection description is not a utl',
                        'edition.required' => 'The collection edition field is mandatory',
                        'edition.date_format' => 'The collection edition does not have a valid date format',
                        'cards.required' => 'The cards field is mandatory'
                        ]
                    );

                    if ($collectionValidator->fails()) {
                        $answer['status'] = 0;
                        $answer['msg'] = implode(", ",$collectionValidator->errors()->all());
                        $httpCode = 409;
                    } else {
                        if (isset($request['card']['id'])) {
                            if (is_int($request['card']['id']) && Card::where('id', '=', $request['card']['id'])->exists()){
                                $card = Card::where('id', '=', $request['card']['id'])->first();
                                $collection = new Collection();
                                $collection->name = $request["name"];
                                $collection->image = $request["image"];
                                $collection->edition = DateTime::createFromFormat('d/m/Y', $request["edition"]);
                                $collection->save();
                                $relation = new Relation();
                                $relation->cardId = $card->id;
                                $relation->collectionId = $collection->id;
                                $relation->save();
                                $answer['existingCard'] = $card;
                                $answer['msg'] = "Collection added";
                                $answer['newCollection'] = $collection;
                            } else {
                                $answer['status'] = 0;
                                $answer['msg'] = "The card doesn't exists, please, create a new card";
                                $httpCode = 409;
                            }
                        } else {
                            $cardValidator = Validator::make(
                                $request['card'],
                                [
                                    'name' => 'required|string',
                                    'description' => 'required|string',
                                ],
                                [
                                    'name.required' => 'The card name field is mandatory',
                                    'name.string' => 'The card name field is not a character string',
                                    'description.required' => 'The card description field is mandatory',
                                    'description.string' => 'The card description field is not a character string',
                                    ]
                                );

                                if ($cardValidator->fails()) {
                                    $answer['status'] = 0;
                                    $answer['msg'] = implode(", ",$cardValidator->errors()->all());
                                    $httpCode = 409;
                                } else {
                                    $card = new Card();
                                    $card->name = $request['card']['name'];
                                    $card->description = $request['card']['description'];
                                    $card->save();

                                    $collection = new Collection();
                                    $collection->name = $request["name"];
                                    $collection->image = $request["image"];
                                    $collection->edition = DateTime::createFromFormat('d/m/Y', $request["edition"]);
                                    $collection->save();

                                    $relation = new Relation();
                                    $relation->cardId = $card->id;
                                    $relation->collectionId = $collection->id;
                                    $relation->save();

                                    $answer['newCard'] = $card;
                                    $answer['msg'] = "Collection added";
                                    $answer['newCollection'] = $collection;
                                }
                            }

                        }
                    } catch (\Exception $e) {
                        $answer['status'] = 0;
                        $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                        $httpCode = 500;
                    }
                    return response()->json($answer)->setStatusCode($httpCode);
                }

        //Permite agregar una carta existente a una colección existente.
        // Recibe id de la carta e id de la colección.
        public function addExistsCardToCollection(Request $request) {
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 500;
            $data = $request->getContent();
            try {
                $data = json_decode($data, true);
                $cardValidator = Validator::make(
                    $data,
                    [
                        'cardId' => 'required|integer|exists:cards,id',
                        'collectionId' => 'required|integer|exists:collections,id',
                    ],
                    [
                        'cardId.required' => 'The card id field is mandatory',
                        'cardId.integer' => 'The card id is not a valid number',
                        'cardId.exists' => 'The card id is not exsits',
                        'collectionId.required' => 'The collection id field is mandatory',
                        'collectionId.integer' => 'The collection id is not a valid number',
                        'collectionId.exists' => 'The collection id is not exsits',
                        ]
                    );

                    if ($cardValidator->fails()) {
                        $answer['status'] = 0;
                        $answer['msg'] = implode(", ",$cardValidator->errors()->all());
                        $httpCode = 409;
                    } else {
                       if(Relation::where('cardId', $data['cardId'])->where('collectionId', $data['collectionId'])->exists()) {
                            $httpCode = 200;
                            $answer['msg'] = "The relation exists.";
                        } else {
                             $card = Card::where('id', $data['cardId'])->first();
                             $collection = Collection::where('id', '=', $data['collectionId'])->first();
                             $relation = new Relation();
                             $relation->cardId = $card->id;
                             $relation->collectionId = $collection->id;
                             $relation->save();

                             $httpCode = 200;
                             $answer['msg'] = "Card \"$card->name\" added to collecion \"$collection->name\"";
                             $answer['collection'] = $collection;
                             $answer['card'] = $card;
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




