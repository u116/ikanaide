<?php

include('resources/functions.php');
require('database/conn.php');

// Propósito: distinguir el medio mediante la query de la URL (proveniente de los links 'Edit this page' de las views de query) y recoger el ID correspondiente.
if ($_GET) {
    $medium = substr(parse_url($_SERVER['REQUEST_URI'])['query'], 0, 3);
    // Resultado: array $uriQuery[aid|mid|vid] => table row id
    $id = $_GET[$medium];
}

if (isset($_POST['submit'])) {
    $animeSubmission['anime_id'] = $id;
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
            // Current `edit_anime`'s column number: 13 (1 is ID which will query as null, 1 is anime_id foreign key, 11 are form data)
            $animeSubmission[$key] = $value;
        }
    }

    $stmt = $db -> prepare('INSERT INTO `edit_anime` VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt -> bind_param('issssissssss',
        $animeSubmission['anime_id'],
        $animeSubmission['title'],
        $animeSubmission['english_title'],
        $animeSubmission['japanese_title'],
        $animeSubmission['type'],
        $animeSubmission['episodes'],
        $animeSubmission['status'],
        $animeSubmission['start_date'],
        $animeSubmission['end_date'],
        $animeSubmission['desc'],
        $animeSubmission['cover'],
        $animeSubmission['header']
    );
    $stmt -> execute();

    print "<p style='margin: 40px auto;'>Your edit submission has been correctly submitted. Thanks!</p>";
    $db -> close();
} else {
    // Propósito: mostrar un formulario adecuado para cada medio.
    switch ($medium) {
        case 'aid':
            if (isset($id)) {
                $stmt = $db->prepare("SELECT * FROM anime WHERE anime_id = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $row = $result->fetch_assoc();
                    foreach ($row as $key => $value) {
                        $animeInfo[$key] = $value;
                    }
                    $animeInfo['studios'] = 'Sunrise';
                } else {
                    // Si ninguna entrada de la base de datos coincide con $_GET['aid'], manda un 404.
                    header('Location: /404');
                }

                foreach ($animeInfo as $key => $value) {
                    if (!($key === 'members' || $key === 'favorited' || $key === 'anime_id')) {
                        $animeForm[$key] = $value;
                    }
                }
                $db -> close();
            }
            break;
        case 'mid':
            print 'manga';
            break;
        case 'vid':
            print 'vn';
            break;
        default:
            header('Location: /404');
    }
    require('resources/views/edit/edit.view.php');
}