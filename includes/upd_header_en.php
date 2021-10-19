
<?php ?>
<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Required meta tags -->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <!-- Bootstrap CSS -->
    <link type="text/css" href="css/bootstrap.min.css" rel="stylesheet">
	  <!-- Other CSS -->
    <link rel="stylesheet" type="text/css" href="css/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="css/upd-custom.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato|Noto+Sans">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>UPD Dashboard</title>
  </head>
  <body id="body-pd" class="body-pd" data-new-gr-c-s-check-loaded="14.1023.0" data-gr-ext-installed="">
  <?php

  // get the current page name and split it by "_" to get the menu and tab varibles - to activate the main menu and tab menu
  $currentPageName = htmlspecialchars(substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1,-4));

  // if the "_" exists than split the page name by it and populate $menu and $tab variables
  if (strpos($currentPageName,"_") !== false) {
      $menu = explode("_", $currentPageName)[0];
      $tab = explode("_", $currentPageName)[1];
  }
  //if not - then just populate the $menu variable with the page name to activate the left sidebar menu only.
  else {
      $menu = $currentPageName;
      $tab = "";
  }

  ?>
  <div class="container vh-100">
    <header id="header">
      <div class="row">
        <div class="col-lg-3 col-md-4 col-sm-6 col-6 text-start"><img class="img-fluid fip-colour" src="./assets/img/CRA-FIP-9pt-e.png" alt="Government of Canada"></div>
        <div class="col-lg-9 col-md-8 col-sm-6 col-6 text-end lang-toggle" class="locale-switcher"> <ul class="list-inline">
        <li class="list-inline-item hidden"><a href="test_en.php" data-locale="en">English</a></li>
        <li class="list-inline-item"><a href="test_fr.php" data-locale="fr">Français</a></li>
        </ul> <!-- <a href="#" data-locale="en">English</a> | <a href="#" data-locale="fr">Français</a> -->
      </div>
    </div>
    </header>
