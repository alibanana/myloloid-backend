<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\User;

class UserServiceController extends BaseController
{
    // Get list of all Users
    public function getUsers(){
        $users = User::all();
        return $this->sendResponse($users->toArray(), 'Users retrieved successfully.');
    }

    // show user by ID
    public function getUser($id){
        $user = User::find($id);
        return $this->sendResponse($user->toArray(), 'User retrieved successfully.');
    }
    // Delete user by ID
    public function destroyUser($id){
        $user = User::find($id);
        $user->delete();
        return $this->sendResponse($user->toArray(), 'User deleted successfully.');
    }

    // Create a user
    public function createUser(Request $request){
        $input = $request->all();

        dd($request->all());

        $validation = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'min:10', 'max:13'],
            'is_admin' => ['required'],
            'password' => ['required', 'string', 'min:8'],
            'c_password' => 'required|same:password',
        ]);

        if($validation->fails()){
            return $this->sendError('Validation Error.', $validation->errors());        
        }

        $user = new User([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => bcrypt($input['password']),
            'phone' => $input['phone'],
        ]);

        if ($request->get('is_admin') == "Admin")
        {
            $user->is_admin = 1;
            $user->save();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'Admin created successfully.');
        } else {
            $user->is_admin = 0;
            $user->save(); 
            return $this->sendResponse($user->toArray(), 'User created successfully.');
        }
    }

    // Update a user by ID
    public function updateUser(Request $request, $id){
        $validation = Validator::make ($request->all(), [
            'name'=> 'required|alpha',
            'email'=> 'required|email',
            'phone'=> 'required|min:10|max:13'
        ]);

        if($validation->fails()){
            return $this->sendError('Validation Error.', $validation->errors());        
        }

        $user = User::find($id);
        $user->name = $request->get('name');

        if (!($request->get('email') == $user->email))
        {
            $validation = Validator::make ($request->all(), [
                'email'=> 'unique.users',
            ]);

            if($validation->fails()){
                return $this->sendError('Email has already been used.', $validation->errors());       
            }

            $user->email = $request->get('email');
        }

        $user->phone = $request->get('phone');

        $user->save(); 

        return $this->sendResponse($user->toArray(), 'User Updated successfully.');
    }
}
