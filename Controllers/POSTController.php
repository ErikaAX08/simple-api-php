<?php

namespace SimpleAPI\Controllers;

use SimpleAPI\Database\DBController;
use SimpleAPI\Controllers\ResponseController;
use SimpleAPI\Controllers\JWTController;

class POSTController
{
    public $response;

    function __construct($request)
    {
        $this->request = $request;
        $this->table = explode("?", $request)[0];
        $this->columns = $this->getColumns();
        $this->login = $this->getLogin();
        $this->token = $this->getToken();
        $this->register = $this->getRegister();
    }

    public function getColumns()
    {
        $columns = [];
        $db = $_ENV["DB_NAME"];
        $query = "SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = '$db' AND table_name = '$this->table'";
        $response = DBController::query($query);

        foreach ($response as $key => $value) {
            array_push($columns, $value->item);
        }

        array_shift($columns);
        array_pop($columns);
        array_pop($columns);

        $match = 0;

        foreach (array_keys($_POST) as $key => $value) {
            if (in_array($value, $columns)) {
                $match++;
            } else {
                $match--;
                break;
            }
        }

        if ($match > 0) {
            return true;
        } else {
            $this->response = ResponseController::LogError(
                404,
                "Fields in the form do not match the database"
            );
            return false;
        }
    }

    public function getLogin()
    {
        if ($this->columns && isset($_GET["login"])) {
            if ($_GET["login"] === "true") {
                $email = $_POST["email_user"];
                $query = DBController::query(
                    "SELECT * FROM $this->table WHERE email_user = '$email'"
                );
                if (!empty($query)) {
                    $crypt = crypt(
                        $_POST["password_user"],
                        $_ENV["HASH_CRYPT"]
                    );
                    if ($query[0]->password_user === $crypt) {
                        $dataToken = [
                            "id" => $query[0]->id_user,
                            "email" => $query[0]->email_user,
                        ];

                        $jwt = JWTController::CreateToken($dataToken);
                        JWTController::UpdateToken($jwt, $query[0]->id_user);

                        $this->response = ResponseController::LogData(
                            "Login successfully",
                            "null"
                        );
                        return true;
                    } else {
                        $this->response = ResponseController::LogError(
                            404,
                            "Wrong password"
                        );
                    }
                } else {
                    $this->response = ResponseController::LogError(
                        404,
                        "Wrong email"
                    );
                }
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "Authorization required"
                );
            }
        } else {
            return false;
        }
    }

    public function getToken()
    {
        if (
            $this->columns &&
            isset(apache_request_headers()["Authorization"])
        ) {
            $token = apache_request_headers()["Authorization"];
            $token = JWTController::CheckToken($token);

            if ($token) {
                return true;
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "The user is not authorized"
                );
                return false;
            }
        } else {
            $this->response = ResponseController::LogError(
                404,
                "The user is not authorized"
            );
            return false;
        }
    }

    public function getRegister()
    {
        if (
            $this->columns &&
            isset($_GET["register"]) &&
            $_GET["register"] === "true"
        ) {
            $pass = $_POST["password_user"];

            if ($pass !== null) {
                $hash = $_ENV["HASH_CRYPT"];
                $crypt = crypt($pass, $hash);
                $data = $_POST;
                $data["password_user"] = $crypt;

                $this->postData($data);

                $this->response = ResponseController::LogData(
                    "The user was registered successfully",
                    "null"
                );

                return true;
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "The password is empty"
                );
                return false;
            }
        } else {
            return false;
        }
    }

    public function postData($data)
    {
        $columns = "(";
        $params = "(";

        foreach ($data as $key => $value) {
            $columns .= "$key,";
            $params .= "'$value',";
        }

        $columns = substr($columns, 0, -1);
        $params = substr($params, 0, -1);

        $columns .= ")";
        $params .= ")";

        $query = "INSERT INTO $this->table $columns VALUES $params";

        return DBController::query($query);
    }

    public function response()
    {
        if (
            $this->register === false &&
            $this->login === false &&
            $this->token === true
        ) {
            // $this->response = ResponseController::LogData(
            //     $this->postData($data)
            // );
        }
        echo $this->response;
    }
}
