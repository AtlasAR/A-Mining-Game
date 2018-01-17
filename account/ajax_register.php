<?php
    include('../includes/config.php');
    include('../structure/database.php');
    include('../structure/user.php');

    $db = new database($db_host, $db_name, $db_user, $db_password);
    $user = new user($db);
    
    if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])){    
        $username = trim($_POST['username'], ' ');
        $password = $_POST['password'];
        $password2= $_POST['password2'];
        $json = array('result' => '', 'success' => false);
        
        if(strlen($username) > 12 || strlen($username) < 3){
            $json['result'] = 'Your username must be at least 3 characters, and no greater than 12 characters.';
        }else if(strlen($password) < 3){
            $json['result'] = 'Your password cannot be less than 3 characters..';
        }else if($password != $password2){
            $json['result'] = 'Your passwords do not match!';
        }else if($user->usernameExists($username)){
            $json['result'] = 'This username already exists!';
        }else{
            //make sure they don't already have an account that is disabled
            //with this ip
            
            $db->processQuery("SELECT * FROM `users` WHERE `ip` = ? AND `disabled` = 1 LIMIT 1", array($_SERVER['REMOTE_ADDR']));
            
            if($db->getRowCount() > 0 || isset($_COOKIE['banned'])){
                $json['result'] = 'You do not have permission to create a new account.';
                setcookie('banned','true',time() + (10 * 365 * 24 * 60 * 60), '/', '.rscharts.com');
            }else{
                if($user->createUser($username,$password)){
                    $json['result'] = 'Congratulations! Your account has successfully been created.';
                    $json['success'] = true;
                }else{
                    $json['result'] = 'Your account could not be registered. Please try again soon!.';
                }
            }
        }
    }else{
        $json['result'] = 'No POST data received.';
    }
    
    echo json_encode($json);
?>