<?php

namespace SimpleAPI\Controllers;

use SimpleAPI\Controllers\ResponseController;
use SimpleAPI\Database\DBController;

class PUTController
{
    function __construct(string $route)
    {
        $this->request = $route;
        $this->table = explode("?", $route)[0];
        $this->data = $this->getData();
        $this->id = $this->getId();
        $this->columns = $this->getColumns();
        $this->token = $this->getToken();
    }

    public function getData()
    {
        $data = [];
        parse_str(file_get_contents("php://input"), $data);
        return $data;
    }

    public function getId()
    {
        if (isset($_GET["id"]) && isset($_GET["nameId"])) {
            $nameId = $_GET["nameId"];
            $id = $_GET["id"];

            $getId = "SELECT * FROM $this->table WHERE $nameId = '$id'";

            if ($getId) {
                return ["nameId" => $nameId, "id" => $id];
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "The id is not found in the database"
                );
                return false;
            }
        } else {
            $this->response = ResponseController::LogError(
                404,
                "Something was wrong"
            );
            return false;
        }
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

        foreach (array_keys($this->data) as $key => $value) {
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

    public function getToken()
    {
        if ($this->columns) {
            if (isset($_GET["token"])) {
                $token = $_GET["token"];
                $user = "SELECT token_user,token_exp_user from users WHERE token_user = '$token'";
                $user = DBController::query($user);

                if (!empty($user)) {
                    $time = time();

                    if ($user[0]->token_exp_user > $time) {
                        return true;
                    } else {
                        $this->response = ResponseController::LogError(
                            404,
                            "The token has expired"
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
            } else {
                $this->response = ResponseController::LogError(
                    404,
                    "The user is not authorized"
                );
                return false;
            }
        } else {
            return null;
        }
    }

    public function putData()
    {
        if ($this->id) {
            $set = "";

            foreach ($this->data as $key => $value) {
                $set .= "$key = '$value', ";
            }

            $set = substr($set, 0, -2);

            $nameId = $this->id["nameId"];
            $id = $this->id["id"];

            $query = "UPDATE $this->table SET $set WHERE $nameId = $id";

            $data = DBController::query($query);

            return ResponseController::LogData(
                "The data was updated successfully",
                $data
            );
        } else {
            return ResponseController::LogError(404, "Something was wrong");
        }
    }

    public function response()
    {
        $this->putData();
        echo $this->response;
    }
}
