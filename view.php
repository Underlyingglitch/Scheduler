<?php

include "functions.php";

$scheduler = new Scheduler();

if (!$scheduler->get_user($_GET['user'])) {
    header("Location: index.php?invalid");
    exit();
}

if (!file_exists('events/'.$_GET['user'].'.json')) {
    header("Location: index.php?notready");
    exit();
}

if (isset($_GET['save'])) {
    setcookie('code' ,$_GET['user'] , time() + (86400 * 30) , "/");
    header("Location: view.php?user=".$_GET['user']);
}

$events = $scheduler->get_events($_GET['user']);

?>

<html>
    <head>
        <title>Bekijk rooster</title>
        <link rel="stylesheet" href="style.css" \>
    </head>
    <body>
        <table border="1">
            <tr>
                <th></th>
                <th>Ma</th>
                <th>Di</th>
                <th>Wo</th>
                <th>Do</th>
                <th>Vr</th>
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
                            echo "<td style='background-color: orange'><span class='tooltip'>".$x[0].$x[1]."<span class='tooltiptext'>".substr($m[0], 3)."</span></span></td>";
                        } else {
                            echo "<td>".$x[0]."</td>";
                        }
                        ?>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>

        <p>Let op: Aan dit project wordt nog steeds gewerkt. Voortgang is te zien op de <a href="https://github.com/underlyingglitch/scheduler" target="_blank">GitHub pagina</a></p>
        <hr>
        <h3>Vrienden:</h3>
        <p>Binnenkort...</p>
    </body>
</html>