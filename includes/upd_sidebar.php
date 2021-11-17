<?php ?>
<div class="l-navbar show-n" id="nav-bar">
    <nav class="nav">
        <div>
          <a class="nav_logo"> <span class="material-icons nav_logo-icon">leaderboard</span> <span class="nav_logo-name">UPD</span> </a>
            <div class="nav_list mt-5">
              <!-- <a class="nav_link active" aria-current="page" id="active"> <span class="material-icons nav_icon">home</span> <span class="nav_name" data-i18n="menu-overview">Overview</span> </a> -->
              <a href="./overview_summary.php" class="nav_link <?php if ($menu=="overview") {echo "active";} ?>" <?php if ($menu=="overview") {echo "aria-current='page' id='active'";} ?>> <span class="material-icons nav_icon">home</span> <span class="nav_name" data-i18n="menu-overview">Overview</span> </a>
              <a href="./pages_home.php" class="nav_link <?php if ($menu=="pages") {echo "active";} ?>" <?php if ($menu=="pages") {echo "aria-current='page' id='active'";} ?>> <span class="material-icons nav_icon">layers</span> <span class="nav_name" data-i18n="menu-pages">Pages</span> </a>
              <a href="./tasks_home.php" class="nav_link <?php if ($menu=="tasks") {echo "active";} ?>" <?php if ($menu=="tasks") {echo "aria-current='page' id='active'";} ?>> <span class="material-icons nav_icon">assignment</span> <span class="nav_name" data-i18n="menu-tasks">Tasks</span> </a>
              <a href="./projects_home.php" class="nav_link <?php if ($menu=="projects") {echo "active";} ?>" <?php if ($menu=="projects") {echo "aria-current='page' id='active'";} ?>> <span class="material-icons nav_icon">folder</span> <span class="nav_name" data-i18n="menu-projects">Projects</span> </a>
               </div>
        </div>
        <div class="mb-4">
        <a href="#" class="nav_link"> <span class="material-icons nav_icon">feedback</span> <span class="nav_name" data-i18n="menu-feedback">Feedback</span> </a>
        <a class="nav_link"> <span class="material-icons nav_icon pointer" id="header-toggle">compress</span> </a>
        </div>
    </nav>
</div>
