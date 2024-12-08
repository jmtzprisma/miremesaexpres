<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserKycMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $module = null)
    {
        $modules = gs()->kyc_modules;
        $lastSegment = collect(request()->segments())->last();
        //print_r($lastSegment);exit();

        if (@$modules->user->$module && ($lastSegment != 'video-valid' && $lastSegment != 'save-only-kyc' && $lastSegment != 'waiting-response')) {
            $user = auth()->user();

            if ($user->kv == 0) {
                if(!$user->kyc)
                {
                    $new_pwd = $this->generate_string(20);
                }else{
                    $new_pwd = $user->kyc;

                }
                $resp_r = $this->creaUsuario($user, $new_pwd);
                
                if($resp_r)
                {
                    if(!$user->kyc)
                    {
                        $user->kyc = $new_pwd;
                        $user->save();
                    }

                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();

                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                        }
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    //dd("qweqwe" . $token);

                    session()->put('token_cryptopocket', $token);
                    session()->put('only_kyc', true);
                    $notify[] = ['info', 'Para ser verificado por KYC, proporcione esta información'];
                    $notify[] = ['info', 'Por favor valide su cuenta por video'];
                    
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                }else{
                    
                    $user->kv = 0;
                    $user->past_video_id = $user->video_id;
                    $user->video_id = null;
                    $user->save();
                    
                    $token = $this->nuevoToken($user->email, $user->kyc);
                    if($token == 'login incorrecto')
                    {
                        $new_pwd = $this->generate_string(20);
                        $user->kyc = $new_pwd;
                        $user->save();

                        $identity = $user->identification;
                        if(empty($identity) || is_null($identity))
                        {
                            $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                            return to_route('user.profile.setting')->withNotify($notify);
                        }else{
                            $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                        }
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                    }
                    session()->put('token_cryptopocket', $token);
                    session()->put('only_kyc', true);
                    $notify[] = ['info', 'No fue posible realizar la verificación'];
                    $notify[] = ['info', 'Por favor, verifique sus datos y realice nuevamente el proceso'];
                    
                    return to_route('user.send.money.video_valid')->withNotify($notify);
                    
                }

            }
            if ($user->kv == 2) {
                if(!is_null(auth()->user()->video_id))
                {
                    $response = $this->consultaUser($user->email, $user->video_id);
                            
                    if(isset($response->status) && $response->status == 'success' && $response->kyc_status == 'valid'){
                        
                        $user->kv = 1;
                        $user->save();

                        $notify[] = ['info', 'Validación realizada correctamente.'];
                        return to_route('user.home')->withNotify($notify);
                        
                    }else if(isset($response->status) && $response->status == 'success' && $response->kyc_status == 'fail'){
                        
                        $user->kv = 0;
                        $user->past_video_id = $user->video_id;
                        $user->video_id = null;
                        $user->save();
                        
                        $token = $this->nuevoToken($user->email, $user->kyc);
                        if($token == 'login incorrecto')
                        {
                            $new_pwd = $this->generate_string(20);
                            $user->kyc = $new_pwd;
                            $user->save();

                            $identity = $user->identification;
                            if(empty($identity) || is_null($identity))
                            {
                                $notify[] = ['error', 'Complete sus datos y vuelva a intentarlo por favor.'];
                                return to_route('user.profile.setting')->withNotify($notify);
                            }else{
                                $this->cambiaPwd($user->email, $user->identification, $user->kyc);
                            }
                            
                            $token = $this->nuevoToken($user->email, $user->kyc);
                        }
                        session()->put('token_cryptopocket', $token);
                        session()->put('only_kyc', true);
                        $notify[] = ['info', 'No fue posible realizar la verificación'];
                        $notify[] = ['info', 'Por favor, verifique sus datos y realice nuevamente el proceso'];
                        
                        return to_route('user.send.money.video_valid')->withNotify($notify);
                    }else if(isset($response->status) && $response->status == 'error'){ 
                        $user->kv = 0;
                        $user->past_video_id = $user->video_id;
                        $user->video_id = null;
                        $user->save();
                    }else{
                    
                        $notify[] = ['warning', 'Por favor espere, su identificación esta siendo procesada'];
                        $notify[] = ['info', 'Su video identificación se encuentra en proceso'];
                        return to_route('user.send.money.waiting_response')->withNotify($notify);
                    }
                }
                else{
                    $notify[] = ['warning', 'Por favor espere, su identificación esta siendo procesada'];
                    $notify[] = ['info', 'Su video identificación se encuentra en proceso'];
                    return to_route('user.kyc.data')->withNotify($notify);
                }
            }
        }

        return $next($request);
    }
    
    
    private function consultaUser($email, $videoId){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/check-kyc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "videoId":"' . $videoId . '" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/check-kyc|{
            "email": "' . $email . '",
            "videoId":"' . $videoId . '" 
        } 
        ';
        $log->response = $response;
        $log->save();
        $response = json_decode($response);

        curl_close($curl);
        return $response;
    }

    private function generate_string($strength = 16) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    private function creaUsuario($user, $new_pwd){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/kyc-tlc',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "'. $user->email .'",
            "name": "'. $user->firstname .'",
            "last_name": "'. $user->lastname .'",
            "last_name2": "",
            "phone": "'. $user->mobile .'",
            "country": "' . @$user->address->country .  '",
            "address": "' . @$user->address->address .  '",
            "identification_number": "' . $user->identification . '",
            "password":"'. $new_pwd .'" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        
        $log = new \App\Models\LogKyc;
        $log->user_id = $user->id;
        $log->params = 'https://pasarela.cryptopocket.io/kyc-tlc|{
            "id": "'. $user->id .'",
            "email": "'. $user->email .'",
            "name": "'. $user->firstname .'",
            "last_name": "'. $user->lastname .'",
            "last_name2": "",
            "phone": "'. $user->mobile .'",
            "country": "' . @$user->address->country .  '",
            "address": "' . @$user->address->address .  '",
            "identification_number": "' . $user->identification . '",
            "password":"'. $new_pwd .'" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);

        //dd($response);
        // print_r($response);
        // print_r("<br");

        curl_close($curl);

        return $response->status == 'success' ? true : false;
    }

    private function nuevoToken($email, $password){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/kyc-token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "password":"' . $password . '" 
        } 
        ',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/kyc-token|{
            "email": "' . $email . '",
            "password":"' . $password . '" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        
        curl_close($curl);
        return isset($response->status) ? $response->message : $response->kycToken->authorization;
    }

    private function cambiaPwd($email, $identity, $password){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pasarela.cryptopocket.io/tlc/change-pwd',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "' . $email . '",
            "identification_number":"' . $identity . '",
            "pwd":"' . $password . '" 
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $log = new \App\Models\LogKyc;
        $log->params = 'https://pasarela.cryptopocket.io/tlc/change-pwd|{
            "email": "' . $email . '",
            "identification_number":"' . $identity . '",
            "pwd":"' . $password . '" 
        } 
        ';
        $log->response = $response;
        $log->save();

        $response = json_decode($response);
        //dd($response);
        
        curl_close($curl);
        return true;
    }

}
