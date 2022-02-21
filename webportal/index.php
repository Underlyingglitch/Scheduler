<?php
include "functions.php";

$changes = json_decode(file_get_contents('../data/changes_web.json'), true);
$groups = [];

foreach (json_decode(file_get_contents('../data/groups.json'), true) as $group) {
    $a = explode(';', $group);
    foreach($a as $b) {
        $groups[] = $b;
    }
}
sort($groups);

?>

<html>
    <head>
        <title>Scheduler</title>
        <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    </head>
    <body>
        <main class="container">
            <div class="bg-light p-5 rounded mt-3">
                <h1 class="text-center">Welkom bij Scheduler</h1>
                <br>
                <?php if (isset($_GET['invalid'])) { echo "<p style='color:red;'>Ongeldige code!</p>"; } elseif (isset($_GET['notready'])) { echo "<p style='color:red;'>Code is geldig, maar rooster is nog niet geladen. Probeer over een half uur opnieuw!</p>"; } ?>
                <!-- <form action="view.php" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <input class="form-control col-md-2" type="text" name="user" placeholder="Persoonlijke code">
                        </div>
                        <div class="col-md-3">
                            <input class="btn btn-primary" type="submit" value="Naar rooster">
                        </div>
                    </div>
                    <input class="form-check-input" type="checkbox" name="save"> Bewaar code (gebruikt cookies)
                </form> -->
                <!-- <?php if (isset($_COOKIE['code'])) { ?><a href="view.php?user=<?php echo htmlspecialchars(stripslashes($_COOKIE['code'])); ?>">Of ga terug naar je opgeslagen pagina</a><br><?php } ?> -->
                <h4>Voeg rooster toe</h4>
                <form method="post" action="newuser.php">
                    <div class="row">
                        <div class="col-md-4">
                            <input class="form-control" type="text" placeholder="Naam" name="name">
                        </div>
                        <div class="col-md-4">
                            <input class="form-control" type="text" placeholder="URL" name="url">
                        </div>
                        <div class="col-md-4">
                            <input class="btn btn-primary" type="submit" name="submit" value="Voeg toe">
                            <a class="btn btn-info" href="info.php">Meer informatie</a>
                        </div>
                    </div>
                </form>
                <hr>
                <table class="table">
                    <tr>
                        <th>Vak</th>
                        <th>Tijd</th>
                        <th>Soort wijziging</th>
                        <th>Opmerking</th>
                    </tr>
                    <?php foreach ($changes as $change) { ?>
                        <tr style="background-color: <?php echo hex2rgba($change['color'], 0.2); ?>">
                            <td><?php echo $change['description']." (".$change['teacher'].")"; ?></td>
                            <td><?php echo $change['time']; ?></td>
                            <td><?php echo $change['title']; ?></td>
                            <td>
                                <?php if($change['title'] == "Docent vervangen"){
                                    echo "<s>".$change['fields'][2]['value']."</s> ".$change['fields'][3]['value'];
                                }elseif($change['title'] == "Lokaal gewijzigd"){
                                    echo "<s>".$change['fields'][2]['value']."</s> ".$change['fields'][3]['value'];
                                } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <hr>
                Let op. Momenteel worden enkel de volgende groepen ondersteund. Staat jouw groep er niet tussen? Voeg dan jouw eigen rooster toe via het formulier bovenaan deze pagina.<br>
                <?php foreach ($groups as $group) { echo $group." | "; } ?>
                <hr>
                <p>Let op: Aan dit project wordt nog steeds gewerkt. Voortgang is te zien op de <a href="https://github.com/underlyingglitch/scheduler" target="_blank">GitHub pagina</a></p>
            </div>
        </main>
    </body>
</html>