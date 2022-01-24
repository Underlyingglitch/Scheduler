<html>

<body>
    <?php if (isset($_GET['invalid'])) { echo "Ongeldige code!"; } elseif (isset($_GET['notready'])) { echo "Code is geldig, maar rooster is nog niet geladen. Probeer over een half uur opnieuw!"; } ?>

    <form action="view.php" method="get">
        <input type="text" name="user" placeholder="Persoonlijke code">
        <input type="submit">
        <input type="checkbox" name="save">Bewaar code (gebruikt cookies)
    </form>
    <?php if (isset($_COOKIE['code'])) { ?><a href="view.php?user=<?php echo $_COOKIE['code']; ?>">Of ga verder naar je opgeslagen pagina</a><?php } ?>
</body>
</html>