<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

class Users extends REST_Controller {

    private $api_keys = array('apikeyandroid', 'apikeyios');

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // models
        $this->load->model('users_model');
        // end of models

        // Autorization using api_key
        if( !isset($this->_args['api_key']) || ( isset($this->_args['api_key']) && !in_array($this->_args['api_key'], $this->api_keys) ) ){
            $this->response(NULL, REST_Controller::HTTP_UNAUTHORIZED); 
        }
        // end of Autorization using api_key
    }

    public function users_get()
    {
        // Users from a data store e.g. database
        $users = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'fact' => 'Loves coding'],
            ['id' => 2, 'name' => 'Jim', 'email' => 'jim@example.com', 'fact' => 'Developed on CodeIgniter'],
            ['id' => 3, 'name' => 'Jane', 'email' => 'jane@example.com', 'fact' => 'Lives in the USA', ['hobbies' => ['guitar', 'cycling']]],
        ];

        $id = $this->get('id');

        // If the id parameter doesn't exist return all the users

        if ($id === NULL)
        {
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users)
            {
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.

        $user = NULL;

        if (!empty($users))
        {
            foreach ($users as $key => $value)
            {
                if (isset($value['id']) && $value['id'] === $id)
                {
                    $user = $value;
                }
            }
        }

        if (!empty($user))
        {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    public function users_post()
    {
        $access_id = $this->post('access_id');
        $parent_id = $this->post('parent_id');
        $login = $this->post('login');
        $password = $this->post('password');
        $name = $this->post('name');
        $surname = $this->post('surname');
        $middle_name = $this->post('middle_name');
        $icon = $this->post('icon'); // need to be changed in API doc
        $babies = $this->post('babies');
        $cash = $this->post('cash');

        $has_user = false;

        // check user exists
        $get_user = $this->users_model->find( array( 'login' => $login ) );
        if ( is_array($get_user) && count($get_user) > 0 ) {

            $has_user = true;

            $babies = $this->users_model->find( array( 'access_id' => 1 /* 1 is a ученик */ , 'parent_id' => $get_user[0]->id ), array( 'id' ) );

            $message = array(
                'id' => $get_user[0]->id,
                'access_id' => $get_user[0]->access_id,
                'parent_id' => $get_user[0]->parent_id,
                'login' => $get_user[0]->login,
                'name' => $get_user[0]->name,
                'surname' => $get_user[0]->surname,
                'middle_name' => $get_user[0]->middle_name,
                'icon_url' => USERS_ICONS_PATH . $get_user[0]->icon_url,
                'babies' => $babies,
                'cash' => $get_user[0]->cash,
                'message' => 'login already exists',
                'code' => REST_Controller::HTTP_OK
            );
        }
        // end of check user exists

        // if user is not exists, insert user data
        if ( !$has_user ) {

            $icon_url = USERS_ICONS_PATH . 'user_icon.jpg'; // need to make functionality

            $insert_data = array(
                'access_id' => $access_id,
                'parent_id' => $parent_id,
                'login' => $login,
                'password' => $password,
                'name' => $name,
                'surname' => $surname,
                'middle_name' => $middle_name,
                'icon_url' => $icon_url,
                'cash' => $cash
            );

            if ( $user_id = $this->users_model->insert( $insert_data ) ) {

                $get_user = $this->users_model->find( array( 'id' => $user_id ) );

                if ( is_array($get_user) && count($get_user) > 0 ) {
                    
                    $message = array(
                        'id' => $get_user[0]->id,
                        'access_id' => $get_user[0]->access_id,
                        'parent_id' => $get_user[0]->parent_id,
                        'login' => $get_user[0]->login,
                        'name' => $get_user[0]->name,
                        'surname' => $get_user[0]->surname,
                        'middle_name' => $get_user[0]->middle_name,
                        'icon_url' => USERS_ICONS_PATH . $get_user[0]->icon_url,
                        'babies' => $babies,
                        'cash' => $get_user[0]->cash,
                        'message' => 'user has been created successfuly',
                        'code' => REST_Controller::HTTP_CREATED
                    );

                } else {

                    $message = array(
                        'message' => 'user has been created successfuly, but cannot get user data, please try again',
                        'code' => REST_Controller::HTTP_NO_CONTENT
                    );

                }

            } else {

                $message = array(
                    'message' => 'an error occurred, user is not created',
                    'code' => REST_Controller::HTTP_NOT_IMPLEMENTED
                );
                
            }
        }
        // end of if user is not exists, insert user data

        $this->set_response($message, $message['code']);
    }

    public function users_delete()
    {
        $id = (int) $this->get('id');

        // Validate the id.
        if ($id <= 0)
        {
            // Set the response and exit
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];

        $this->set_response($message, REST_Controller::HTTP_NO_CONTENT); // NO_CONTENT (204) being the HTTP response code
    }

}
