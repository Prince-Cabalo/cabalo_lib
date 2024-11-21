<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require '../src/vendor/autoload.php';
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,  
    ]
]);


$app->post('/user/register', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $uname = $data->username;
    $pass = $data->password;
    $servername = "localhost";
    $password = "";
    $username = "root";
    $dbname = "cabalo_lib";

    try {
        $conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $uname]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $response->getBody()->write(json_encode(array(
                "status" => "fail",
                "data" => array("Result:" => "Username already exists")
            )));
            return $response;
        }

        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $uname,
            ':password' => hash('sha256', $pass)
        ]);

        $response->getBody()->write(json_encode(array("status" => "success", "data" => null)));

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }
    $conn = null;
    return $response;
});

$app->post('/user/auth', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $username = $data->username; // Use username only for authentication
    $pass = $data->password;
    $servername = "localhost";
    $password = "";
    $dbUsername = "root"; // Changed variable name for clarity
    $dbname = "cabalo_lib";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Only check for username now
        $sql = "SELECT * FROM users WHERE username = :username AND password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => hash('SHA256', $pass)
        ]);
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll();

        if (count($data) == 1) {
            $userid = $data[0]['userid'];
            $key = 'cabalo4B';
            $iat = time();
            $payload = [
                'iss' => 'http://library.org',
                'aud' => 'http://library.com',
                'iat' => $iat,
                'exp' => $iat + 3600,
                'data' => array("userid" => $userid)
            ];
            
            $jwt = JWT::encode($payload, $key, 'HS256');
            
            $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':userid' => $userid]);

            if ($stmt->rowCount() > 0) {
                $sql = "UPDATE used_tokens SET token = :token WHERE userid = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':token' => $jwt,
                    ':userid' => $userid
                ]);
            } else {
                $sql = "INSERT INTO used_tokens (token, userid) VALUES (:token, :userid)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':token' => $jwt,
                    ':userid' => $userid
                ]);
            }

            $response->getBody()->write(
                json_encode(array("status" => "success", "token" => $jwt, "data" => null))
            );
        } else {
            $response->getBody()->write(
                json_encode(array("status" => "fail", "data" => array("title" => "Authentication failed")))
            );
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


$app->post('/forgotpass/{userid}', function (Request $request, Response $response, array $args) {
    $userid = $args['userid'];
    $data = json_decode($request->getBody());
    $newPassword = $data->newPassword;
    $key = 'cabalo4B';
    $jwt = $data->token;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for token validity
        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata) {
            return $response->withJson(array("status" => "no token", "data" => null));
        } elseif ($userdata['token'] != $jwt) {
            return $response->withJson(array("status" => "invalid token", "data" => null));
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user's password
            $sql = "UPDATE users SET password = :password WHERE userid = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['password' => $hashedPassword, 'userid' => $userid]);

            // Generate a new token
            $iat = time();
            $payload = [
                'iss' => 'http://library.org',
                'aud' => 'http://library.com',
                'iat' => $iat,
                'exp' => $iat + 3600,
                'data' => array("userid" => $userid)
            ];
            $newJwt = JWT::encode($payload, $key, 'HS256');

            // Update the token in the database
            $sql = "UPDATE used_tokens SET token = :token WHERE userid = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['token' => $newJwt, 'userid' => $userid]);

            return $response->withJson(array("status" => "success", "data" => "Password updated successfully.", "newToken" => $newJwt));
        }
    } catch (PDOException $e) {
        return $response->withJson(array("status" => "fail", "data" => array("title" => $e->getMessage())));
    } catch (Exception $e) {
        return $response->withJson(array("status" => "fail", "data" => array("title" => "Token Expired, Please Relogin")));
    } finally {
        $conn = null;
    }
});



$app->put('/user/update', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $newUsername = $data->new_username;
    $newPassword = $data->new_password;
    $jwt = $data->token;
    $userid = $data->userid;
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "cabalo_lib";
    $key = 'cabalo4B';

    try {
        
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata){
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        } else
        if ($userdata ['token'] != $jwt){
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        }else{
        
        $sql = "UPDATE users SET username = :username, password = :password WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'username' => $newUsername,
            'password' => hash('sha256', $newPassword),
            'userid' => $userid
        ]);

        if ($stmt->rowCount() > 0) {
            $key='cabalo4B';
                $iat=time();
                $payload=[
                    'iss'=> 'http://library.org',
                    'aud'=>'http://library.com',
                    'iat'=> $iat, 
                    'exp'=> $iat + 3600,
                    'data'=>array(
                        "userid"=>$userid)
                ];

                $jwt=JWT::encode($payload, $key, 'HS256');
                $sql = "UPDATE used_tokens SET token = :token  WHERE userid = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'token' => $jwt,
                    'userid'=> $userid   
            ]);
            $response->getBody()->write(json_encode(array("status" => "success", "data" => "User updated successfully","newToken" =>$jwt)));
        } else {
            $response->getBody()->write(json_encode(array("status" => "fail", "data" => "No changes made")));
        }}
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid Token, Please Login Again"))));
    }

    $conn = null;
    return $response;
});

$app->delete('/user/delete', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $jwt = $data->token;
    $userid = $data->userid;
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "cabalo_lib";
    $key = 'cabalo4B';

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if the token is valid for the given userid
        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata) {
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        } elseif ($userdata['token'] != $jwt) {
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        } else {
            // Delete the user
            $sql = "DELETE FROM users WHERE userid = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['userid' => $userid]);

            if ($stmt->rowCount() > 0) {
                $response->getBody()->write(json_encode(array("status" => "success", "data" => "User deleted successfully")));
            } else {
                $response->getBody()->write(json_encode(array("status" => "fail", "data" => "No user found to delete")));
            }
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Invalid Token, Please Login Again"))));
    }

    $conn = null;
    return $response;
});


$app->get('/display/users', function (Request $request, Response $response, array $args) {
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $dbname = "cabalo_lib";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT userid, username FROM users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            $response->getBody()->write(json_encode(array("status" => "success", "data" => $users)));
        } else {
            $response->getBody()->write(json_encode(array("status" => "fail", "data" => "No users found.")));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


$app->post('/add/books', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody());
    $author = $data->author;
    $title = $data->title;
    $jwt = $data->token;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";
    $key = 'cabalo4B'; // JWT Secret key

    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate token (without userid)
        $payload = JWT::decode($jwt, new Key($key, 'HS256'));
        $userid = $payload->data->userid; // Extract userid from the token payload if needed

        // Check if the token is already used
        $sql = "SELECT * FROM used_tokens WHERE token = :token";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['token' => $jwt]);
        $tokenRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenRecord) {
            return $response->withJson(array("status" => "fail", "data" => "Invalid or already used token."));
        }

        // Token is valid, proceed to add the book

        // Check if the author exists
        $sql = "SELECT authorid FROM author WHERE name = :author";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['author' => $author]);
        $existingAuthor = $stmt->fetch(PDO::FETCH_ASSOC);

        // Insert author if it doesn't exist
        if (!$existingAuthor) {
            $sql = "INSERT INTO author (name) VALUES (:author)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['author' => $author]);
            $authorid = $conn->lastInsertId();
        } else {
            $authorid = $existingAuthor['authorid'];
        }

        // Check if a book with the same title and author already exists
        $sql = "SELECT COUNT(*) FROM books WHERE title = :title AND authorid = :authorid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['title' => $title, 'authorid' => $authorid]);
        $existingBookCount = $stmt->fetchColumn();

        if ($existingBookCount > 0) {
            return $response->withJson(array("status" => "fail", "data" => "Book with the same title and author already exists."));
        } else {
            // Insert the new book
            $sql = "INSERT INTO books (title, authorid) VALUES (:title, :authorid)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['title' => $title, 'authorid' => $authorid]);

            // Mark the token as used (delete it or invalidate it)
            $sql = "DELETE FROM used_tokens WHERE token = :token";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['token' => $jwt]);

            // Generate a new JWT token
            $iat = time();
            $payload = [
                'iss' => 'http://library.org',
                'aud' => 'http://library.com',
                'iat' => $iat,
                'exp' => $iat + 3600, // Token expiry in 30 minutes
                'data' => array(
                    "userid" => $userid // Keep userid if you still need it in the token
                )
            ];
            $newJwt = JWT::encode($payload, $key, 'HS256');

            // Insert the new token into the `used_tokens` table
            $sql = "INSERT INTO used_tokens (userid, token) VALUES (:userid, :token)";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['userid' => $userid, 'token' => $newJwt]);

            // Return success response with the new token
            return $response->withJson(array("status" => "success", "data" => "Book added successfully.", "newToken" => $newJwt));
        }
    } catch (PDOException $e) {
        return $response->withJson(array("status" => "fail", "data" => array("title" => $e->getMessage())));
    } catch (Exception $e) {
        return $response->withJson(array("status" => "fail", "data" => array("title" => "Token Expired, Please Relogin")));
    } finally {
        $conn = null;
    }
});




$app->get('/display/author', function (Request $request, Response $response, array $args) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";

    $data = json_decode($request->getBody());
    $userid = $data->userid;
    $key = 'cabalo4B';
    $jwt = $data->token;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata) {
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        } elseif ($userdata['token'] != $jwt) {
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        } else {
            $sql = "SELECT * FROM author";
            $stmt = $conn->query($sql);
            $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $iat = time();
            $payload = [
                'iss' => 'http://library.org',
                'aud' => 'http://library.com',
                'iat' => $iat,
                'exp' => $iat + 3600, // Token expires in 30 minutes
                'data' => array("userid" => $userid)
            ];

            $newJwt = JWT::encode($payload, $key, 'HS256');
            $sql = "UPDATE used_tokens SET token = :token WHERE userid = :userid";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['token' => $newJwt, 'userid' => $userid]);

            $response->getBody()->write(json_encode(array("status" => "success", "data" => $authors, "newToken" => $newJwt)));
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("message" => $e->getMessage()))));
    }

    $conn = null;
    return $response;
});


$app->put('/update/books/{bookid}', function (Request $request, Response $response, array $args) {
    $bookid = $args['bookid'];
    $data = json_decode($request->getBody());
    $title = $data->title;
    $author = $data->author;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";
    $userid = $data->userid;
    $key = 'cabalo4B';
    $jwt = $data->token;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata) {
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        } elseif ($userdata['token'] != $jwt) {
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        } else {
            try {
                $sql = "SELECT authorid FROM author WHERE name = :author";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['author' => $author]);
                $existingAuthor = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingAuthor) {
                    $sql = "INSERT INTO author (name) VALUES (:author)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['author' => $author]);
                    $authorid = $conn->lastInsertId();  
                } else {
                    $authorid = $existingAuthor['authorid'];  
                }

                $sql = "UPDATE books SET title = :title, authorid = :authorid WHERE bookid = :bookid";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'title' => $title,
                    'authorid' => $authorid,
                    'bookid' => $bookid
                ]);

                $iat = time();
                $payload = [
                    'iss' => 'http://library.org',
                    'aud' => 'http://library.com',
                    'iat' => $iat,
                    'exp' => $iat + 3600,
                    'data' => array("userid" => $userid)
                ];

                $jwt = JWT::encode($payload, $key, 'HS256');
                $sql = "UPDATE used_tokens SET token = :token WHERE userid = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'token' => $jwt,
                    'userid' => $userid   
                ]);

                $response->getBody()->write(json_encode(array("status" => "success", "data" => null, "newToken" => $jwt)));
            } catch (PDOException $e) {
                $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
            }
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Token Expired, Please Relogin"))));
    }

    $conn = null;
    return $response;
});

$app->put('/update/author/{authorid}', function (Request $request, Response $response, array $args) {
    $authorid = $args['authorid'];
    $data = json_decode($request->getBody());
    $name = $data->name;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";
    $userid = $data->userid;
    $key = 'cabalo4B';
    $jwt = $data->token;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userdata) {
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        } elseif ($userdata['token'] != $jwt) {
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        } else {
            try {
                $sql = "UPDATE author SET name = :name WHERE authorid = :authorid";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['name' => $name, 'authorid' => $authorid]);

                $iat = time();
                $payload = [
                    'iss' => 'http://library.org',
                    'aud' => 'http://library.com',
                    'iat' => $iat, 
                    'exp' => $iat + 3600,
                    'data' => array("userid" => $userid)
                ];

                $jwt = JWT::encode($payload, $key, 'HS256');
                $sql = "UPDATE used_tokens SET token = :token WHERE userid = :userid";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'token' => $jwt,
                    'userid' => $userid   
                ]);

                $response->getBody()->write(json_encode(array("status" => "success", "data" => null, "newToken" => $jwt)));
            } catch (PDOException $e) {
                $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => $e->getMessage()))));
            }
        }
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(array("status" => "fail", "data" => array("title" => "Token Expired, Please Relogin"))));
    }

    $conn = null;
    return $response;
});


$app->delete('/delete/books/{bookid}', function (Request $request, Response $response, array $args) {
    $bookid = $args['bookid'];
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";
    $key ='cabalo4B';
    $data=json_decode($request->getBody());
    $jwt=$data->token;
    $userid = $data->userid;
    try{
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
            $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userdata){
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        }
        if ($userdata ['token'] != $jwt){
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        }else{
        
    try {
       
        $sql = "DELETE FROM books WHERE bookid = :bookid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['bookid' => $bookid]);

        $iat=time();
        $payload=[
            'iss'=> 'http://library.org',
            'aud'=>'http://library.com',
            'iat'=> $iat, 
            'exp'=> $iat + 3600,
            'data'=>array(
                "userid"=>$userid)
        ];

        $jwt=JWT::encode($payload, $key, 'HS256');
        $sql = "UPDATE used_tokens SET token = :token  WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'token' => $jwt,
            'userid'=> $userid   
        ]);
        $response->getBody()->write(json_encode(array("status"=>"success", "data"=>null,"newToken" =>$jwt)));
    } catch(PDOException $e) {
        $response->getBody()->write(json_encode(array("status"=>"fail", "data"=>array("title"=>$e->getMessage()))));
    }}
}
catch(Exception $e){
    $response->getBody()->write(json_encode(array("status"=>"fail","data"=>array("title"=>"Token Expired, Please Relogin"))));
}

    $conn = null;
    return $response;
});


$app->delete('/delete/author/{authorid}', function (Request $request, Response $response, array $args) {
    
    $authorid = $args['authorid'];
   
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cabalo_lib";
    $key ='cabalo4B';
    $data=json_decode($request->getBody());
    $userid = $data->userid;
    $jwt=$data->token;
    try{
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT * FROM used_tokens WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
            $stmt->execute(['userid' => $userid]);
        $userdata = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$userdata){
            $response->getBody()->write(json_encode(array("status" => "no token", "data" => null)));
        }
        if ($userdata ['token'] != $jwt){
            $response->getBody()->write(json_encode(array("status" => "invalid token", "data" => null)));
        }else{
    try {
       
        $sql = "DELETE FROM author WHERE authorid = :authorid";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['authorid' => $authorid]);

        $iat=time();
        $payload=[
            'iss'=> 'http://library.org',
            'aud'=>'http://library.com',
            'iat'=> $iat, 
            'exp'=> $iat + 3600,
            'data'=>array(
                "userid"=>$userid)
        ];

        $jwt=JWT::encode($payload, $key, 'HS256');
        $sql = "UPDATE used_tokens SET token = :token  WHERE userid = :userid";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'token' => $jwt,
            'userid'=> $userid   
        ]);
        $response->getBody()->write(json_encode(array("status"=>"success", "data"=>null, "newToken" =>$jwt)));
    } catch(PDOException $e) {
        $response->getBody()->write(json_encode(array("status"=>"fail", "data"=>array("title"=>$e->getMessage()))));
    }}
}
catch(Exception $e){
    $response->getBody()->write(json_encode(array("status"=>"fail","data"=>array("title"=>"Token Expired, Please Relogin"))));
}
    $conn = null;
    return $response;
});


$app->run();

//go to https://github.com/firebase/php-jwt
//C:\xampp\htdocs\security\src>composer require firebase/php-jwt on cmd
