<?php
/*
* This file should display the login form. Login and authentication process should use user credentials
* from the file users.txt
*/
  session_start();
  // read users.txt
  $file = "users.txt";
  $string = file_get_contents($file);
  // seperate data from new line
  $users = explode("\n", $string);

  // set data as array
  $credentials = array();
  foreach($users as $user) {
    // seperate data from pipe
    list($username,$password) = explode('|', $user);
    $credentials[$username] = $password;
  }
  // get data from form 
  $username =  $_POST['username'];
  $password = $_POST['password'];
  $errors = null;
  // if(empty($_POST) &&isset($_SESSION['username']) && isset($_SESSION['username'])) {
  //   header('Location: login.php');
  // }
  // match data with form and users.txt
  if(!empty($_POST['username'])&& !empty($_POST['password'])) {
    if($password == $credentials[$username]) {
      $_SESSION['logged_in'] = true;
      $_SESSION['username'] = $username;
      $_SESSION['password'] = $password;
      header('Location: index.php');
    } else {
      $errors = true;
    }
  }
  if($_SESSION['logged_in'] == true) {
    header('Location: index.php');
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
  <title>Login</title>
</head>
<body>
  <div class="d-flex justify-content-center pt-5">
    <div class="col-12 col-md-6 col-lg-4">
      <?php if($errors == true) { ?>
      <div class="alert alert-danger pb-2">
        Username or password doesn't match, please try again! 
      </div>
      <?php } ?>
      <div class="card shadow-sm">
        <div class="card-body login-card-body">
          <div class="text-center pt-2">
            <h5 class="login-box-msg font-weight-bold">Login</h5>
          </div>
          <form method="post">
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="username" placeholder="username" required>
            </div>
            <div class="input-group mb-3">
              <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


