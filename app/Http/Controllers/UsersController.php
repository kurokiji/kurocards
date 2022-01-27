<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\User;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Faker\Factory as Faker;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rule as ValidationRule;

class UsersController extends Controller
    {
        // Permite registrar un usuario en la base de datos.
        // Recibe un nickname único, un email único, una contraseña que contenta una minúscula, una mayuscula, un número y un caracter especial, y el perfil que debe de ser "private", "professional" o "administrator"
        public function register(Request $request)
        {
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 200;
            $data = $request->getContent();
            try {
                $data = json_decode($data, true);
                $validator = Validator::make(
                    $data,
                    [
                        'nickname' => 'required|unique:App\Models\User,nickname||max:30',
                        'email' => 'required|email:rfc|unique:App\Models\User,email|max:50',
                        'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
                        'profile' => ['required', ValidationRule::in(['private', 'professional', 'administrator'])],
                    ],
                    [
                        'nickname.required' => 'The nickname field is required',
                        'nickname.unique' => 'The nickname entered already exists',
                        'nickname.max' => 'The nickname cannot be longer than 30 characters',
                        'email.required' => 'The email field is required',
                        'email.email' => 'The email field is not in email format',
                        'email.unique' => 'The email entered already exists',
                        'password.required' => 'The email field is required',
                        'password.regex' => 'The password must have an uppercase,a lowercase,a number,a special character and have at least 6 characters.',
                        'profile.in' => 'The profile title must be private,professional or administrator'
                    ]
                );

                if ($validator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$validator->errors()->all());
                    $httpCode = 409;
                } else {
                    $user = new User();
                    $user->nickname = $data['nickname'];
                    $user->email = $data['email'];
                    $user->password = Hash::make($data['password']);
                    $user->save();
                    $answer['msg'] = "User $user->nickname successfull register";
                }
            } catch (\Exception $e) {
                $answer['status'] = 0;
                $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : "There's a server error. Try again.");
                $httpCode = 500;
            }
            return response()->json($answer)->setStatusCode($httpCode);
        }

        // Permite hacer login en el servicio
        // Recibe un nickname y una contraseña que estén registrados en la BD
        // Devuelve un token
        public function login(Request $request)
        {
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 200;
            try {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'nickname' => 'required',
                        'password' => 'required'
                    ],
                    [
                        'nickname.required' => 'The nickname field is required',
                        'password.required' => 'The password is mandatory',
                    ]
                );
                if ($validator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$validator->errors()->all());
                    $httpCode = 409;
                } else {
                    $user = User::where('nickname', $request->nickname)->first();
                    if ($user) {
                        if (Hash::check($request->password, $user->password)) {
                            $token = Hash::make(now() . $user->id);
                            $user->api_token = $token;
                            $user->save();
                            $answer['api_token'] = $token;
                            $answer['msg'] = "Login succesfull";
                        } else {
                            $answer['status'] = 0;
                            $answer['msg'] = "Incorrect password";
                            $httpCode = 409;
                        }
                    } else {
                        $answer['status'] = 0;
                        $answer['msg'] = "There is no user with that nickname";
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

        // Permite recuperar la contraseña de una cuenta existente.
        // Recibe un correo electrónico registrado en la BD.
        // Devuelve una contraseña nueva para ese usuario.
        public function passwordRecover(Request $request)
        {
            $answer = ['status' => 1, 'msg' => ''];
            $httpCode = 200;
            $data = $request->getContent();
            try {
                $validator = Validator::make(
                    $request->all(),
                    ['email' => 'required'],
                    ['email.required' => 'The email field is required']
                );
                if ($validator->fails()) {
                    $answer['status'] = 0;
                    $answer['msg'] = implode(", ",$validator->errors()->all());
                    $httpCode = 409;
                } else {
                    $data = json_decode($data);
                    $user = User::where('email', $request->email)->first();
                    if ($user) {
                        $faker = Faker::create('es_ES');
                        $password = $faker->password;
                        $user->password = Hash::make($password);
                        $answer['password'] = $password;
                        $answer['msg'] = 'Password recover success';
                        $user->save();
                    } else {
                        $answer['status'] = 0;
                        $answer['msg'] = "There is no user with that email";
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
    }
