<?php


Set::Route('users', array(

      "Controller" => "users:users:index",
      "Pattern" => "/users/"
));

Set::Route('users_login', array(

      "Controller" => "users:users:login",
      "Pattern" => "/login/"
));

Set::Route('users_logout', array(

      "Controller" => "users:users:logout",
      "Pattern" => "/logout/"
));

Set::Route('users_login_auth', array(

      "Controller" => "users:users:loginAuth",
      "Pattern" => "/loginAuth/"
));

Set::Route('users_List', array(

      "Controller" => "users:users:list",
      "Pattern" => "/users/List/"
));