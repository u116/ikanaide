<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';

class User
{

    private object $con;

    public function __construct()
    {
        $this -> con = new Database;
    }

    public function register(array $registerInfo): string
    {
        if (isset($registerInfo['username'], $registerInfo['email'], $registerInfo['password'], $registerInfo['confirm'])) {
            if (!(empty($registerInfo['username']) || empty($registerInfo['email']) || empty($registerInfo['password']) || empty($registerInfo['confirm']))) {
                if (filter_var($registerInfo['email'], FILTER_VALIDATE_EMAIL)) {
                    if (preg_match('/^[a-zA-Z0-9]+$/', $registerInfo['username']) === 1) {
                        if (strlen($registerInfo['username']) > 3 && strlen($registerInfo['username']) < 16) {
                            $result = $this -> con -> db -> execute_query('SELECT user_id FROM user WHERE username = ?', [$registerInfo['username']]);
                            if ($result -> num_rows === 0) {
                                $result = $this -> con -> db -> execute_query('SELECT user_id FROM user WHERE email = ?', [$registerInfo['email']]);
                                if ($result -> num_rows === 0) {
                                    $password = password_hash($registerInfo['password'], PASSWORD_DEFAULT);
                                    $this -> con -> db -> execute_query('INSERT INTO `user` (`username`, `password`, `email`) VALUES (?, ?, ?)', [
                                        $registerInfo['username'],
                                        $password,
                                        $registerInfo['email'],
                                    ]);
                                    $result = $this -> con -> db -> execute_query('SELECT user_id FROM user WHERE username = ?', [$registerInfo['username']]);
                                    $user_id = $result -> fetch_column();
                                    setcookie('username', $registerInfo['username'], strtotime('NOW+60DAYS'));
                                    setcookie('user_id', $user_id, strtotime('NOW+60DAYS'));
                                    setcookie('passwd', $password, strtotime('NOW+60DAYS'));
                                    setcookie('session', "Yes", strtotime('NOW+60DAYS'));
                                    return 'Ok';
                                } else {
                                    return 'Sorry, that email is already taken';
                                }
                            } else {
                                return 'Sorry, that username is already taken.';
                            }
                        } else {
                            return 'The username  must be between 3 and 16 characters long.';
                        }
                    } else {
                        return 'The username must only contain alphanumeric characters.';
                    }
                } else {
                    return 'That email format is not valid.';
                }
            } else {
                return 'Please fill up the missing fields.';
            }
        } else {
            return 'Please fill up the missing fields.';
        }
    }

    public function login(array $loginInfo): string
    {
        if (isset($loginInfo['username'], $loginInfo['password'])) {
            if (!(empty($loginInfo['username']) || empty($loginInfo['password']))) {
                if (preg_match('/^[a-zA-Z0-9]+$/', $loginInfo['username']) === 1) {
                    if (strlen($loginInfo['username']) > 3 && strlen($loginInfo['username']) < 16) {
                        // Comprobación del nombre de usuario. Si coincide se comprobará la contraseña.
                        $result = $this -> con -> db -> execute_query('SELECT `user_id` FROM user WHERE username = ?', [$loginInfo['username']]);
                        if ($result -> num_rows === 1) {
                            $user_id = $result -> fetch_column();
                            // Comprobación de la contraseña. Si coincide se autenticará al usuario.
                            $result = $this -> con -> db -> execute_query('SELECT `password` FROM user WHERE username = ?', [$loginInfo['username']]);
                            $password = $result -> fetch_column();
                            if (password_verify($loginInfo['password'], $password)) {
                                // Todas las verificaciones han sido exitosas, por lo que se inicia una sesión al usuario autenticado.
                                setcookie('username', $loginInfo['username'], strtotime('NOW+60DAYS'));
                                setcookie('user_id', $user_id, strtotime('NOW+60DAYS'));
                                setcookie('passwd', $password, strtotime('NOW+60DAYS'));
                                setcookie('session', "Yes", strtotime('NOW+60DAYS'));
                                return 'Ok';
                            } else {
                                return 'Incorrect username or password';
                            }
                        } else {
                            return 'Incorrect username or password.';
                        }
                    } else {
                        return 'Incorrect username or password.';
                    }
                } else {
                    return 'Incorrect username or password.';
                }
            } else {
                return 'Please fill up the missing fields.';
            }
        } else {
            return 'Please fill up the missing fields.';
        }
    }

    // Este método añade seguridad al sistema de autenticación de usuario. Se compara la información actual de una cookie con la de la base de datos.
    // Si coincide, indicaría que el usuario no ha alterado la información de sus cookies y que, efectivamente, conserva la información generada en User::login() o User::register).
    public function validateSession(): bool
    {
        if (isset($_COOKIE['username'], $_COOKIE['passwd'], $_COOKIE['user_id'])) {
            $result = $this -> con -> db -> execute_query('SELECT `user_id` FROM `user` WHERE username = ?', [$_COOKIE['username']]);
            if ($result -> num_rows === 1) {
                $id = $result -> fetch_column();
                if ($id == $_COOKIE['user_id']) {
                    $passwd = $this -> con -> db -> execute_query('SELECT `password` FROM `user` WHERE `user_id` = ?', [$_COOKIE['user_id']]) -> fetch_column();
                    if ($passwd === $_COOKIE['passwd']) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getInfo(string $username): array
    {
        $result = $this -> con -> db -> execute_query('SELECT * FROM user WHERE user_id = ?', [$_COOKIE['user_id']]);
        return $result -> fetch_assoc();
    }

    public function animelist(string $username): array
    {
        $result = $this -> con -> db -> execute_query('SELECT * FROM `animelist` WHERE `user_id` = ?', [$_COOKIE['user_id']]);
        if ($result -> num_rows > 0) {
            $i = 0; // Esto es simplemente un contador que uso para no necesitar un for dentro del foreach.
            while ($row = $result -> fetch_assoc()) {
                foreach($row as $key => $value) {
                    $animelist[$i][$key] = $value;
                }
                $i++;
            }
            return $animelist;
        } else {
            return $animelist = [];
        }
    }

    // Añadir o borrar un anime o manga a la base de datos.
    public function addToList($medium, $medium_id) {
        if ($this -> validateSession() === TRUE) {
            if (isset($_POST['add'])) {
                $user_id = $_COOKIE['user_id'];
                switch($medium) {
                    case 'anime':
                        $this -> con -> db -> execute_query('INSERT INTO `animelist` (`user_id`, `anime_id`, `progress`) VALUES (?, ?, default)', [$user_id, $medium_id]);
                        break;
                    case 'manga':
                        $this -> con -> db -> execute_query('INSERT INTO `mangalist` (`user_id`, `manga_id`, `progress`) VALUES (?, ?, default)', [$user_id, $medium_id]);
                        break;
                    default:
                        exit(header('Location: /404'));
                }
                header('Location: /anime?id=' . $medium_id);
            } else if ($_POST['delete']) {
                $user_id = $_COOKIE['user_id'];
                switch($medium) {
                    case 'anime':
                        $this -> con -> db -> execute_query('DELETE FROM `animelist` WHERE `user_id` = ? AND `anime_id` = ?', [$user_id, $medium_id]);
                        break;
                    case 'manga':
                        $this -> con -> db -> execute_query('DELETE FROM `mangalist` WHERE `user_id` = ? AND `manga_id` = ?', [$user_id, $medium_id]);
                        break;
                    default:
                        exit(header('Location: /404'));
                }
                header('Location: /anime?id=' . $medium_id);
            }
        } else {
            exit(header("Location: /logout"));
        }
    }

    public function getAnimes(array $animelist): array
    {
        if (count($animelist) > 0) {
            for ($i=0; $i<count($animelist); $i++) {
                $anime = $this -> con -> db -> execute_query('SELECT `anime_id`, `title`, `episodes`, `type`,  `cover` FROM `anime` WHERE `anime_id` = ?', [$animelist[$i]['anime_id']]);
                $animes[$i] = $anime -> fetch_assoc();
            }
            return $animes;
        } else {
            return $animes = [];
        }
    }

}