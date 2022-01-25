<?php

include "functions.php";

$scheduler = new Scheduler();

$user = trim(htmlspecialchars(stripslashes($_GET['user'])));

if (!$scheduler->get_user($user)) {
    header("Location: index.php?invalid");
    exit();
}

if (!file_exists('events/'.$user.'.json')) {
    header("Location: index.php?notready");
    exit();
}

if (isset($_GET['save'])) {
    setcookie('code' ,$user , time() + (86400 * 30) , "/");
    header("Location: view.php?user=".$user);
}

$events = $scheduler->get_events($user);

?>

<html>
    <head>
        <title>Bekijk rooster</title>
        <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <!-- Bootstrap + Popper.JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </head>
    <body>
        <main class="container">
            <div class="bg-light p-5 rounded mt-3">
                <h1 class="text-center">Rooster van <?php echo $scheduler->get_name($user); ?></h1>
                <br>
                <table class="table">
                    <tr>
                        <th></th>
                        <th>Maandag</th>
                        <th>Dinsdag</th>
                        <th>Woensdag</th>
                        <th>Donderdag</th>
                        <th>Vrijdag</th>
                    <tr>
                    <?php foreach ($events as $nr => $p) { ?>
                        <tr>
                            <th><?php echo $nr+1; ?></th>
                            <?php foreach ($p as $c) { ?>
                                <?php
                                $x = explode(" ", $c['description']);
                                if ($x[0] == "[X]") {
                                    echo "<td style='background-color: red'>".$x[1]."</td>";
                                } elseif ($x[0] == "[!]") {
                                    preg_match('/([0-9]{2}(?=\))).*/', $c['description'], $m);
                                    echo "<td style='background-color: orange' data-toggle='tooltip' title='".substr($m[0], 3)."'>".$x[0].$x[1]."</td>";
                                } else {
                                    echo "<td>".$x[0]."</td>";
                                }
                                ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
                <hr>
                <p>Let op: Aan dit project wordt nog steeds gewerkt. Voortgang is te zien op de <a href="https://github.com/underlyingglitch/scheduler" target="_blank">GitHub pagina</a></p>
            </div>
        </main>
    </body>

    <script src="dist/js/view.js"></script>
</html>