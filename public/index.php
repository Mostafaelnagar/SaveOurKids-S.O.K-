<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
$app = new \Slim\App;
//Creating a new app with the config to show errors
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);
/*
End Point :createuser
param :email ,password,.......
Method :Post
*/ 
$app->post('/createuser',function(Request $request,Response $response){
    if(!haveEmptyParameters(array('email', 'password', 'firstName', 'middleName', 'lastName', 'country', 'city', 'phoneNumber', 'birthday', 'gender'), $request, $response)){
        $request_data = $request->getParsedBody(); 
        $email = $request_data['email'];
        $password = $request_data['password'];
        $firstName = $request_data['firstName'];
        $middleName = $request_data['middleName']; 
        $lastName = $request_data['lastName']; 
        $country = $request_data['country']; 
        $city = $request_data['city']; 
        $phoneNumber = $request_data['phoneNumber']; 
        $birthday = $request_data['birthday']; 
        $gender = $request_data['gender']; 
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $db = new DbOperations; 
        
        $result = $db->createUser($email, $hash_password, $firstName,$middleName, $lastName, $country, $city, $phoneNumber, $birthday, $gender);
        
        if($result == USER_CREATED){
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'User created successfully';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});
/*
End Point userlogin
params : email, password
Method :post
*/
$app->post('/userlogin', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody(); 
        $email = $request_data['email'];
        $password = $request_data['password'];
        
        $db = new DbOperations; 
        $result = $db->userLogin($email, $password);
        if($result == USER_AUTHENTICATED){
            
            $user = $db->getUserByEmail($email);
            $response_data = array();
            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user; 
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == USER_NOT_FOUND){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});
/*
End point :newfeed
params :feed_content,
Method :POST
*/

$app->post('/newfeed',function(Request $request,Response $response){
     
    $request_data = $request->getParsedBody(); 
    $feed_content = $request_data['feed_content'];
    $feed_Date = $request_data['feed_Date'];
    $user_Name = $request_data['user_Name'];
    $user_Image = $request_data['user_Image'];
    $files = $request->getUploadedFiles();
    $image = $files['image'];
    $path = 'C:/xampp/htdocs/findKids/uploads/images/feed/'.date("Y-m-d-H-m-s").'.jpg';        
        $db = new DbOperations; 
        if ($image->getError() === UPLOAD_ERR_OK) {
        $result = $db->createFeed($feed_content,$path,$feed_Date,$user_Name,$user_Image);
        
        if($result == USER_CREATED){
            $image->moveTo($path);
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Feed created successfully';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});

/*
End point :addchild
params :child_name,par_id.....
Method :POST
*/

$app->post('/addchild',function(Request $request,Response $response){
     
    $request_data = $request->getParsedBody(); 
    $par_Id = $request_data['par_Id'];
    $child_Name = $request_data['child_Name'];
    $birthday = $request_data['birthday'];
    $gender = $request_data['gender'];
    $files = $request->getUploadedFiles();
    $child_img_url = $files['child_img_url'];
    $child_sec_img = $files['child_sec_img'];
    $path1 = 'C:/xampp/htdocs/findKids/uploads/images/child/'.date("Y-m-d-H-m-s").'.jpg'; 
    $path2 = 'C:/xampp/htdocs/findKids/uploads/images/child/'.$par_Id.'.jpg';          
        $db = new DbOperations; 
        if ($child_img_url->getError() === UPLOAD_ERR_OK) {
        $result = $db->addchild($par_Id,$child_Name,$birthday,$gender,$path1,$path2);
        
        if($result == USER_CREATED){
            $child_img_url->moveTo($path1);
            $child_sec_img->moveTo($path2);
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Feed created successfully';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});

/*
End point :allchildren
params:par_Id
Method ":Post
*/
$app->post('/allchildren', function(Request $request, Response $response){
    $request_data = $request->getParsedBody(); 
    $par_Id = $request_data['par_Id'];
    $db = new DbOperations; 
    $childs = $db->getAllchildren($par_Id);
    $response_data = array();
    $response_data['error'] = false; 
    $response_data['chlidren'] = $childs; 
    $response->write(json_encode($response_data));
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
});
/*
End point :allfeeds
params:--
Method ":GET
*/
$app->get('/allfeeds', function(Request $request, Response $response){
    $db = new DbOperations; 
    $feeds = $db->getAllFeeds();
    $response_data = array();
    $response_data['error'] = false; 
    $response_data['feeds'] = $feeds; 
    $response->write(json_encode($response_data));
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
});

/*
End point :emerengcyPost
params:'longtitude', 'latitude', 'special_Info','emr_Date', 'status','user_Id','child_id'
Method ":post
*/
$app->post('/emerengcyPost',function(Request $request,Response $response){
    if(!haveEmptyParameters(array('longtitude', 'latitude', 'special_Info','emr_Date', 'status','user_Id','child_id'), $request, $response)){
        $request_data = $request->getParsedBody(); 
        $longtitude = $request_data['longtitude'];
        $latitude = $request_data['latitude'];
        $special_Info = $request_data['special_Info'];
        $emr_Date = $request_data['emr_Date']; 
        $status = $request_data['status']; 
        $user_Id = $request_data['user_Id']; 
        $child_id = $request_data['child_id']; 
        
        $db = new DbOperations; 
        
        $result = $db->emerengcyPost($longtitude, $latitude, $special_Info,$emr_Date, $status, $active_People, $user_Id, $child_id);
        
        if($result == USER_CREATED){
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Post created successfully';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }else {
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Error';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});

/*
End point :emerengcyPost
params:'longtitude', 'latitude', 'special_Info','emr_Date', 'status','user_Id','child_id'
Method ":post
*/
$app->post('/activeusers', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('user_longtitude', 'user_latitude','user_Id','emr_Id'), $request, $response)){
        $request_data = $request->getParsedBody(); 
        $longtitude = $request_data['user_longtitude'];
        $latitude = $request_data['user_latitude'];
        $user_Id = $request_data['user_Id'];
        $emr_Id = $request_data['emr_Id'];

        $db = new DbOperations; 
        $result = $db->add_Active_User($longtitude, $latitude,$user_Id,$emr_Id);
        if($result == USER_CREATED){
            $active_Users = $db->getActiveUsers($emr_Id);
            $db->update_Active_Users_Count('`active_People`+1',$emr_Id);
            $response_data = array();
            $response_data['error']=false; 
            $response_data['message'] = 'Added Successfully';
            $response_data['active_Users']=$active_Users; 
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == USER_NOT_FOUND){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});
$app->post('/getactive', function(Request $request, Response $response){
    $request_data = $request->getParsedBody(); 
    $emr_Id = $request_data['emr_id'];
    $db = new DbOperations; 
    $active_Users = $db->getActiveUsers($emr_Id);
    $response_data = array();
    $response_data['error'] = false; 
    $response_data['active_Users'] = $active_Users; 
    $response->write(json_encode($response_data));
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
});

//updating a user
$app->post('/deleteactiveusers', function (Request $request, Response $response) {
    if(!haveEmptyParameters(array('user_Id','emr_Id'), $request, $response)){
       
        $requestData = $request->getParsedBody();

        $user_Id = $requestData['user_Id'];
        $emr_Id = $requestData['emr_Id'];
        $db = new DbOperations();

        $responseData = array();
        $result=$db->delete_Active_User($user_Id);
        if ($$result = 1) {
            $db->update_Active_Users_Count('`active_People`-1',$emr_Id);
            $responseData['error'] = false;
            $responseData['message'] = 'Deleted successfully';
            $response->getBody()->write(json_encode($responseData));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 
        } else {
            $responseData['error'] = true;
            $responseData['message'] = 'InValid User Id,User Location Not Deleted';
            $response->getBody()->write(json_encode($responseData));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 
        }
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200); 
    }
});


//function for check params empty or not
function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 
    
    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }
    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error; 
}

/*
End point :emerengcyPost
params:'longtitude', 'latitude', 'special_Info','emr_Date', 'status','user_Id','child_id'
Method ":post
*/
$app->post('/getemerengcySpecialInfo',function(Request $request,Response $response){
    if(!haveEmptyParameters(array('emr_Id'), $request, $response)){
        $request_data = $request->getParsedBody(); 
        $emr_Id = $request_data['emr_Id'];
        $db = new DbOperations; 
        $result = $db->getspecial_info($emr_Id);
        $response_data = array();
    $response_data['error'] = false; 
    $response_data['emergency_Info'] = $result; 
    $response->write(json_encode($response_data));
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200); 
        
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});
/*
End point :emerengcyPost
params:'longtitude', 'latitude', 'special_Info','emr_Date', 'status','user_Id','child_id'
Method ":post
*/

$app->post('/childLocated',function(Request $request,Response $response){
     
    $request_data = $request->getParsedBody(); 
    $emr_Id = $request_data['emr_Id'];
    $par_Id = $request_data['par_Id'];
        $db = new DbOperations; 
        
        $result = $db->update_Child_Status($emr_Id,$par_Id);
        
        if($result == USER_CREATED){
            $parent_Info = $db->getparent_Info($par_Id);
            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Child Located successfully';
            $message['parent_Info']=$parent_Info; 
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422); 
});
$app->run();