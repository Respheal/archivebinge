<?php
class User{

    function __construct(mysqli $conn)
    {
        $this->mysqli = $conn;
    }

    function checkUser($userData = array()){
        $conn = $this->mysqli;
        if(!empty($userData) && (gettype($conn) == "object")){
            $username = $userData['user_name'];
            $claim = $userData['claim'];
            $oauth_id = $userData['oauth_uid'];
            $oauth_provider = $userData['oauth_provider'];
            if($userData['persist'] == "on"){$token = $userData['token'];}
            $date = date('Y-m-d H:i:s');
            mysqli_autocommit($conn, false);
            
            //Check whether user data already exists in database
            $existcheck = "select users.user_id from users left join oauth_ids on users.user_id=oauth_ids.user_id WHERE oauth_ids.oauth_provider = ? and oauth_ids.oauth_uid = ?";
            $stmt = $conn->prepare($existcheck);
            $stmt->bind_param('ss',$oauth_provider, $oauth_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows < 1){
                //Insert user data
                $stmt_user = $conn->prepare("INSERT INTO users (user_name, created, modified, claim_code) VALUES (?, ?, ?, ?)");
                $stmt_user->bind_param('ssss', $username, $date, $date, $claim);
                $stmt_user->execute();
                $uid = $stmt_user->insert_id;
                $stmt_user = $conn->prepare("INSERT INTO oauth_ids (user_id, oauth_provider, oauth_uid) VALUES (?, ?, ?)");
                $stmt_user->bind_param('iss', $uid, $oauth_provider, $oauth_id);
                if($stmt_user->execute()){
                    if(!$conn->commit()){$errors[] = "Commit failed".$stmt_user->error;}
                }else{$conn->rollback();$errors[] = "Transaction failed".$stmt_user->error;}
                $stmt_user->close();
            }

            if(empty($errors)){
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($userData['user_id']);
                $stmt->fetch();
                $user_id = $userData['user_id'];
                if($token){
                    //create a server-side cookie token for the login
                    $stmt_user = $conn->prepare("INSERT INTO user_cookies (user_id, token, created) VALUES (?, ?, ?)");
                    $stmt_user->bind_param('iss', $user_id, $token, $date);
                    $stmt_user->execute();
                    $conn->commit();
                    $stmt_user->close();
                }
            }
            mysqli_autocommit($conn, true);
            $stmt->close();
            $conn->close();
        }
        
        //Return user data
        return array($userData, $errors);
    }
}
?>
