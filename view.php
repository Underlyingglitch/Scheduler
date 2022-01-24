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
            <?php foreach ($events as $nr => $period) { ?>
                <tr>
                    <th><?php echo $nr+1; ?></th>
                    <?php foreach ($period as $course) { ?>
                        <?php
                        $x = explode(" ", $course['description']);
                        if ($x[0] == "[X]") {
                            echo "<td style='background-color: red'>".$x[1]."</td>";
                        } elseif ($x[0] == "[!]") {
                            echo "<td style='background-color: orange'>".$x[0].$x[1]."</td>";
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