<?php

include "functions.php";

$user = trim(htmlspecialchars(stripslashes($_GET['user'])));
$scheduler = new Scheduler($user, isset($_GET['next']));

if (!$scheduler->get_user()) {
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
$hs = $scheduler->get_schedule();
$events = $scheduler->get_events($hs);

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
    
        <script src="https://kit.fontawesome.com/4e658c380b.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="dist/css/style.css" \>
    </head>
    <body>
        <main class="container">
            <div class="bg-light p-5 rounded mt-3">
                <h1 class="text-center">Rooster van <?php echo $scheduler->get_name($user); ?></h1>
                <br>
                <table class="table">
                    <tr>
                        <th><a href="view.php?user=<?php echo $_GET['user']; ?>"><i class="fas fa-arrow-circle-left fa-2x"></i></a></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="text-align: right"><a href="view.php?user=<?php echo $_GET['user']; ?>&next"><i id="toggleNext" class="fas fa-arrow-circle-right fa-2x"></i></a></th>
                    </tr>
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
                                $h = $scheduler->get_homework($c['summary']);
                                switch ($x[0]) {
                                    case "[X]":
                                        preg_match('/([0-9]{2}(?=\))).*/', $c['description'], $m);
                                        $s = "style='background-color: red'";
                                        $p = "data-toggle='tooltip' title='".substr($m[0], 3)."'";
                                        $d = ($x[1] == "[!]")?$x[2]:$x[1];
                                        break;
                                    case "[!]":
                                        preg_match('/([0-9]{2}(?=\))).*/', $c['description'], $m);
                                        $s = "style='background-color: orange'";
                                        $p = "data-toggle='tooltip' title='".str_replace(["komt van","\\r"], ["was", ""],substr($m[0], 3))."'";
                                        $d = $x[1];
                                        break;
                                    default:
                                        $p=$s = "";
                                        $d = $x[0];
                                }
                                ?>
                                <td class="cell" <?php echo $s; ?>>
                                    <table class="inner-table">
                                        <tr <?php echo $p; ?>>
                                            <td><?php echo $d; ?></td>
                                            <td style="text-align: right"><?php echo str_replace(["(", ")"], "", $c['teacher']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><small><?php echo $c['location']; ?></small></td>
                                            <td style="text-align: right"><?php if ($h) { ?><span data-toggle="popover" class="homework-icon" title="<?php echo $h; ?>"><i class="fas fa-file-signature"></i></span><?php } ?></td>
                                        </tr>
                                    </table>
                                </td>
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