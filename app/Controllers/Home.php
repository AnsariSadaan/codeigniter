<?php
namespace App\Controllers;
use App\Models\UserModel;


class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function userdata(){
        $user_model = new UserModel();
        $users = $user_model->findAll();
        return view('userdata', ['users' => $users]);
        // print_r($users);
    }


    public function signup() {
        if(isset($_POST['name'])){
            $user_model = new UserModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT)
            ];
            $result = $user_model->save($data);
            if($result){
                return redirect()->to('/login')->with('success', 'Registration successful! Please log in.');
            }else {
                return redirect()->back()->with('error', 'Failed to register. Please try again.');
            }
        }
        return view('signup');
    }

    public function login() {
        if(isset($_POST['email'])){
            $user_model = new UserModel();
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $user = $user_model->where('email', $email)->first();
            if($user){
                if (password_verify($password, $user->password))    {
                    return redirect()->to('/dashboard')->with('success', 'Login successful!');
                }   else    {
                        return redirect()->back()->with('error', 'Invalid password. Please try again.');
                        }

                }   else {
                            return redirect()->back()->with('error', 'Invalid email. Please try again.');
                        }
            }
                return view('login');
        }


        public function dashboard() {
            return view('dashboard');
        }

        public function logout() {
            
        }

}
