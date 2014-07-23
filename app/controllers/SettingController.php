<?php

class SettingController extends \BaseController {

    /**
     * user repository
     * 
     * @var \Bausch\Repositories\UserRepositoryInterface
     */
    private $users;

    public function __construct(\Bausch\Repositories\UserRepositoryInterface $users) {
        parent::__construct();

        $this->users = $users;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        return View::make('setting.index');
    }

    /**
     * show change password from
     * 
     * @return Response
     */
    public function changePassword() {
        return View::make('setting.password');
    }

    /**
     * change the user password
     * 
     * @return Response
     */
    public function password() {
        $passwords = array(
            'password' => Input::get('password'),
            'password_new' => Input::get('password_new'),
            'password_new_repeat' => Input::get('password_new_repeat'),
        );

        $rules = array(
            'password' => 'required',
            'password_new' => 'required|min:6',
            'password_new_repeat' => 'required|min:6|same:password_new',
        );

        // Validate the passwords
        $validator = Validator::make($passwords, $rules);

        // Check if the form validates with success.
        if ($validator->passes()) {
            $teh_user = $this->users->findById($this->userid);

            if (Hash::check($passwords['password'], $teh_user->getPassword())) {
                $teh_user->setPassword($passwords['password_new']);
            } else {
                return Redirect::action('SettingController@changePassword')->withErrors(array('password' => 'The provided password is not correct'));
            }

            // update the user
            $teh_user->setPassword($passwords['password_new']);
            $this->users->update($teh_user);

            return Redirect::action('SettingController@index')->with('success', 'Password has been changed');
        }

        // Something went wrong.
        return Redirect::action('SettingController@changePassword')->withErrors($validator);
    }

    /**
     * show form
     * 
     * @return Response
     */
    public function changeEmail() {
        return View::make('setting.email');
    }

    /**
     * change email
     * 
     * @return Response
     */
    public function email() {
        $input = array(
            'email' => Input::get('email'),
        );

        $rules = array(
            'email' => 'required|email',
        );

        // Validate eMail
        $validator = Validator::make($input, $rules);

        if ($validator->passes()) {
            $teh_user = $this->users->findById($this->userid);

            $teh_user->setEmail($input['email']);

            $this->users->update($teh_user);

            return Redirect::action('SettingController@index')->withSuccess('eMail has been updated');
        }

        return Redirect::action('SettingController@changeEmail')->withErrors($validator);
    }

}