<html>

<body>
    <?php if (isset($_GET['invalid'])) { echo "Ongeldige code!"; } elseif (isset($_GET['notready'])) { echo "Code is geldig, maar rooster is nog niet geladen. Probeer over een half uur opnieuw!"; } ?>

    <form action="view.php" method="get"><input type="text" name="user"><input type="submit"></form>
</body>
</html>