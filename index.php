<?php
include "functions.php";
?>

<head>
        <title>Scheduler</title>
    </head>
    <body>
        <?php if (isset($_GET['invalid'])) { echo "Ongeldige code!"; } elseif (isset($_GET['notready'])) { echo "Code is geldig, maar rooster is nog niet geladen. Probeer over een half uur opnieuw!"; } ?>
        <form action="view.php" method="get">
            <input type="text" name="user" placeholder="Persoonlijke code">
            <input type="submit">
            <input type="checkbox" name="save">Bewaar code (gebruikt cookies)
        </form>
        <?php if (isset($_COOKIE['code'])) { ?><a href="view.php?user=<?php echo $_COOKIE['code']; ?>">Of ga verder naar je opgeslagen pagina</a><br><?php } ?>
        <hr>
        <form method="post" action="newuser.php">
            <input type="text" placeholder="Naam"><br>
            <input type="text" placeholder="URL"><br>
            <input type="submit" name="submit">
        </form>
        <hr>
        <p>Let op: Aan dit project wordt nog steeds gewerkt. Voortgang is te zien op de <a href="https://github.com/underlyingglitch/scheduler" target="_blank">GitHub pagina</a></p>
    </body>
</html>