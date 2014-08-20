<?php
define( '_ROOT', dirname(__FILE__) . '');
require_once("models/config.php");
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->config('debug', false);
$app->view(new \Slim\jsonAPI\JsonApiView());


/****** Log In User  *******/
$app->map('/user.login', function () use ($app) {
	
	if(isUserLoggedIn()) {
		$loggedInUser = $_SESSION["userCakeUser"];
		$loggedInUser->userLogOut();
	}
	if ( $app->request->isGet() ) {
		$parameters = $app->request->get();
	} else {
		$parameters =  getParams($app);
	}
	//Forms posted
	if (!empty($parameters)) {
		$errors = array();
		$username = sanitize(trim($parameters["login"]));
		$password = trim($parameters["password"]);
		
		
		//Perform some validation
		//Feel free to edit / change as required
		if($username == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
		}
		if($password == "") {
			$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
		}
	
		if (count($errors) == 0) {
			//A security note here, never tell the user which credential was incorrect
			if(!usernameExists($username)) {
				$app->render(200,array( 'error_code' => 'INVALID_ARGUMENT_LOGIN', 'message' => 'User does not exist.' ));
			} else {
				$userdetails = fetchUserDetails($username);
				//See if the user's account is activated
				if($userdetails["active"]==0) {
					$errors[] = lang("ACCOUNT_INACTIVE");
					$app->render(200,array(
				                'lock_type' => 'EMAIL_NOT_VERIFIED',
				                'locks' => array('EMAIL_NOT_VERIFIED'),
				                'token' => md5(uniqid(mt_rand(), true)),
				                'user_id' => $userdetails["id"],
				        ));
				} else {
					//Hash the password and use the salt from the database to compare the password.
					$entered_pass = generateHash($password,$userdetails["password"]);
					
					if($entered_pass != $userdetails["password"]) {
						//Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
						$app->render(200,array( 'error_code' => 'INVALID_ARGUMENT_LOGIN', 'message' => 'User does not exist.' ));
					} else {
						
						//Passwords match! we're good to go'
						
						//Construct a new logged in user object
						//Transfer some db data to the session object
						$loggedInUser = new loggedInUser();
						$loggedInUser->email = $userdetails["email"];
						$loggedInUser->user_id = $userdetails["id"];
						$loggedInUser->hash_pw = $userdetails["password"];
						$loggedInUser->title = $userdetails["title"];
						$loggedInUser->displayname = $userdetails["display_name"];
						$loggedInUser->username = $userdetails["user_name"];
						$loggedInUser->sign_up_stamp = $userdetails["sign_up_stamp"];
						$loggedInUser->last_sign_in_stamp = $userdetails["last_sign_in_stamp"];
						$loggedInUser->token = md5(uniqid(mt_rand(), true));
						$loggedInUser->active = $userdetails["active"];
						
						//Update last sign in
						$loggedInUser->updateLastSignIn();
						$_SESSION["userCakeUser"] = $loggedInUser;
						$app->render(200,array(
				                'lock_type' => null,
				                'locks' => array(),
				                'token' => $loggedInUser->token,
				                'user_id' => $loggedInUser->user_id,
				        ));
									
					}
				}
			}
		}
	}
})->via('GET', 'POST');


/****** Get User Info  *******/
$app->map('/user.get', function () use ($app){
	$user = array();
	$ip = $_SERVER['REMOTE_ADDR'];
			
	if ( isUserLoggedIn() ) {
		$loggedInUser = $_SESSION["userCakeUser"];		
		array_push($user,array(
			'created_at'=> $loggedInUser->sign_up_stamp,
			'email'=> $loggedInUser->email,
			'email_verified' => $loggedInUser->active,
			'features' => array(),
			'first_name'=> $loggedInUser->username,
			'ip_address' => $ip,
			'last_login_at' => $loggedInUser->last_sign_in_stamp,
			'last_name' => $loggedInUser->username,
			'lock' => null,
			'locks' => array(),
			'login' => $loggedInUser->email,
			'permissions' => array(),
			'properties' => array('porp' => array('value'=>null, 'override'=>false)),
			'subscription' => null,
			'updated_at' => $loggedInUser->sign_up_stamp,
			'user_id' => $loggedInUser->user_id,
		));
	}
	
	$app->render(200,$user);	
})->via('GET', 'POST');

/****** Verfy Email  *******/
$app->map('/user.verifyEmail', function () use ($app){
	if ( $app->request->isGet() ) {
		$parameters = $app->request->get();
	} else {
		$parameters =  getParams($app);
	}
	
	//Get token param
	if(isset($parameters["email_token"])) {	
		$token = $parameters["email_token"];	
		if(!isset($token)) {
			$errors[] = lang("FORGOTPASS_INVALID_TOKEN");
			 //Check for a valid token. Must exist and active must be = 0
		} else if(!validateActivationToken($token)) {
			$errors[] = lang("ACCOUNT_TOKEN_NOT_FOUND");
		} else {
			//Activate the users account
			if(!setUserActive($token)) {
				$errors[] = lang("SQL_ERROR");
			}  
		}
	}
	
	if(count($errors) == 0) {
		$app->render(200,array( 'email_verified' => true ));
	} else {
		$app->render(200,array( 'error_code' => 'INVALID_ARGUMENT_EMAIL_TOKEN', 'message' => 'Invalid email verification token.' ));
	}
})->via('GET', 'POST');


/****** Sign Up User  *******/
$app->map('/user.save', function () use ($app){
	if ( $app->request->isGet() ) {
		$parameters = $app->request->get();
	} else {
		$parameters =  getParams($app);
	}
	
	if(!empty($parameters) && !isUserLoggedIn()) {
		$errors = array();
		$email = trim($parameters["email"]);
		$email_confirm = trim($parameters["login"]);
		$password = trim($parameters["password"]);
		$confirm_pass = trim($parameters["password"]);
		
		if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass)) {
			$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
		} else if($password != $confirm_pass) {
			$errors[] = lang("ACCOUNT_PASS_MISMATCH");
		}
		if(!isValidEmail($email) && !isValidEmail($email_confirm)) {
			$errors[] = lang("ACCOUNT_INVALID_EMAIL");
		}
		
		if($email != $email_confirm) {
			$errors[] = lang("ACCOUNT_EMAIL_MATCH");
		}
		
		//End data validation
		if(count($errors) == 0)
		{
			$us = explode( "@", $email);	
			$username = $us[0];
			$displayname = $username;
			//Construct a user object
			$user = new User($username,$displayname,$password,$email);
			
			//Checking this flag tells us whether there were any errors such as possible data duplication occured
			if(!$user->status) {
				if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));		
			} else {
				//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
				if(!$user->userCakeAddUser()) {
					if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
					if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
				}
			}
		}

		if(count($errors) == 0) {
			$userdetails = fetchUserDetails($email);
			$ip = $_SERVER['REMOTE_ADDR'];
									
			$app->render(200, array(
				'created_at'=> $userdetails["sign_up_stamp"],
				'email'=> $userdetails["email"],
				'email_verified' => $userdetails["active"],
				'features' => array(),
				'first_name'=> $userdetails["user_name"],
				'ip_address' => $ip,
				'last_login_at' => $userdetails["last_sign_in_stamp"],
				'last_name' => $userdetails["user_name"],
				'lock' => null,
				'locks' => array( 0 => array(
								'created_at'=>$userdetails["sign_up_stamp"],
								'issued_by_user_id'=>'1',
								'reason'=>'Email requires verification.',
								'type'=>'EMAIL_NOT_VERIFIED'
							)),
				'login' => $userdetails["email"],
				'permissions' => array(),
				'properties' => array('porp' => array('value'=>null, 'override'=>false)),
				'subscription' => null,
				'updated_at' =>  $userdetails["sign_up_stamp"],
				'user_id' => $userdetails["id"],
			));
		} else {
			$app->render(200,array( 'error_code' => 'INVALID_OPERATION_LOGIN_EXISTS', 'message' => 'User with login ' . $parameters["email"] . ' already exists.' ));
		}
	}
	
})->via('GET', 'POST');

/****** Logout  *******/
$app->map('/user.logout', function () use ($app){
	if(isUserLoggedIn()) {
		$loggedInUser = $_SESSION["userCakeUser"];
		$loggedInUser->userLogOut();
	}	
	$app->render(200,array());	
})->via('GET', 'POST');

/****** Heartbeat to check on User for Frontend  *******/
$app->map('/token.heartbeat', function () use ($app){
	if(isUserLoggedIn()) {
		$app->render(200,array('alive'=>true));
	} else {
		$app->render(200,array('alive'=>false));	
	}
})->via('GET', 'POST');


/****** All The Options routes just need to return 200  *******/
$app->options('/token.heartbeat', function () use ($app){
	cors();
	$app->render(200,array());
});

$app->options('/user.logout', function () use ($app){
	cors();
	$app->render(200,array());
});

$app->options('/user.login', function () use ($app){
	cors();
	$app->render(200,array());
});

$app->options('/user.get', function () use ($app){
	cors();
	$app->render(200,array());
});

$app->options('/user.save', function () use ($app){
	cors();
	$app->render(200,array());
});

$app->options('/user.verifyEmail', function () use ($app){
	cors();
	$app->render(200,array());
});



/****** Run App  *******/
$app->run();


//helper functions
	function getParams ($app) {
		$parameters = array();
		$body_params = json_decode($app->request->getBody());
		if($body_params) {
	        foreach($body_params as $param_name => $param_value) {
	            $parameters[$param_name] = $param_value;
	        }
	    }
		return $parameters;
	}
	
	function cors () {
		if (true) {
			if (isset($_SERVER['HTTP_ORIGIN'])) {
		        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		        // header('Access-Control-Allow-Credentials: true');
		        // header('Access-Control-Max-Age: 0');    // cache for 1 day
		    }
		
		    // Access-Control headers are received during OPTIONS requests
		    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		
		        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		            // header("Access-Control-Allow-Methods: GET, POST, OPTIONS");   
		             header("Access-Control-Allow-Methods: POST");        
		
		        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		            header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
		
		        exit(0);
		    }
		}
	}

	function my_uni ($str) {
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_my_unicode', $str);
	}
			
	function replace_my_unicode($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}
	
	function uncodeback ($str) {
		return str_replace('\\\\', "\\", $str);
	}
	
	function uncode ($str) {
		return str_replace('"', "", $str);
	}	
	
	function myapp_areEqual($a, $b) {
		return ($a->username . "_" .$a->user_id . $a->user_id . $a->user_id == $b);
	}
	
	function myapp_getPostVar($v) {
		return $v;
	}
	
	function myapp_isLoggedIn($u) {
		return $u->id;
	}
	
	function isSetNotEmpty($var) {
		return (isset($var) && !empty($var));	
	}
	
	function checkMyHeaders() {
		//$request = getallheaders();
	    return (isset($_POST["application"]) && $_POST["application"] =="speakniwota");// && 
	    		//isset($request["application"]) && $request["application"] =="speakniwota");
	}
	
	?>
