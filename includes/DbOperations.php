<?php 
    
    class DbOperations{
        //the database connection variable
        private $con; 

        //inside constructor
        //we are getting the connection link
        function __construct(){
            require_once dirname(__FILE__) . '/Db_Connect.php';
            $db = new Db_Connect; 
            $this->con = $db->connect(); 
        }


        /*  The Create Operation 
            The function will insert a new user in our database
        */
        public function createUser($email, $password, $firstName,$middleName, $lastName, $country, $city, $phoneNumber, $birthday, $gender){
    
            if(!$this->isEmailExist($email)){
                
                $stmt = $this->con->prepare("INSERT INTO users (email, password, firstName,middleName, lastName,country,city,phoneNumber,birthday,gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssss", $email, $password, $firstName,$middleName, $lastName, $country, $city, $phoneNumber, $birthday, $gender);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }

         /*  The Create Operation 
            The function will insert a new feed in our database
        */
        public function createFeed($feed_content, $path,$feed_date,$user_name,$user_image){
                $stmt = $this->con->prepare("INSERT INTO feed (content,picture,feed_Date,user_Name,user_Image) VALUES(?,?,?,?,?)");
                $stmt->bind_param("sssss",$feed_content, $path,$feed_date,$user_name,$user_imag);
                if($stmt->execute()){
                    echo $user_image;

                    return USER_CREATED; 
                }else{
                   
                    return USER_FAILURE;
                }
           
        }
        /* 
            The Read Operation 
            The function will check if we have the user in database
            and the password matches with the given or not 
            to authenticate the user accordingly    
        */
        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        /*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        /*
            The Read Operation
            Function is returning all the users from database
        */
        public function getAllFeeds(){
            $stmt = $this->con->prepare("SELECT feed_Id,content,picture,feed_Date,user_Name,user_Image FROM feed;");
            $stmt->execute(); 
            $stmt->bind_result($feed_Id, $content , $picture,$feed_Date, $user_Name, $user_Image);
            $feeds = array(); 
            while($stmt->fetch()){ 
                $feed = array(); 
                $feed['feed_Id'] = $feed_Id; 
                $feed['content']=$content; 
                $feed['feed_img'] = $picture; 
                $feed['feed_Date'] = $feed_Date; 
                $feed['user_Name'] = $user_Name; 
                $feed['user_Image'] = $user_Image; 
                array_push($feeds, $feed);
            }             
            return $feeds; 
        }
        /*
            The Read Operation
            Function is returning all the users from database
        */
        public function getAllchildren($par_Id){
            $stmt = $this->con->prepare("SELECT child_id,par_Id,child_Name,birthday, gender ,child_img_url,child_sec_img FROM child where par_Id=?;");
            $stmt->bind_param("s", $par_Id);
            $stmt->execute(); 
            $stmt->bind_result($ch_Id, $par_Id , $child_Name,$birthday, $gender, $child_img_url,$child_sec_img);
            $chlidren = array(); 
            while($stmt->fetch()){ 
                $child = array(); 
                $child['ch_Id'] = $ch_Id; 
                $child['par_Id']=$par_Id; 
                $child['child_Name'] = $child_Name; 
                $child['birthday'] = $birthday; 
                $child['gender'] = $gender; 
                $child['child_img_url'] = $child_img_url;
                $child['child_sec_img'] = $child_sec_img;
                
                array_push($chlidren, $child);
            }             
            return $chlidren; 
        }
/*  The Create Operation 
            The function will insert a add  Child in our database
        */
        public function addchild($par_Id,$child_Name,$birthday,$gender,$path1,$path2){
            $stmt = $this->con->prepare("INSERT INTO child (par_Id,child_Name,birthday,gender,child_img_url,child_sec_img) VALUES(?,?,?,?,?,?)");
            $stmt->bind_param("ssssss",$par_Id,$child_Name,$birthday,$gender,$path1,$path2);
            if($stmt->execute()){
                return USER_CREATED; 
            }else{
               
                return USER_FAILURE;
            }
       
    }
        
        /*
            The Read Operation
            This function reads a specified user from database
        */
        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT user_Id,email,firstName,middleName,lastName,country,city,phoneNumber,birthday,gender FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($user_Id, $email , $firstName, $middleName,$lastName,$country,$city,$phoneNumber,$birthday,$gender);
            $stmt->fetch(); 
            $user = array(); 
            $user['user_id'] = $user_Id; 
            $user['email']=$email; 
            $user['firstName'] = $firstName; 
            $user['middleName'] = $middleName; 
            $user['lastName'] = $lastName; 
            $user['country'] = $country; 
            $user['city'] = $city; 
            $user['phoneNumber'] = $phoneNumber; 
            $user['birthday'] = $birthday; 
            $user['gender'] = $gender; 
            return $user; 
        }


        /*
            The Update Operation
            The function will update an existing user
            from the database 
        */
        public function updateUser($email, $name, $school, $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, name = ?, school = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $name, $school, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        /*
            The Update Operation
            This function will update the password for a specified user
        */
        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $email);
                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;
            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        /*
            The Delete Operation
            This function will delete the user from database
        */
        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        /*
            The Read Operation
            The function is checking if the user exist in the database or not
        */
        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT user_Id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }


        /*  The Create Operation 
            The function will insert a new Emergency in our database
        */
        public function emerengcyPost($longtitude, $latitude, $special_Info,$emr_Date, $status, $active_People, $user_Id, $child_id){
     
            $stmt = $this->con->prepare("INSERT INTO emergency_request (longtitude, latitude, special_Info,emr_Date, status,active_People,user_Id,child_id) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
            $stmt->bind_param("sssssss", $longtitude, $latitude, $special_Info,$emr_Date, $status, $user_Id, $child_id);
            if($stmt->execute()){
                return USER_CREATED; 
            }else{
                return USER_FAILURE;
            }
           
        }

        /*  The Create Operation 
                    The function will insert a new active User in our active_users table
        */
        public function add_Active_User($user_longtitude, $user_latitude,$user_Id,$emr_Id){
            
            $stmt = $this->con->prepare("INSERT INTO active_users (user_longtitude, user_latitude,user_Id,emr_Id) VALUES (?,?, ?,?)");
            $stmt->bind_param("ddii", $user_longtitude, $user_latitude,$user_Id,$emr_Id);
            if($stmt->execute()){
                return USER_CREATED; 
            }else{
                return USER_FAILURE;
                }
            
        }

    /*
            The Read Operation
            This function reads a all Active  Users from database
        */
        public function getActiveUsers($emr_Id){
                    $stmt = $this->con->prepare("SELECT active_Id,user_longtitude,user_latitude,user_Id,emr_Id FROM active_users where emr_Id=?");
                    $stmt->bind_param("i", $emr_Id);
                    $stmt->execute(); 
                    $stmt->bind_result($active_Id, $user_longtitude , $user_latitude,$user_Id,$emr_Id);
                    $active_Users = array();
                    while($stmt->fetch()){ 
                        $active = array(); 
                        $active['active_Id'] = $active_Id; 
                        $active['user_longtitude']=$user_longtitude; 
                        $active['user_latitude'] = $user_latitude; 
                        $active['user_Id'] = $user_Id; 
                        $active['emr_Id'] = $emr_Id; 
                        array_push($active_Users, $active);
                    }   
                    return $active_Users; 
                }

        //Method to delete active user
        public function delete_Active_User($user_Id){
            $stmt = $this->con->prepare("DELETE FROM `active_users`  WHERE user_Id = ?");
            $stmt->bind_param("i", $user_Id);
            $stmt->execute();
            $num_rows = $stmt->num_rows;
            
            if ($num_rows > 0){
                return 1;
            }else{
                return 0;

            }    
        }

        //Method to update active count into Emerngcy Table
        public function update_Active_Users_Count($method,$emr_Id){
            $stmt = $this->con->prepare("UPDATE `emergency_request` SET `active_People`= {$method}  WHERE `emr_Id`=?");
            $stmt->bind_param("i",$emr_Id);
            if ($stmt->execute()){
                return true;
            }else{
                return false;

            }    
        }

        /*
                The Read Operation
                This function reads a get All Emerngcy posts from database
        */
    public function getspecial_info($emr_Id){
        $stmt = $this->con->prepare("SELECT child.child_id,child.child_Name,child.birthday,child.gender ,child.child_img_url,emergency_request.emr_Id,emergency_request.special_Info
        ,emergency_request.emr_Date,emergency_request.status,emergency_request.active_People
        ,emergency_request.user_Id,emergency_request.child_id
        from child,emergency_request
        WHERE child.child_id= emergency_request.child_id
        AND emergency_request.emr_Id=?");
        $stmt->bind_param("i", $emr_Id);
        $stmt->execute(); 
        $stmt->bind_result($emr_Id, $special_Info , $emr_Date,$status,$active_People,$child_id,$user_Id,$child_Name,$birthday, $gender, $child_img_url,$child_id);
        $emergency_Info = array();
        while($stmt->fetch()){ 
            $emr = array(); 
            $emr['emr_Id'] = $emr_Id; 
            $emr['special_Info']=$special_Info; 
            $emr['emr_Date'] = $emr_Date; 
            $emr['status'] = $status; 
            $emr['active_People'] = $active_People; 
            $emr['child_id'] = $child_id; 
            $emr['user_Id'] = $user_Id; 
            $emr['child_Name'] = $child_Name; 
            $emr['birthday'] = $birthday; 
            $emr['gender'] = $gender; 
            $emr['child_img_url'] = $child_img_url;
            array_push($emergency_Info, $emr);
        }   
        return $emergency_Info; 
    }
public function update_Child_Status($emr_Id,$par_Id){

    $stmt = $this->con->prepare("UPDATE `emergency_request` SET `status`= 'Found'  WHERE `emr_Id`=?");
    $stmt->bind_param("i",$emr_Id);
    // $stmt->execute();
    // $num_rows = $stmt->num_rows;
    if ($stmt->execute()){
        return USER_CREATED;
    }else{
        return USER_FAILURE;

    }   
}
public function getparent_Info($user_Id){
    $stmt = $this->con->prepare("SELECT `firstName`,`middleName`,`lastName`,`country`,`city`,`phoneNumber`
    from users where user_Id=?");
    $stmt->bind_param("i", $user_Id);
    $stmt->execute(); 
    $stmt->bind_result($firstName, $middleName , $lastName,$country,$city,$phoneNumber);
    $parent_Info = array();
    while($stmt->fetch()){ 
        $parent = array(); 
        $parent['firstName'] = $firstName; 
        $parent['middleName']=$middleName; 
        $parent['lastName'] = $lastName; 
        $parent['country'] = $country; 
        $parent['city'] = $city; 
        $parent['phoneNumber'] = $phoneNumber; 
        array_push($parent_Info, $parent);
    }   
    return $parent_Info; 
}
}